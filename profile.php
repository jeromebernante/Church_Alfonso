<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connection.php'; 
$user_id = $_SESSION['user_id'];

$sql = "SELECT id, name, email, phone, address, password FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($id, $name, $email, $phone, $address, $password);
$stmt->fetch();
$stmt->close();

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

if ($id < 10) {
    $formatted_id = "0-000-00" . $id;
} elseif ($id < 100) {
    $formatted_id = "0-000-0" . $id;
} else {
    $formatted_id = "0-000-" . $id;
}

$masked_password = str_repeat('*', strlen($password));

date_default_timezone_set('Asia/Manila');
$current_datetime = date("l, F j, Y g:i A");

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
                <a href="front.php" class="nav_link active">
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

<!-- Content -->

    <section class="welcome">
        <center><h2>This is your profile details, <?php echo htmlspecialchars($user_name); ?>!</h2></center>
        <center><p>Current Date and Time: <?php echo $current_datetime; ?></p></center>
    </section>
    <section class="about-us">
    <h2>Your Profile</h2>
    <p class="justified">
    The My Profile section allows you to manage and personalize your account. Here, you can update your personal details, change your password, and customize your preferences to enhance your experience. Keep your information up to date and stay connected with our parish community effortlessly.    </p>
</section>
    <div class="profile-container">
        <h2>My Profile</h2>
        <table>
            <tr><th>User ID</th><td><?php echo htmlspecialchars($formatted_id); ?></td></tr>
            <tr><th>Name</th><td><?php echo htmlspecialchars($name); ?></td></tr>
            <tr><th>Email</th><td><?php echo htmlspecialchars($email); ?></td></tr>
            <tr><th>Contact Number</th><td><?php echo htmlspecialchars($phone); ?></td></tr>
            <tr><th>Address</th><td><?php echo htmlspecialchars($address); ?></td></tr>
            <tr><th>Password</th><td><?php echo htmlspecialchars($masked_password); ?></td></tr>
        </table><br>
        <button id="editProfileBtn" class="edit-btn">Edit Profile</button>
        
    </div>
</div>


<div id="editProfileModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 style="text-align: center; color: #2c3e50; margin-bottom: 15px;">Edit Profile</h2>
        <form id="editProfileForm" style="display: flex; flex-direction: column; gap: 10px;">

            <label for="editName">Name:</label>
            <input type="text" name="name" id="editName" value="<?php echo htmlspecialchars($name); ?>" required>

  
            <label for="editEmail">Email:</label>
            <input type="email" name="email" id="editEmail" value="<?php echo htmlspecialchars($email); ?>" required readonly style="background-color:rgb(170, 164, 164);">

            <label for="editPhone">Contact Number:</label>
            <input type="text" name="phone" id="editPhone" value="<?php echo htmlspecialchars($phone); ?>" required readonly style="background-color: rgb(170, 164, 164);">


            <label for="editAddress">Address:</label>
            <input type="text" name="address" id="editAddress" value="<?php echo htmlspecialchars($address); ?>" required>

            <label for="editNewPassword">New Password <small>(leave blank to keep current)</small>:</label>
            <input type="password" name="new_password" id="editNewPassword">

            <label for="confirmPassword">Confirm Current Password:</label>
            <input type="password" name="current_password" id="confirmPassword" required>

            <button type="button" id="confirmEdit" class="modal-btn">
                <span id="buttonText">Submit</span>
                <div id="loadingSpinner" class="spinner" style="display: none;"></div>
            </button>
        </form>
    </div>
</div>

<style>
.modal-btn {
    background: linear-gradient(135deg, #3498db, #6dd5fa);
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.modal-btn:hover {
    background: linear-gradient(135deg, #2980b9, #56ccf2);
    transform: scale(1.05);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
}

.modal-btn:disabled {
    background: #a0a0a0;
    cursor: not-allowed;
}

.spinner {
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top: 3px solid white;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    animation: spin 0.6s linear infinite;
}


@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
$(document).ready(function () {
    var editModal = $("#editProfileModal");
    var otpModal = $("#otpModal");
    var btn = $("#editProfileBtn");
    var closeBtn = $(".close");

    editModal.hide();
    otpModal.hide();

    btn.click(function () {
        editModal.show();
    });

    closeBtn.click(function () {
        editModal.hide();
        otpModal.hide();
    });

    $(window).click(function (event) {
        if (event.target === editModal[0]) {
            editModal.hide();
        }
        if (event.target === otpModal[0]) {
            otpModal.hide();
        }
    });

    $("#confirmEdit").click(function () {
        var formData = {
            name: $("#editName").val(),
            email: $("#editEmail").val(),
            phone: $("#editPhone").val(),
            address: $("#editAddress").val(),
            new_password: $("#editNewPassword").val(),
            current_password: $("#confirmPassword").val()
        };

        $("#buttonText").text("Processing...");
        $("#loadingSpinner").show();
        $("#confirmEdit").prop("disabled", true);

        $.post("verify_password.php", formData, function (response) {
            $("#loadingSpinner").hide();
            $("#buttonText").text("Submit");
            $("#confirmEdit").prop("disabled", false);

            if (response.status === "success") {
                Swal.fire({
                    title: "OTP Sent!",
                    text: "Please check your email.",
                    icon: "info",
                    confirmButtonText: "OK"
                }).then(() => {
                    editModal.hide();
                    otpModal.show();
                });
            } else {
                Swal.fire({
                    title: "Error!",
                    text: response.message,
                    icon: "error",
                    confirmButtonText: "Try Again"
                });
            }
        }, "json");
    });

    $("#verifyOtp").click(function () {
        var otp = $("#otpInput").val();

        $.post("verify_otp_profile.php", { otp: otp }, function (response) {
            if (response.status === "success") {
                Swal.fire({
                    title: "Success!",
                    text: "Profile updated successfully.",
                    icon: "success",
                    confirmButtonText: "OK"
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: "Error!",
                    text: "Invalid OTP.",
                    icon: "error",
                    confirmButtonText: "Try Again"
                });
            }
        }, "json");
    });
});


</script>

<div id="otpModal" class="modal">
    <div class="modal-content">
        <h2>Enter OTP</h2>
        <input type="text" id="otpInput" placeholder="Enter OTP">
        <button type="button" id="verifyOtp" style="
        background-color: #4CAF50; 
        color: white; 
        border: none; 
        padding: 10px 20px; 
        font-size: 16px; 
        border-radius: 8px; 
        cursor: pointer; 
        transition: background 0.3s, transform 0.2s;
    "
    onmouseover="this.style.backgroundColor='#45a049'; this.style.transform='scale(1.05)';"
    onmouseout="this.style.backgroundColor='#4CAF50'; this.style.transform='scale(1)';">
        Verify
    </button>

    </div>
</div>



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
    .notification-badge {
    background: red;
    color: white;
    padding: 3px 8px;
    border-radius: 50%;
    font-size: 12px;
    margin-left: 5px;
}
    
   .content {
    margin-left: 260px;
    padding: 50px;
    background: #f8f9fa; 
    min-height: 100vh;
}

.profile-container {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    max-width: 900px;
    margin: auto;
}

.profile-container h2 {
    color: #3b7302; 
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

th, td {
    padding: 12px;
    text-align: left;
}

th {
    background: #265f27;
    color: white;
    font-weight: bold;
    text-transform: uppercase;
}

td {
    background: #e9f5e9;
    color: #333;
}

tr:nth-child(even) td {
    background: #d9edd9;
}

tr:hover td {
    background: #c8e6c9;
    transition: background 0.3s ease-in-out;
}


.back-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background: #3b7302;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: 0.3s;
}

.back-btn:hover {
    background: #295102; 
}


@media screen and (max-width: 768px) {
    .content {
        margin-left: 0;
        padding: 10px;
    }

    .profile-container {
        width: 90%;
    }
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    padding: 20px;
    width: 350px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    position: relative;
}

.close {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 20px;
    cursor: pointer;
}

form label {
    font-weight: bold;
    margin-bottom: 5px;
    color: #2c3e50;
}

form input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

.modal-btn {
    background: #3b7302;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 10px;
    transition: 0.3s ease;
}

.modal-btn:hover {
    background: #2980b9;
}

.edit-btn {
    background: linear-gradient(135deg, #3b7302, #3b7302);
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.edit-btn:hover {
    background: linear-gradient(135deg,rgb(5, 66, 107), #56ccf2);
    transform: scale(1.05);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
}

.edit-btn:active {
    transform: scale(0.98);
}
    </style>
</body>
</html>
