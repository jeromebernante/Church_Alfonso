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

$sql_notif = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND status = 'success'";
$stmt_notif = $conn->prepare($sql_notif);
$stmt_notif->bind_param("i", $user_id);
$stmt_notif->execute();
$stmt_notif->bind_result($notif_count);
$stmt_notif->fetch();
$stmt_notif->close();

$requests = [
    'pamisa_requests' => 0,
    'baptism_requests' => 0,
    'wedding_requests' => 0,
    'blessings_requests' => 0
];

foreach ($requests as $table => &$count) {
    $sql = "SELECT COUNT(*) FROM $table WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
    }
}

$current_year = date("Y");
$current_month = date("m");

$upcoming_events = [];

$event_tables = [
    'Pamisa' => ['table' => 'pamisa_requests', 'date_column' => 'selected_date'],
    'Baptism' => ['table' => 'baptism_requests', 'date_column' => 'selected_date'],
    'Wedding' => ['table' => 'wedding_requests', 'date_column' => 'wedding_date'],
    'Blessing' => ['table' => 'blessings_requests', 'date_column' => 'blessing_date']
];

foreach ($event_tables as $event_name => $event_info) {
    $sql = "SELECT {$event_info['date_column']} FROM {$event_info['table']} WHERE user_id = ? AND YEAR({$event_info['date_column']}) = ? AND MONTH({$event_info['date_column']}) = ? ORDER BY {$event_info['date_column']} ASC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iii", $user_id, $current_year, $current_month);
        $stmt->execute();
        $stmt->bind_result($event_date);
        while ($stmt->fetch()) {
            $upcoming_events[] = [
                'type' => $event_name,
                'date' => $event_date
            ];
        }
        $stmt->close();
    }
}

$past_events = [];
$current_date = date("Y-m-d"); 

foreach ($event_tables as $event_name => $event_info) {
    $sql = "SELECT {$event_info['date_column']} FROM {$event_info['table']} WHERE user_id = ? AND {$event_info['date_column']} < ? ORDER BY {$event_info['date_column']} DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("is", $user_id, $current_date);
        $stmt->execute();
        $stmt->bind_result($event_date);
        while ($stmt->fetch()) {
            $past_events[] = [
                'type' => $event_name,
                'date' => $event_date
            ];
        }
        $stmt->close();
    }
}

$sql_rates = "SELECT service_name, rate, additional_info FROM rates";
$result_rates = $conn->query($sql_rates);
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
    <h2>Your Dashboard</h2>
    <p class="justified">
        The Dashboard provides an overview of your activities, including past service reservations, upcoming appointments, and important notifications. Stay updated and manage your bookings efficiently all in one place.
    </p>
</section>

<section class="dashboard-stats">
    <div class="stats-container">
        <div class="stat-box">
            <h3>Your Total Request/s: Mass</h3>
            <p><?php echo $requests['pamisa_requests']; ?></p>
        </div>
        <div class="stat-box">
            <h3>Your Total Request/s: Baptism</h3>
            <p><?php echo $requests['baptism_requests']; ?></p>
        </div>
        <div class="stat-box">
            <h3>Your Total Request/s: Wedding</h3>
            <p><?php echo $requests['wedding_requests']; ?></p>
        </div>
        <div class="stat-box">
            <h3>Your Total Request/s: Blessing</h3>
            <p><?php echo $requests['blessings_requests']; ?></p>
        </div>
    </div>
</section>


<section class="rates-section">
    <table>
        <tr>
            <th>Service</th>
            <th>Rate</th>
            <th>Details</th>
        </tr>
        <?php while ($row = $result_rates->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['service_name'] === 'Pamisa' ? 'Mass' : $row['service_name']); ?></td>
                <td>
                    <?php 
                        echo ($row['rate'] !== null) ? "₱" . number_format($row['rate'], 2) : "Donation"; 
                    ?>
                </td>
                <td><?php echo htmlspecialchars($row['additional_info'] ?? ''); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</section>

<section class="dashboard-events-container">
    <!-- Past Events (Left) -->
    <div class="event-box past-events">
        <h3>Your Past Events</h3>
        <?php if (!empty($past_events)): ?>
            <ul>
                <?php foreach ($past_events as $event): ?>
                    <li><strong><?php echo $event['type']; ?></strong> - <?php echo date("F j, Y", strtotime($event['date'])); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No past events found.</p>
        <?php endif; ?>
    </div>

    <!-- Upcoming Events (Right) -->
    <div class="event-box upcoming-events">
        <h3>Your Upcoming Events This Month</h3>
        <?php if (!empty($upcoming_events)): ?>
            <ul>
                <?php foreach ($upcoming_events as $event): ?>
                    <li><strong><?php echo $event['type']; ?></strong> - <?php echo date("F j, Y", strtotime($event['date'])); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No upcoming events this month.</p>
        <?php endif; ?>
    </div>
</section>


<style>
.rates-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    margin-bottom: 20px;
}

.rates-section h3 {
    color: #333;
}

.rates-section table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.rates-section th, .rates-section td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}

.rates-section th {
    background: #007bff;
    color: white;
}

.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.stat-box {
    color: white;
    padding: 30px;
    border-radius: 15px;
    text-align: center;
    font-weight: bold;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    transform: scale(1);
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
}

.stat-box:nth-child(1) { background: linear-gradient(135deg, #007bff, #0056b3); } /* Blue - Pamisa */
.stat-box:nth-child(2) { background: linear-gradient(135deg, #28a745, #1e7e34); } /* Green - Baptism */
.stat-box:nth-child(3) { background: linear-gradient(135deg, #fd7e14, #d35400); } /* Orange - Wedding */
.stat-box:nth-child(4) { background: linear-gradient(135deg, #dc3545, #a71d2a); } /* Red - Blessing */

.stat-box:hover {
    transform: scale(1.05);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
}

.stat-box h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.stat-box p {
    font-size: 24px;
    font-weight: bold;
}

.dashboard-events {
    display: flex;
    justify-content: center;
    margin-top: 30px;
    padding: 20px;
}

.dashboard-events-container {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding: 20px;
    flex-wrap: wrap;
}

.past-events, .upcoming-events {
    background: linear-gradient(135deg, #6f42c1, #d63384); 
    color: white;
    padding: 30px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    width: 48%;
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.8s ease-out forwards;
}

.event-box {
    background: linear-gradient(135deg, #6f42c1, #d63384);
    color: white;
    padding: 30px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    width: 80%;
    max-width: 600px;
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.8s ease-out forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.event-box h3 {
    font-size: 20px;
    margin-bottom: 15px;
    font-weight: 700;
}

.event-box ul {
    list-style: none;
    padding: 0;
}

.event-box ul li {
    font-size: 18px;
    margin-bottom: 8px;
}

.notification-badge {
    background: red;
    color: white;
    padding: 5px 10px;
    border-radius: 50%;
    font-size: 14px;
    margin-left: 5px;
    font-weight: bold;
}

@media (max-width: 768px) {
    .dashboard-events-container {
        flex-direction: column;
    }
    .past-events, .upcoming-events {
        width: 100%;
    }
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
