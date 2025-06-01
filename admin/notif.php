<?php
session_start();
include 'db_connection.php';

if (isset($_GET['fetch_unread_count'])) {
    $query_unread = "SELECT COUNT(*) AS unread_count FROM admin_notifications WHERE status = 'unread'";
    $result_unread = $conn->query($query_unread);
    $unread_count = ($result_unread && $result_unread->num_rows > 0) ? $result_unread->fetch_assoc()['unread_count'] : 0;
    
    echo json_encode(["unread_count" => $unread_count]);
    exit;
}

if (isset($_GET['mark_read'])) {
    $conn->query("UPDATE admin_notifications SET status = 'read' WHERE status = 'unread'");
    echo json_encode(["success" => true]);
    exit;
}

$query = "SELECT id, message, created_at, status FROM admin_notifications ORDER BY created_at DESC";
$result = $conn->query($query);

$notifications = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

$query_unread = "SELECT COUNT(*) AS unread_count FROM admin_notifications WHERE status = 'unread'";
$result_unread = $conn->query($query_unread);
$unread_count = ($result_unread && $result_unread->num_rows > 0) ? $result_unread->fetch_assoc()['unread_count'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parish of the Holy Cross - Notifications</title>
    <link rel="stylesheet" href="stylesd.css">
    <link rel="stylesheet" href="buttons.css">
    <link rel="icon" href="imgs/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="scriptd.js"></script>
    <style>
        .table-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 10px;
            overflow-x: auto; 
            max-height: 1500px; 
            overflow-y: auto;
        }

        .styled-table {
            width: 80%;
            border-collapse: collapse;
            background-color: #e8f5e9; 
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .styled-table th, .styled-table td {
            padding: 12px 15px;
            text-align: center;
        }

        .styled-table thead {
            background-color: #2e7d32; 
            color: white;
        }

        .styled-table tbody tr {
            border-bottom: 1px solid #ddd;
        }

        .styled-table tbody tr:nth-child(even) {
            background-color: #c8e6c9; 
        }

        .styled-table tbody tr:hover {
            background-color: #a5d6a7;
            transition: 0.3s;
        }

        .search-filter-container {
            text-align: center;
            margin-bottom: 15px;
        }

        .search-box {
            padding: 8px;
            width: 300px;
            border: 1px solid #2e7d32;
            border-radius: 5px;
        }

        .filter-dropdown {
            padding: 8px;
            border-radius: 5px;
            margin-left: 10px;
        }

        @media (max-width: 768px) {
            .styled-table {
                width: 95%;
            }
        }
    </style>
</head>
<body id="bodyTag">
    <header class="header" id="header">
        <div class="header_toggle">
            <i class='bx bx-menu' id="header-toggle"></i>
        </div>
    </header>
    <?php include 'sidebar.php'; ?><br>
    <div class="admin-greeting">Good Day, Admin!</div>
    <div id="datetime" class="datetime"></div>

    <section class="about-us">
        <h2 style="color: black; font-size: 20px;">Your Notifications</h2>
        <p class="justified">
            The Notifications section allows you to view and manage important updates regarding requestors' service reservations. Stay updated and never miss any important announcements.
        </p>
    </section>

    <!-- Search & Filter -->
    <div class="search-filter-container">
        <input type="text" class="search-box" id="searchInput" onkeyup="filterTable()" placeholder="Search notifications...">
        <select class="filter-dropdown" id="statusFilter" onchange="filterTable()">
            <option value="">All</option>
            <option value="unread">Unread</option>
            <option value="read">Read</option>
        </select>
    </div>

    <div class="table-container">
        <table class="styled-table" id="notificationsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Message</th>
                    <th>Created At</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notifications as $notification): ?>
                    <tr>
                        <td><?= htmlspecialchars($notification['id']) ?></td>
                        <td><?= htmlspecialchars($notification['message']) ?></td>
                        <td><?= date("F j, Y g:i A", strtotime($notification['created_at'])) ?></td>
                        <td><?= ucfirst($notification['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<script>
function filterTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let statusFilter = document.getElementById("statusFilter").value.toLowerCase();
    let table = document.getElementById("notificationsTable");
    let rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) {
        let cols = rows[i].getElementsByTagName("td");
        let message = cols[1].innerText.toLowerCase();
        let status = cols[3].innerText.toLowerCase();
        let matchesSearch = message.includes(input);
        let matchesStatus = statusFilter === "" || status.includes(statusFilter);

        rows[i].style.display = (matchesSearch && matchesStatus) ? "" : "none";
    }
}
</script>


    <footer>
    <div class="footer-container">
        <div class="footer-about">
        <h3>About Parish of the Holy Cross</h3>
            <p>
                The Parish of the Holy Cross is a sacred place of worship, where the community comes together to celebrate faith, hope, and love. Whether you're seeking spiritual growth, a peaceful moment of reflection, or a place to connect with others, our church provides a welcoming environment for all.
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


<script>
<?php if (!empty($alertMessage)) echo $alertMessage; ?>

function updateDateTime() {
    let now = new Date();
    let options = { timeZone: 'Asia/Manila', hour12: true, weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    document.getElementById('datetime').innerHTML = new Intl.DateTimeFormat('en-PH', options).format(now);
}

updateDateTime();
setInterval(updateDateTime, 60000); 

</script>
<style>
        .header {
            background: #2c3e50;
            color: white;
        }


        .datetime {
            text-align: center;
            font-size: 18px;
            color: #555;
            margin-bottom: 20px;
        }

        .admin-greeting {
            text-align: center;
            font-size: 35px;
            font-weight: bold;
            color: rgb(88, 177, 90);
        }

        body {
            font-family: Arial, sans-serif;
            background-color: rgb(241, 243, 240); 
        }

    </style>
</body>
</html>
