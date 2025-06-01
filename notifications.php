<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';
$user_id = $_SESSION['user_id'];

$sql = "SELECT id, message, status, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


date_default_timezone_set('Asia/Manila');
$current_datetime = date("l, F j, Y g:i A");

$notif_sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND status = 'unread'";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_data = $notif_result->fetch_assoc();
$unread_count = $notif_data['unread_count'];
$notif_stmt->close();

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
</head>
<body id="bodyTag">
    <header class="header" id="header">
        <div class="header_toggle">
            <i class='bx bx-menu' id="header-toggle"></i>
        </div>
    </header>

    <div class="l-navbar" id="nav-bar">
    <nav class="nav">
        <div>
            <a href="#" class="nav_logo">
                <img src="imgs/logo.png" alt="Parish Logo" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                <span class="nav_logo-name">Parish of the Holy Cross</span>
            </a>
            <div class="nav_list">
                <a href="front.php" class="nav_link active">
                    <i class='bx bx-grid-alt nav_icon'></i> 
                    <span class="nav_name">Dashboard</span>
                </a>
                <a href="dashboard.php" class="nav_link">
                    <i class='bx bx-calendar-event nav_icon'></i> 
                    <span class="nav_name">Event Request</span>
                </a>
                <a href="notifications.php" class="nav_link">
                    <i class='bx bx-bell nav_icon'></i> 
                    <span class="nav_name">Notifications</span>
                </a>
                <a href="history.php" class="nav_link">
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

<div class="welcome">
    <center><h2>Notifications</h2></center>
    <center><p>Current Date and Time: <?php echo $current_datetime; ?></p></center>
    </section>

    <section class="about-us">
    <h2 style="color: black; font-size: 20px;">Your Notifications</h2>
        <p class="justified">
            The Notifications section allows you to view and manage important updates regarding your service reservations. Here, you can review recent messages, check unread notifications, and stay informed about any changes or confirmations. Stay updated and never miss any important announcements.
        </p>
    </section>

        <!-- Filtering Search Function -->
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
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notification): ?>
                    <tr>
                        <td><?= htmlspecialchars($notification['id']) ?></td>
                        <td><?= htmlspecialchars($notification['message']) ?></td>
                        <td><?= date("F j, Y g:i A", strtotime($notification['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No notifications found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>
<br>

<!-- CSS Styling -->
<style>
.table-container {
    max-height: 400px; /* Adjust height as needed */
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 10px;
}

/* Ensures the table stays full-width inside the scrolling container */
.styled-table {
    width: 100%;
    border-collapse: collapse;
}

.styled-table thead {
    position: sticky;
    top: 0;
    background-color: #3E8E41;
    color: white;
    text-transform: uppercase;
}

.search-filter-container {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding: 10px;
    background-color: #e0f2e9;
    border-radius: 8px;
}

.search-box, .filter-dropdown {
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

.styled-table th, .styled-table td {
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

.styled-table th, .styled-table td {
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

<script>
    function filterTable() {
    let searchInput = document.getElementById("searchInput").value.toLowerCase();
    let statusFilter = document.getElementById("statusFilter").value;
    let table = document.getElementById("notificationsTable");
    let rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) {
        let message = rows[i].getElementsByTagName("td")[1]?.textContent.toLowerCase() || "";
        let status = rows[i].dataset.status;

        let matchesSearch = message.includes(searchInput);
        let matchesFilter = statusFilter === "" || status === statusFilter;

        rows[i].style.display = matchesSearch && matchesFilter ? "" : "none";
    }
}

</script>

<script src="scriptd.js"></script>
    
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

</body>
</html>
