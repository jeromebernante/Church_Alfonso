<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connection.php';
$user_id = $_SESSION['user_id'];

$sql = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name);
$stmt->fetch();
$stmt->close();

date_default_timezone_set('Asia/Manila');
$current_datetime = date("l, F j, Y g:i A");

$sql = "SELECT id, name_of_intended, name_of_requestor, pamisa_type, selected_date, selected_time, price, status, payment_receipt, created_at 
        FROM pamisa_requests WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$history = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$sql = "SELECT id, bride_name, groom_name, priest_name, contact, wedding_date, payment_receipt, status, created_at 
        FROM wedding_requests WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$wedding_history = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$sql = "SELECT b.id, b.baptized_name, b.parents_name, b.ninongs_ninangs, b.request_date, b.selected_date, b.status, b.price, p.receipt_path 
        FROM baptism_requests b 
        LEFT JOIN baptism_payments p ON b.id = p.baptism_request_id 
        WHERE b.user_id = ? ORDER BY b.request_date DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("MySQL error: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$baptism_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$sql_notif = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND status = 'success'";
$stmt_notif = $conn->prepare($sql_notif);
$stmt_notif->bind_param("i", $user_id);
$stmt_notif->execute();
$stmt_notif->bind_result($notif_count);
$stmt_notif->fetch();
$stmt_notif->close();


$sql = "SELECT id, type_of_blessing, name_of_requestor, priest_name, name_of_blessed, blessing_date, blessing_time, receipt_path, status, created_at 
        FROM blessings_requests WHERE user_id = ? ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("MySQL error: " . $conn->error);  // Debugging output
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$blessing_history = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parish of the Holy Cross - History</title>
    <link rel="stylesheet" href="stylesd.css">
    <link rel="stylesheet" href="buttons.css">
    <link rel="icon" href="imgs/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.js"></script>
</head>

<body id="bodyTag">
    <header class="header" id="header">
    </header>

    <div class="l-navbar" id="nav-bar">
        <nav class="nav">
            <div>
                <a href="#" class="nav_logo">
                    <img src="imgs/logo.png" alt="Parish Logo"
                        style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                    <span class="nav_logo-name">Parish of the Holy Cross</span>
                </a>
                <div class="nav_list">
                    <a href="dashboard.php" class="nav_link">
                        <i class='bx bx-grid-alt nav_icon'></i>
                        <span class="nav_name">Dashboard</span>
                    </a>
                    <a href="event_request.php" class="nav_link">
                        <i class='bx bx-calendar-event nav_icon'></i>
                        <span class="nav_name">Event Request</span>
                    </a>
                    <a href="notifications.php" class="nav_link" id="notification-link">
                        <i class='bx bx-bell nav_icon'></i>
                        <span class="nav_name">Notifications</span>
                        <?php if ($notif_count > 0): ?>
                            <span class="notification-badge"><?php echo $notif_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="history.php" class="nav_link active">
                        <i class='bx bx-message-square-detail nav_icon'></i>
                        <span class="nav_name">History</span>
                    </a>
                    <a href="profile.php" class="nav_link">
                        <i class='bx bx-user nav_icon'></i>
                        <span class="nav_name">My Profile</span>
                    </a>
                </div>
            </div>
            <a href="#" class="nav_link" id="logout">
                <i class='bx bx-log-out nav_icon'></i>
                <span class="nav_name">Sign Out</span>
            </a>
        </nav>
    </div>

    <section class="welcome">
        <center>
            <h2>History of Requests for <?php echo htmlspecialchars($user_name); ?>!</h2>
        </center>
        <center>
            <p>Current Date and Time: <?php echo $current_datetime; ?></p>
        </center>
    </section>

    <section class="about-us">
        <h2>Your History Request</h2>
        <p class="justified">
            The History section allows you to view and manage your past service reservations. Here, you can review
            details of previous bookings, check service dates, and keep track of your scheduled appointments. Stay
            organized and easily access your service history whenever needed.
        </p>
    </section>

    <!-- Search and Filter Section -->
    <div class="search-filter-container">
        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search for requests..."
            class="search-box">
        <select id="statusFilter" onchange="filterTable()" class="filter-dropdown">
            <option value="">All Status</option>
            <option value="Pending">Pending</option>
            <option value="Paid">Paid</option>
            <option value="Cancelled">Cancelled</option>
            <option value="Approved">Approved</option>
            <option value="Completed">Completed</option>
        </select>
    </div>

    <div class="table-container">
        <center>
            <h2>Mass Requests</h2>
        </center>
        <?php if (count($history) > 0): ?>
            <table class="styled-table" id="historyTable">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Request ID</th>
                        <th>Intended Name</th>
                        <th>Requestor Name</th>
                        <th>Mass Type</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Receipt</th>
                        <th>Request Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $row): ?>
                        <tr>
                            <td>Mass</td>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name_of_intended']); ?></td>
                            <td><?php echo htmlspecialchars($row['name_of_requestor']); ?></td>
                            <td><?php echo htmlspecialchars($row['pamisa_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['selected_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['selected_time']); ?></td>
                            <td>₱<?php echo number_format($row['price'], 2); ?></td>
                            <td class="status-cell"><?php echo htmlspecialchars($row['status']); ?></td>
                            <td>
                                <?php if (!empty($row['payment_receipt'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['payment_receipt']); ?>"
                                        target="_blank">View</a>
                                <?php else: ?>
                                    No receipt uploaded
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No Mass history found.</p>
        <?php endif; ?>
    </div>

    <div class="table-container">
        <br>
        <center>
            <h2>Wedding Requests</h2>
        </center>
        <?php if (count($wedding_history) > 0): ?>
            <table class="styled-table" id="weddingTable">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Request ID</th>
                        <th>Bride's Name</th>
                        <th>Groom's Name</th>
                        <th>Priest's Name</th>
                        <th>Contact</th>
                        <th>Wedding Date</th>
                        <th>Status</th>
                        <th>Receipt</th>
                        <th>Request Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($wedding_history as $row): ?>
                        <tr>
                            <td>Wedding</td>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['bride_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['groom_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['priest_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact']); ?></td>
                            <td><?php echo htmlspecialchars($row['wedding_date']); ?></td>
                            <td class="status-cell"><?php echo htmlspecialchars($row['status']); ?></td>
                            <td>
                                <?php if (!empty($row['payment_receipt'])): ?>
                                    <a href="uploads/<?php echo htmlspecialchars($row['payment_receipt']); ?>"
                                        target="_blank">View</a>
                                <?php else: ?>
                                    No receipt uploaded
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No wedding requests found.</p>
        <?php endif; ?>
    </div>

    <div class="table-container">
        <br>
        <center>
            <h2>Baptism Requests</h2>
        </center>
        <table class="styled-table" id="blessingTable">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Baptized Name</th>
                    <th>Parents</th>
                    <th>Ninongs & Ninangs</th>
                    <th>Baptism Date</th>
                    <th>Status</th>
                    <th>Price</th>
                    <th>Receipt</th>
                    <th>Request Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($baptism_history as $row): ?>
                    <tr>
                        <td>Baptism</td>
                        <td><?php echo htmlspecialchars($row['baptized_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['parents_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['ninongs_ninangs']); ?></td>
                        <td><?php echo htmlspecialchars($row['selected_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>₱<?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo $row['receipt_path'] ? "<a href='{$row['receipt_path']}' target='_blank'>View</a>" : "No receipt"; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['request_date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </section>

    <div class="table-container">
        <br>
        <center>
            <h2>Blessings Requests</h2>
        </center>
        <?php if (count($blessing_history) > 0): ?>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Type of Blessing</th>
                        <th>Requestor Name</th>
                        <th>Blessed Name</th>
                        <th>Priest's Name</th>
                        <th>Blessing Date</th>
                        <th>Blessing Time</th>
                        <th>Status</th>
                        <th>Receipt</th>
                        <th>Request Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blessing_history as $row): ?>
                        <tr>
                            <td>Blessing</td>
                            <td><?php echo htmlspecialchars($row['type_of_blessing']); ?></td>
                            <td><?php echo htmlspecialchars($row['name_of_requestor']); ?></td>
                            <td><?php echo htmlspecialchars($row['name_of_blessed']); ?></td>
                            <td><?php echo htmlspecialchars($row['priest_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['blessing_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['blessing_time']); ?></td>
                            <td class="status-cell"><?php echo htmlspecialchars($row['status']); ?></td>
                            <td>
                                <?php if (!empty($row['receipt_path'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['receipt_path']); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    No receipt uploaded
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No blessings requests found.</p>
        <?php endif; ?>

        <div>


            <!-- JavaScript for Search & Filter -->
            <script>
                function filterTable() {
                    let input = document.getElementById("searchInput").value.toLowerCase();
                    let filter = document.getElementById("statusFilter").value.toLowerCase();

                    let tables = document.querySelectorAll(".styled-table");

                    tables.forEach(table => {
                        let tr = table.getElementsByTagName("tr");

                        for (let i = 1; i < tr.length; i++) {
                            let tdArray = tr[i].getElementsByTagName("td");
                            let rowText = "";

                            for (let j = 0; j < tdArray.length; j++) {
                                rowText += tdArray[j].textContent.toLowerCase() + " ";
                            }

                            let statusCell = tr[i].getElementsByClassName("status-cell")[0];
                            let statusText = statusCell ? statusCell.textContent.toLowerCase() : "";

                            if (rowText.includes(input) && (filter === "" || statusText === filter)) {
                                tr[i].style.display = "";
                            } else {
                                tr[i].style.display = "none";
                            }
                        }
                    });
                }

            </script>

            <script>
                $(document).ready(function () {
                    $('#notification-link').on('click', function (e) {
                        $.ajax({
                            url: 'clear_notifications.php',
                            method: 'POST',
                            success: function () {
                                $('.notification-badge').fadeOut();
                            }
                        });
                    });
                });
            </script>


            <style>
                .table-container {
                    max-height: 400px;
                    /* Adjust height as needed */
                    overflow-y: auto;
                    overflow-x: auto;
                    border: 1px solid #ccc;
                    /* Optional: for better visibility */
                    padding: 5px;
                    /* Optional: for spacing */
                    margin-bottom: 20px;
                    /* Optional: for separation */
                }

                .notification-badge {
                    background: red;
                    color: white;
                    padding: 3px 8px;
                    border-radius: 50%;
                    font-size: 12px;
                    margin-left: 5px;
                }

                .search-filter-container {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 15px;
                    padding: 10px;
                    background-color: #e0f2e9;
                    border-radius: 8px;
                }

                .search-box,
                .filter-dropdown {
                    padding: 8px;
                    font-size: 16px;
                    border: 2px solid #4CAF50;
                    border-radius: 5px;
                }

                .search-box {
                    width: 70%;
                }

                .filter-dropdown {
                    width: 25%;
                }

                .styled-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }

                .styled-table th,
                .styled-table td {
                    padding: 10px;
                    text-align: left;
                    border-bottom: 1px solid #ddd;
                }

                .styled-table th {
                    background-color: #4CAF50;
                    color: white;
                }

                .styled-table tr:hover {
                    background-color: #f1f1f1;
                }

                .styled-table a {
                    color: #2d8a42;
                    font-weight: bold;
                }

                .styled-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    font-size: 16px;
                    text-align: center;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }

                .styled-table th,
                .styled-table td {
                    padding: 12px;
                    border: 1px solid #ddd;
                }

                .styled-table thead {
                    background-color: #3E8E41;
                    color: white;
                    text-transform: uppercase;
                }

                .styled-table tbody tr:nth-child(even) {
                    background-color: #f3f3f3;
                }

                .styled-table tbody tr:hover {
                    background-color: #ddd;
                }

                .styled-table a {
                    color: #3E8E41;
                    text-decoration: none;
                    font-weight: bold;
                }

                .styled-table a:hover {
                    text-decoration: underline;
                }
            </style>
            <script src="scriptd.js"></script>

            <footer>
                <div class="footer-container">
                    <div class="footer-about">
                        <h3>About Parish of the Holy Cross</h3>
                        <p>
                            The Parish of the Holy Cross is a sacred place of worship, where the community comes
                            together to celebrate faith, hope, and love. Whether you're seeking spiritual growth, a
                            peaceful moment of reflection, or a place to connect with others, our church provides a
                            welcoming environment for all.
                        </p>

                    </div>
                    <div class="footer-contact">
                        <h3>Contact Us</h3>
                        <p>Email: holycrossparish127@yahoo.com</p>
                        <p>Phone: 28671581</p>
                        <p>Address: Gen. T. De Leon, Valenzuela, Philippines, 1442 </p>
                    </div>
                    <div class="footer-socials">
                        <h3>Follow Us</h3>
                        <a href="https://www.facebook.com/ParishoftheHolyCrossValenzuelaCityOfficial/">Facebook</a>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2025 Parish of the Holy Cross. All rights reserved.</p>
                </div>
            </footer>

</body>

</html>