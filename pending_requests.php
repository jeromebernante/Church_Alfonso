<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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

// Fetch pending Pamisa requests
$sql = "SELECT id, name_of_intended, name_of_requestor, pamisa_type, selected_date, selected_time, price, status, payment_receipt, created_at 
        FROM pamisa_requests WHERE user_id = ? AND status = 'Pending' ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_pamisa = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch pending Wedding requests
$sql = "SELECT id, bride_name, groom_name, contact, wedding_date, payment_receipt, status, created_at 
        FROM wedding_requests WHERE user_id = ? AND status = 'Pending' ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_weddings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch pending Baptism requests
$sql = "SELECT b.id, b.baptized_name, b.parents_name, b.ninongs_ninangs, b.request_date, b.selected_date, b.status, b.price, p.receipt_path 
        FROM baptism_requests b 
        LEFT JOIN baptism_payments p ON b.id = p.baptism_request_id 
        WHERE b.user_id = ? AND b.status = 'Pending' ORDER BY b.request_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_baptisms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch pending Blessings requests
$sql = "SELECT id, type_of_blessing, name_of_requestor, name_of_blessed, blessing_date, blessing_time, receipt_path, status, created_at 
        FROM blessings_requests WHERE user_id = ? AND status = 'Pending' ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_blessings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Notifications - Do not change
$sql_notif = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND status = 'success'";
$stmt_notif = $conn->prepare($sql_notif);
$stmt_notif->bind_param("i", $user_id);
$stmt_notif->execute();
$stmt_notif->bind_result($notif_count);
$stmt_notif->fetch();
$stmt_notif->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parish of the Holy Cross</title>
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
                <a href="dashboard.php" class="nav_link active">
                    <i class='bx bx-grid-alt nav_icon'></i> 
                    <span class="nav_name">Dashboard</span>
                </a>
                <a href="dashboard.php" class="nav_link">
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

                <a href="pending_requests.php" class="nav_link">
                    <i class='bx bx-time-five nav_icon'></i> 
                    <span class="nav_name">Pending Requests</span>
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

    <section class="welcome">
        <center><h2>Good Day, <?php echo htmlspecialchars($user_name); ?>!</h2></center>
        <center><p>Current Date and Time: <?php echo $current_datetime; ?></p></center>
    </section>

    <section class="about-us">
    <h2>Your Pending Requests</h2>
    <p class="justified">
        This section provides an overview of your pending service requests, including upcoming appointments and ongoing requests. Stay informed and track the status of your requests efficiently in one place.
    </p>
</section>


<style>
    .notification-badge {
    background: red;
    color: white;
    padding: 3px 8px;
    border-radius: 50%;
    font-size: 12px;
    margin-left: 5px;
}
    </style>

<script src="scriptd.js"></script>
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
