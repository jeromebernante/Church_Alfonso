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
$current_day = date('l');
// if ($current_day === 'Monday') {
//     echo '
//     <style>
//         body {
//             background-color: white !important;
//             overflow: hidden;
//         }
//     </style>
//     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
//     <script>
//         document.addEventListener("DOMContentLoaded", function() {
//             Swal.fire({
//                 icon: "info",
//                 title: "Scheduling Closed",
//                 text: "Scheduling is not available on Mondays. Please come back another day.",
//                 confirmButtonText: "OK"
//             }).then(() => {
//                 window.location.href = "front.php"; // You can change this if needed
//             });
//         });
//     </script>
//     ';
//     exit();
// }


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
                    <a href="event_request.php" class="nav_link active">
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
        <center>
            <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
        </center>
        <center>
            <p>Current Date and Time: <?php echo $current_datetime; ?></p>
        </center>
    </section>
    <section class="about-us">
        <h2>About Us</h2>
        <p class="justified">
            We are a community dedicated to spreading love, peace, and faith. Our parish in Valenzuela City is a place
            for everyone to come together, learn, grow, and find inspiration. With a rich history, we continue to serve
            with passion, welcoming all who wish to be a part of our mission. Join us in our journey as we strive to
            make a positive impact on our community and the world.
        </p>
    </section>
    <section class="main-content">
        <div class="calendar-container"
            style="display: flex; justify-content: space-between; gap: 20px; padding: 20px;">
            <div id="calendar" style="flex: 1; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
            </div>

            <div class="services-buttons" style="flex: 0.3; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <button id="pamisa" class="service-btn">Mass Intention</button>
                <button id="baptismBtn" class="service-btn">Baptism</button>
                <button id="blessing" class="service-btn">Blessing</button>
                <button id="wedding" class="service-btn">Wedding</button>



                <!-- Wedding Modal -->
                <div id="weddingModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Wedding Service</h2>
                        <p style="color: red; font-weight: bold; font-size: 15px;">⚠️ You are allowed to set a wedding
                            request at least 3 months after the seminar.</p>

                        <div
                            style="border: 1px solid #ccc; border-radius: 10px; padding: 15px; background-color: #f9f9f9; margin-bottom: 20px;">
                            <h3 style="margin-top: 0; color: #333;">💒 Wedding Payment Information</h3>

                            <p style="font-size: 16px; font-weight: bold; color: #000;">💰 Amount: <span
                                    style="color: #4CAF50;">₱7,000.00</span></p>

                            <p style="font-size: 15px; color: #000;">📱 GCash Account Name: <strong>CH*****
                                    AL*****</strong><br>
                                📞 GCash Number: <strong>0991 189 5057</strong></p>

                            <div style="text-align: center; margin: 10px 0;">
                                <img src="./imgs/qr.png" alt="GCash QR Code"
                                    style="max-width: 200px; border-radius: 8px; box-shadow: 0 0 8px rgba(0,0,0,0.1);">
                            </div>

                            <p style="font-size: 14px; color: #666; font-style: italic;">* Please upload a screenshot of
                                your GCash payment receipt below to confirm your booking.</p>
                        </div>

                        <br>

                        <!-- Wedding Form -->
                        <form id="weddingForm" enctype="multipart/form-data">
                            <label for="brideName">Bride's Name:</label>
                            <input type="text" id="brideName" name="brideName" required>

                            <label for="groomName"><br>Groom's Name:</label>
                            <input type="text" id="groomName" name="groomName" required>

                            <label for=""><br>Select Priest: <br></label>

                            <select name="priest_name" id="priest_name" style="padding:10px">

                                <?php

                                $select_priest = mysqli_query($conn, 'SELECT * FROM priests');

                                if (mysqli_num_rows($select_priest) > 0) {
                                    while ($row = mysqli_fetch_assoc($select_priest)) {
                                ?>

                                        <option value="<?php echo $row['name'] ?>"><?php echo $row['name'] ?></option>

                                <?php
                                    }
                                }
                                ?>
                            </select>

                            <label for="contact"><br>Contact Number:</label>
                            <input type="text" id="contact" name="contact" required>

                            <label for="weddingDate"><br>Select Wedding Date:</label>
                            <input type="date" id="weddingDate" name="weddingDate" required>

                            <label for="gcashReceipt"><br>Upload GCash Receipt:</label>
                            <input type="file" id="gcashReceipt" name="gcashReceipt"
                                accept="image/png, image/jpeg, image/jpg" required>

                            <button type="submit" style="background-color: #4CAF50; border: none; color: white; padding: 15px 70px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 10px 2px; cursor: pointer; border-radius: 12px;">Submit Request</button>
                        </form>

                        <button id="closeModalBtn" style="background-color: rgb(189, 32, 32); border: none; color: white; padding: 15px 105px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 4px 2px; cursor: pointer; border-radius: 12px;">Close</button>
                    </div>
                </div>

                <script>
                    document.getElementById("weddingForm").addEventListener("submit", function(event) {
                        event.preventDefault();

                        let formData = new FormData(this);

                        fetch("wedding_request.php", {
                                method: "POST",
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === "success") {
                                    Swal.fire("Success!", data.message, "success").then(() => {
                                        document.getElementById("weddingModal").style.display = "none";
                                        document.getElementById("weddingForm").reset();
                                    });
                                } else {
                                    Swal.fire("Error!", data.message, "error");
                                }
                            })
                            .catch(error => console.error("Error:", error));
                    });
                </script>

                <script>
                    document.getElementById("wedding").addEventListener("click", function() {
                        document.getElementById("weddingModal").style.display = "flex";
                    });

                    document.querySelector(".close").addEventListener("click", function() {
                        document.getElementById("weddingModal").style.display = "none";
                    });

                    document.getElementById("closeModalBtn").addEventListener("click", function() {
                        document.getElementById("weddingModal").style.display = "none";
                    });

                    window.addEventListener("click", function(event) {
                        let modal = document.getElementById("weddingModal");
                        if (event.target === modal) {
                            modal.style.display = "none";
                        }
                    });

                    document.getElementById("weddingDate").addEventListener("input", function() {
                        let selectedDate = new Date(this.value);
                        let minDate = new Date();
                        minDate.setMonth(minDate.getMonth() + 3);

                        if (selectedDate < minDate) {
                            Swal.fire({
                                icon: "warning",
                                title: "Invalid Date",
                                text: "Please select a wedding date at least 3 months from today.",
                                confirmButtonColor: "#d33"
                            });
                            this.value = "";
                        }
                    });

                    document.getElementById("gcashReceipt").addEventListener("change", function() {
                        let file = this.files[0];
                        let allowedExtensions = ["image/png", "image/jpeg", "image/jpg"];

                        if (file && !allowedExtensions.includes(file.type)) {
                            Swal.fire({
                                icon: "error",
                                title: "Invalid File Type",
                                text: "Please upload an image file (PNG, JPG, JPEG) only.",
                                confirmButtonColor: "#d33"
                            });
                            this.value = "";
                        }
                    });

                    document.getElementById("weddingForm").addEventListener("submit", function(event) {
                        event.preventDefault();

                        let brideName = document.getElementById("brideName").value.trim();
                        let groomName = document.getElementById("groomName").value.trim();
                        let priestName = document.getElementById("priest_name").value;
                        let contact = document.getElementById("contact").value.trim();
                        let weddingDate = document.getElementById("weddingDate").value;
                        let gcashReceipt = document.getElementById("gcashReceipt").files[0];

                        if (!brideName || !groomName || !contact || !weddingDate || !gcashReceipt) {
                            Swal.fire({
                                icon: "error",
                                title: "Missing Information",
                                text: "Please fill in all fields and upload your GCash receipt before submitting.",
                                confirmButtonColor: "#d33"
                            });
                            return;
                        }

                        Swal.fire({
                            icon: "success",
                            title: "Request Submitted",
                            html: `<strong>Bride:</strong> ${brideName}<br>
                   <strong>Groom:</strong> ${groomName}<br>
                   <strong>Priest:</strong> ${priestName}<br>
                   <strong>Contact Number:</strong> ${contact}<br>
                   <strong>Date:</strong> ${weddingDate}<br>
                   <strong>GCash Receipt Uploaded</strong> ✅`,
                            confirmButtonColor: "#28a745"
                        }).then(() => {
                            document.getElementById("weddingModal").style.display = "none";
                            document.getElementById("weddingForm").reset();
                        });
                    });
                </script>


                <div id="service-details" style="padding: 20px; margin-top: 20px; border-top: 1px solid #ccc;">
                    <center>
                        <h3>Service Details</h3>
                    </center><br>
                    <p id="service-info">Please select a date and a service.</p>

                    <!-- Pamisa Form -->
                    <form id="pamisa-form" style="display: none; margin-top: 20px;">
                        <label for="pamisa-time" style="font-weight: bold; margin-bottom: 5px;">Time:</label>
                        <select id="pamisa-time" class="dropdown-field" required
                            style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
                            <option value="8:00AM">8:00AM</option>
                            <option value="10:00AM">10:00AM</option>
                        </select>

                        <label for="name-of-intended" style="font-weight: bold; margin-bottom: 5px;">Mass of offered:</label>
                        <input type="text" id="name-of-intended" class="input-field" placeholder="Enter name" required
                            style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

                        <label for="name-of-requestor" style="font-weight: bold; margin-bottom: 5px;">Mass requested by:</label>
                        <input type="text" id="name-of-requestor" class="input-field" placeholder="Enter name" required
                            style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

                        <label for="pamisa-type" style="font-weight: bold; margin-bottom: 5px;">Mass Intention:</label>
                        <select id="pamisa-type" class="dropdown-field" required
                            style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
                            <option value="Thanksgiving">Thanksgiving</option>
                            <option value="Birthday">Birthday</option>
                            <option value="Special Intention">Special Intention</option>
                            <option value="Speedy Recovery">Speedy Recovery</option>
                            <option value="Wedding Anniversary">Wedding Anniversary</option>
                            <option value="Souls">Souls</option>
                        </select>
                        <div class="payment-card">

                            <h2 style="margin-bottom: 10px; font-size: 24px;">GCash Payment</h2>
                            <p style="font-size: 15px; color: #333;">Please upload a screenshot of your GCash payment.</p>

                            <div style="background-color: #f9f9f9; border-radius: 8px; padding: 15px; margin: 15px 0;">
                                <img src="./imgs/qr.png" alt="GCash QR Code" style="max-width: 180px; border-radius: 8px; margin-bottom: 10px;">
                                <p style="margin: 5px 0; font-size: 14px;"><strong>Amount to Pay:</strong> ₱100</p>
                                <p style="margin: 5px 0; font-size: 14px;"><strong>GCash Number:</strong> 0991 189 5057</p>
                                <p style="margin: 5px 0; font-size: 14px;"><strong>GCash Name:</strong> CHR**** AL****</p>
                            </div>

                            <input type="file" id="gcash-receipt" accept="image/*,application/pdf" required
                                style="margin-bottom: 15px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 100%; box-sizing: border-box;">

                        </div>
                        <button type="submit" id="save-pamisa" class="service-submit-btn" style="background-color: #4CAF50; color: white; border: none; padding: 12px 20px; 
                    font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer; 
                    transition: background 0.3s ease-in-out; box-shadow: 2px 2px 5px rgba(0,0,0,0.2);">
                            Save Mass
                        </button>

                    </form>

                    <!-- Baptism Form -->
                    <form id="baptism-form" style="display: none; margin-top: 20px;">
                        <label for="baptized-name" style="font-weight: bold; margin-bottom: 5px;">Name of Baptized Person:</label>
                        <input type="text" id="baptized-name" class="input-field" placeholder="Enter name" required
                            style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

                        <label style="font-weight: bold; margin-bottom: 5px;">Parents:</label>
                        <br><br>
                        <label for="father-name" style="font-weight: bold; margin-bottom: 5px;">Name of Father:</label>
                        <input type="text" id="father-name" name="parents_name[]" class="input-field" placeholder="Father First name, Last name"
                            style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
                        <label for="mother-name" style="font-weight: bold; margin-bottom: 5px;">Name of Mother:</label>
                        <input type="text" id="mother-name" name="parents_name[]" class="input-field" placeholder="Mother first name, last name"
                            style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

                        <div id="ninongNinangFields">
                            <label for="ninong-ninang" style="font-weight: bold; margin-bottom: 5px;">Godfather and Godmother:</label>
                            <input type="text" name="ninong_ninang[]" class="input-field" placeholder="First name, Last name" required
                                style="width: 100%; padding: 12px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
                            <input type="text" name="ninong_ninang[]" class="input-field" placeholder="First name, Last name" required
                                style="width: 100%; padding: 12px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
                        </div>
                        <button type="button" id="addMore" class="add-more-btn" style="background-color: #007BFF; color: white; border: none; padding: 12px 20px; 
                            font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer; 
                            transition: background 0.3s ease-in-out; box-shadow: 2px 2px 5px rgba(0,0,0,0.2);">
                            Add More Ninong/Ninang
                        </button>
                        <br><br>

                        <button type="submit" id="save-baptism" class="service-submit-btn" style="background-color: #4CAF50; color: white; border: none; padding: 12px 20px; 
                            font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer; 
                            transition: background 0.3s ease-in-out; box-shadow: 2px 2px 5px rgba(0,0,0,0.2);">
                            Save Baptism
                        </button>
                    </form>

                    <button id="proceed-payment" style="display: none; margin-top: 15px;">Proceed to Payment</button>

                    <!-- Blessing Form -->
                    <form id="blessing-form" style="display: none; margin-top: 20px;">
                        <!-- Priest Select -->
                        <label for="priest_name" style="font-weight: bold;">Select Priest: <br></label>
                        <select name="priest_name" id="priest_name"
                            style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
                            <option value="" selected disabled>Select Priest</option>
                            <?php
                            $select_priest = mysqli_query($conn, 'SELECT * FROM priests');
                            if (mysqli_num_rows($select_priest) > 0) {
                                while ($row = mysqli_fetch_assoc($select_priest)) {
                            ?>
                                    <option value="<?php echo $row['name'] ?>"><?php echo $row['name'] ?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>

                        <!-- Blessing Time Select -->
                        <label for="blessing-time" style="font-weight: bold; margin-bottom: 5px;">Time:</label>
                        <select id="blessing-time" name="blessing_time" class="dropdown-field" required
                            style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
                            <option value="" selected disabled>Select Priest and Schedule First</option>
                        </select>

                        <!-- Name of Blessed -->
                        <label for="name-of-blessed" style="font-weight: bold; margin-bottom: 5px;">Subject of the Blessing:</label>
                        <input type="text" id="name-of-blessed" name="name_of_blessed" class="input-field"
                            placeholder="Enter name" required
                            style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

                        <!-- Requestor Name -->
                        <label for="name-of-requestor-blessing" style="font-weight: bold; margin-bottom: 5px;">Name of the Requester:</label>
                        <input type="text" id="name-of-requestor-blessing" name="name_of_requestor" class="input-field"
                            placeholder="Enter name" required
                            style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

                        <!-- Type of Blessing -->
                        <label for="type-of-blessing" style="font-weight: bold; margin-bottom: 5px;">Type of
                            Blessing:</label>
                        <select id="type-of-blessing" name="type_of_blessing" class="dropdown-field" required
                            style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
                            <option value="For House">For House</option>
                            <option value="For Deceased">For Deceased</option>
                            <option value="For Car">For Car</option>
                            <option value="For Business">For Business</option>
                        </select>

                        <!-- GCash Section -->
                        <div
                            style="margin-bottom: 25px; padding: 20px; border: 1px solid #ddd; border-radius: 12px; background-color: #f9f9f9; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <h3 style="margin-top: 0; font-size: 18px; font-weight: bold; color: #333;">🙏 Donate via
                                GCash</h3>

                            <img src="./imgs/qr.png" alt="GCash QR Code"
                                style="max-width: 200px; border-radius: 10px; margin: 15px 0; box-shadow: 0 0 5px rgba(0,0,0,0.1);">

                            <p style="margin: 0; font-size: 16px; font-weight: 600; color: #000;">📱 GCash Number: <span
                                    style="color: #4CAF50;">0991 189 5057</span></p>

                            <p style="font-size: 14px; color: #555; margin-top: 8px;">💡 <em>Any donation will do. Thank
                                    you for your generosity!</em></p>
                        </div>

                        <!-- Donation Receipt -->
                        <label for="donation-receipt" style="font-weight: bold; margin-bottom: 5px;">GCash or Bank
                            Transfer Receipt for Donation:</label>
                        <input type="file" id="donation-receipt" name="donation_receipt" class="input-field"
                            accept="image/*,application/pdf"
                            style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

                        <!-- Submit Button -->
                        <button type="submit" id="save-blessing" class="service-submit-btn" style="background-color: #4CAF50; color: white; border: none; padding: 12px 20px; 
                            font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer; 
                            transition: background 0.3s ease-in-out; box-shadow: 2px 2px 5px rgba(0,0,0,0.2);">
                            Save Blessing
                        </button>
                    </form>

                </div>
            </div>

            <div id="payment-modal" class="modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); 
                justify-content: center; align-items: center; z-index: 1000;">

                <div class="modal-content" style="background: white; padding: 25px 20px; border-radius: 10px; width: 90%; max-width: 420px; 
               text-align: center; position: relative; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">

                    <span class="close" id="close-modal" style="position: absolute; top: 10px; right: 15px; font-size: 24px; 
                   font-weight: bold; cursor: pointer;">&times;</span>

                    <h2 style="margin-bottom: 10px;">GCash Payment</h2>
                    <p style="font-size: 15px;">Please upload a screenshot of your GCash payment.</p>

                    <div style="background-color: #f9f9f9; border-radius: 8px; padding: 15px; margin: 15px 0;">
                        <img src="./imgs/qr.png" alt="GCash QR Code"
                            style="max-width: 180px; border-radius: 8px; margin-bottom: 10px;">
                        <p style="margin: 5px 0;"><strong>Amount to Pay:</strong> ₱100 per head</p>
                        <p style="margin: 5px 0;"><strong>GCash Number:</strong> 0991 189 5057</p>
                        <p style="margin: 5px 0;"><strong>GCash Name:</strong> CHR**** AL****</p>
                        <p style="font-size: 13px; color: #666;"><em>Any donation will do. Thank you for your
                                generosity!</em></p>
                    </div>

                    <input type="file" id="gcash-receipt" accept="image/*,application/pdf" required
                        style="margin-bottom: 15px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 100%;">

                    <button id="submit-payment" style="background-color: #4CAF50; color: white; border: none; 
                   padding: 12px 25px; font-size: 16px; font-weight: bold; 
                   border-radius: 8px; cursor: pointer; margin-top: 10px;">
                        Submit Payment
                    </button>
                </div>
            </div>


        </div>

    </section>
    
    <div id="loading-spinner"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; z-index: 9999;">
        <div
            style="width: 50px; height: 50px; border: 5px solid #fff; border-top-color: #3498db; border-radius: 50%; animation: spin 1s linear infinite;">
        </div>
    </div>

    <style>
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <!-- Baptism Payment Modal -->
    <div id="payment-modal-baptism" class="modal" style="
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                justify-content: center;
                align-items: center;
                z-index: 1000;
            ">

        <div class="modal-content" style="
                background: white;
                padding: 25px 20px;
                border-radius: 10px;
                width: 90%;
                max-width: 420px;
                text-align: center;
                box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            ">

            <h2 style="margin-bottom: 10px;">🧾 Upload Payment Receipt</h2>
            <p style="font-size: 14px; color: #444;">Please upload a screenshot of your GCash or Bank Transfer
                receipt below.</p>

            <div style="margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-radius: 8px;">
                <h3 style="margin-top: 0; font-size: 16px; color: #333;">📱 GCash Details</h3>
                <img src="./imgs/qr.png" alt="GCash QR Code"
                    style="max-width: 180px; border-radius: 8px; margin-bottom: 10px; box-shadow: 0 0 5px rgba(0,0,0,0.1);">
                <p style="margin: 0; font-weight: bold; font-size: 15px;">GCash Number: <span
                        style="color: #4CAF50;">0991 189 5057</span></p>
                <p style="font-size: 13px; color: #666;"><em>Any donation will do. Thank you for your
                        generosity!</em></p>
            </div>

            <p id="baptism-payment-amount"
                style="font-size: 16px; font-weight: bold; color: #333; margin-bottom: 5px;">
                Total to Pay: ₱0
            </p>

            <p style="margin: 0; font-size: 14px; color: #444;">
                Base Rate: ₱500
            </p>

            <small style="display: block; margin-bottom: 15px; font-size: 13px; color: #888;">
                * First 2 Ninongs/Ninangs are free. Additional entries cost ₱30 each.
            </small>

            <p style="font-size: 14px; color: #d32f2f; font-weight: bold; margin: 10px 0;">
                Disclaimer: Only the full payment of ₱500 will be accepted. No other amount will be considered.
            </p>

            <input type="file" id="gcash-receipt-baptism" accept="image/*,application/pdf" required
                style="margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 100%;">

            <div>
                <button id="submit-payment-baptism" style="background-color: #4CAF50; color: white; border: none; padding: 10px 30px; 
                        font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer;">
                    Submit Payment
                </button>
                <button id="close-modal-baptism" style="margin-left: 10px; background-color: #ccc; color: black; border: none; 
                        padding: 10px 30px; font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer;">
                    Close
                </button>
            </div>
        </div>
    </div>



    <script>
        $(document).ready(function() {
            function renderCalendar() {
                $('#calendar').fullCalendar({
                    selectable: true,
                    selectHelper: true,
                    timezone: 'Asia/Manila',
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                    },
                    events: [],
                    dayRender: function(date, cell) {
                        if ($('#baptism-form').is(':visible')) {
                            if (date.day() === 0) { // Sunday
                                if (date.isBefore(moment(), 'day')) { // Past Sundays
                                    cell.css('background-color', '#F08080'); // Red background
                                    cell.append('<br><div style="color: white; font-size: 20px; text-align: center; font-weight: bold;">Ended</div>');
                                    cell.addClass('fully-booked'); // Prevent selection
                                } else { // Future Sundays
                                    cell.css('background-color', '#7ea67b'); // Green background
                                    $.getJSON('fetch_slots.php', {
                                        date: date.format('YYYY-MM-DD')
                                    }, function(data) {
                                        let slotsText = data.slots_remaining > 0 ?
                                            `${data.slots_remaining} SLOTS REMAINING` :
                                            `Fully Booked`;
                                        cell.append(`<br><div style="color: white; font-size: 20px; text-align: center; font-weight: bold;">${slotsText}</div>`);
                                        if (data.slots_remaining <= 0) {
                                            cell.addClass('fully-booked');
                                        }
                                    });
                                }
                            } else {
                                cell.css('background-color', '#F08080');
                                cell.append('<br><div style="color: white; font-size: 13px; text-align: center; font-weight: bold;">Not Allowed for Baptism Schedule</div>');
                            }
                        } else if ($('#blessing-form').is(':visible')) {
                            if (date.day() === 5 || date.day() === 6) { // Friday or Saturday
                                cell.css('background-color', '#7ea67b');
                                $.getJSON('fetch_slots.php', {
                                    date: date.format('YYYY-MM-DD'),
                                    service: 'blessing'
                                }, function(data) {
                                    let slotsText = data.slots_remaining > 0 ? `${data.slots_remaining} SLOTS REMAINING` : `Fully Booked`;
                                    cell.append(`<br><div style="color: white; font-size: 20px; text-align: center; font-weight: bold;">${slotsText}</div>`);
                                    if (data.slots_remaining <= 0) cell.addClass('fully-booked');
                                });
                            } else {
                                cell.css('background-color', '#F08080');
                                cell.append('<br><div style="color: white; font-size: 13px; text-align: center; font-weight: bold;">Not Allowed for Blessings</div>');
                            }
                        }
                    },
                    select: function(start, end) {
                        let selectedDate = start.format('YYYY-MM-DD');
                        let selectedDay = moment(selectedDate).format('dddd');
                        let selectedCell = $('.fc-day[data-date="' + selectedDate + '"]');

                        if (selectedCell.hasClass('fully-booked')) {
                            Swal.fire("Invalid Selection", "This date is not available for booking.", "warning");
                            $('#calendar').fullCalendar('unselect');
                            return;
                        }

                        if ($('#baptism-form').is(':visible') && selectedDay === "Sunday" && moment(selectedDate).isBefore(moment(), 'day')) {
                            Swal.fire("Invalid Selection", "Baptism bookings for past Sundays are no longer available.", "error");
                            $('#calendar').fullCalendar('unselect');
                            return;
                        }

                        if ($('#baptism-form').is(':visible')) {
                            if (selectedDay !== "Sunday") {
                                Swal.fire("Invalid Selection", "Only Sundays are allowed for Baptism bookings.", "error");
                                $('#calendar').fullCalendar('unselect');
                                return;
                            }
                        }

                        let events = $('#calendar').fullCalendar('clientEvents', function(event) {
                            return event.start.format('YYYY-MM-DD') === selectedDate;
                        });

                        let timeOptions = {
                            "Monday": ["6:30AM"],
                            "Tuesday": ["6:30AM"],
                            "Wednesday": ["6:00PM"],
                            "Thursday": ["6:30AM"],
                            "Friday": ["6:30AM"],
                            "Saturday": ["6:30AM", "5:00PM"],
                            "Sunday": ["6:00AM", "8:00AM", "10:00AM", "5:00PM", "6:30PM"]
                        };

                        let availableTimes = timeOptions[selectedDay] || [];

                        if (availableTimes.length === 0) {
                            Swal.fire("No Available Time", "No mass schedules for " + selectedDay, "error");
                            $('#calendar').fullCalendar('unselect');
                            return;
                        }

                        if (selectedDay === "Saturday" || selectedDay === "Sunday") {
                            if (events.length === availableTimes.length) {
                                Swal.fire("Fully Booked", "All time slots for this day are booked. Please choose another date.", "warning");
                                $('#calendar').fullCalendar('unselect');
                                return;
                            }
                        } else {
                            if (events.length > 0) {
                                Swal.fire("Date Not Available", "This date is already booked. Please choose another date.", "warning");
                                $('#calendar').fullCalendar('unselect');
                                return;
                            }
                        }

                        $('#service-info').html('<strong>Selected Date:</strong> ' + selectedDate + '.<br><br><strong>Time: </strong>11:30 AM');

                        let timeSelect = $('#pamisa-time');
                        timeSelect.empty();
                        availableTimes.forEach(time => {
                            if (!events.some(event => event.start.format('HH:mm A') === time)) {
                                timeSelect.append(new Option(time, time));
                            }
                        });

                        $('#proceed-payment').hide();
                    }
                });
            }

            renderCalendar();

            $('#pamisa').click(function() {
                $('#calendar').fullCalendar('destroy');

                setTimeout(function() {
                    renderPamisaCalendar();
                    $('#calendar').fullCalendar('removeEvents');

                    $('#service-info').html('<strong>Service:</strong> Blessing<br><br><span style="color: red;">Please Select a Date</span>');
                    $('#pamisa-form').show();
                    $('#baptism-form').hide();
                    $('#blessing-form').hide();
                    $('#proceed-payment').hide();
                }, 100);
            });


            function renderPamisaCalendar() {
                $('#calendar').fullCalendar({
                    selectable: true,
                    selectHelper: true,
                    timezone: 'Asia/Manila',
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                    },
                    events: [],

                    dayRender: function(date, cell) {
                        // Clear any existing styles and content
                        cell.css('background-color', '');
                        cell.find('div').remove();

                        // Apply red background color (F08080) to past dates
                        if (moment(date).isBefore(moment(), 'day')) {
                            cell.css('background-color', '#F08080');
                            cell.append('<br><div style="color: white; font-size: 20px; text-align: center; font-weight: bold;">Ended</div>');
                        }
                    },

                    select: function(start, end) {
                        let selectedDate = start.format('YYYY-MM-DD');

                        // Check if the selected date is in the past
                        if (moment(selectedDate).isBefore(moment(), 'day')) {
                            Swal.fire("Invalid Selection", "Pamisa for past dates are no longer available.", "error");
                            $('#calendar').fullCalendar('unselect');
                            return;
                        }

                        let selectedDay = moment(selectedDate).format('dddd');

                        let events = $('#calendar').fullCalendar('clientEvents', function(event) {
                            return event.start.format('YYYY-MM-DD') === selectedDate;
                        });

                        let timeOptions = {
                            "Monday": ["6:30AM"],
                            "Tuesday": ["6:30AM"],
                            "Wednesday": ["6:00PM"],
                            "Thursday": ["6:00AM"],
                            "Friday": ["6:30AM"],
                            "Saturday": ["6:30AM", "5:00PM"],
                            "Sunday": ["6:00AM", "8:00AM", "10:00AM", "5:00PM", "6:30PM"]
                        };

                        let availableTimes = timeOptions[selectedDay] || [];

                        $('#service-info').html('<strong>Selected Date:</strong> ' + selectedDate + '.<br>');

                        let timeSelect = $('#pamisa-time');
                        timeSelect.empty();
                        availableTimes.forEach(time => {
                            if (!events.some(event => event.start.format('HH:mm A') === time)) {
                                timeSelect.append(new Option(time, time));
                            }
                        });

                        $('#proceed-payment').hide();
                    }
                });
            }

            function renderBlessingCalendar() {
                if (!$('#calendar').length) {
                    console.error('Calendar element not found.');
                    Swal.fire("Error", "Calendar element not found. Please check the page.", "error");
                    return;
                }

                $('#calendar').fullCalendar('destroy');
                $('#calendar').fullCalendar({
                    selectable: true,
                    selectHelper: true,
                    timezone: 'Asia/Manila',
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                    },
                    events: function(start, end, timezone, callback) {
                        $.ajax({
                            url: "fetch_blessings.php?priest_name=" + encodeURIComponent($('#blessing-form #priest_name').val()),
                            type: "GET",
                            dataType: "json",
                            success: function(response) {
                                console.log('Fetched priest:', $('#blessing-form #priest_name').val());
                                window.bookedSlots = response.bookedSlots || {};
                                callback(response.events || []);
                            },
                            error: function(xhr, status, error) {
                                console.error('Error fetching blessing events:', status, error);
                                Swal.fire("Error", "Failed to load blessing schedule. Please try again.", "error");
                                callback([]);
                            }
                        });
                    },
                    displayEventTime: true,
                    eventRender: function(event, element) {
                        element.find('.fc-title').text(event.title);
                        element.find('.fc-time').text(event.start.format('h:mm A'));

                        element.css({
                            'background-color': event.backgroundColor || '#ff0000',
                            'border-color': event.borderColor || '#ff0000',
                            'color': event.textColor || '#ffffff'
                        });
                    },
                    dayRender: function(date, cell) {
                        if (date.day() === 5 || date.day() === 6) { // Friday or Saturday
                            if (date.isBefore(moment(), 'day')) { // Past Fridays or Saturdays
                                cell.css('background-color', '#F08080'); // Red background
                                cell.append('<br><div style="color: white; font-size: 20px; text-align: center; font-weight: bold;">Ended</div>');
                                cell.addClass('fully-booked'); // Prevent selection
                            } else { // Future Fridays or Saturdays
                                cell.css('background-color', '#7ea67b'); // Green background
                                $.getJSON('fetch_slots.php', {
                                    date: date.format('YYYY-MM-DD'),
                                    service: 'blessing'
                                }, function(data) {
                                    if (data.slots_remaining > 0) {
                                        cell.append(`<br><div style="color: white; font-size: 20px; text-align: center; font-weight: bold;">${data.slots_remaining} SLOTS REMAINING</div>`);
                                    }
                                    if (data.slots_remaining <= 0) {
                                        cell.addClass('fully-booked');
                                    }
                                });
                            }
                        } else {
                            cell.css('background-color', '#F08080');
                            cell.append('<br><div style="color: white; font-size: 13px; text-align: center; font-weight: bold;">Not Allowed for Blessings</div>');
                        }
                    },
                    select: function(start) {
                        let selectedDate = start.format('YYYY-MM-DD');
                        let selectedDay = moment(selectedDate).format('dddd');

                        if (!(start.day() === 5 || start.day() === 6)) {
                            Swal.fire("Invalid Selection", "Blessings can only be scheduled on Fridays and Saturdays.", "error");
                            $('#calendar').fullCalendar('unselect');
                            return;
                        }

                        if (moment(selectedDate).isBefore(moment(), 'day')) {
                            Swal.fire("Invalid Selection", "Blessing bookings for past dates are no longer available.", "error");
                            $('#calendar').fullCalendar('unselect');
                            return;
                        }

                        let bookedTimes = window.bookedSlots && window.bookedSlots[selectedDate] ? window.bookedSlots[selectedDate] : [];
                        console.log('Booked times for', selectedDate, ':', bookedTimes);

                        // Collect all event.start times for the selected date
                        let eventStartTimes = [];
                        $('#calendar').fullCalendar('clientEvents', function(event) {
                            if (event.start.format('YYYY-MM-DD') === selectedDate) {
                                let eventTime = event.start.format('HH:mm:ss');
                                if (!eventStartTimes.includes(eventTime)) { // Avoid duplicates
                                    eventStartTimes.push(eventTime);
                                }
                            }
                        });
                        console.log(`Event start times for ${selectedDate}:`, eventStartTimes);

                        // Define default required times
                        let defaultRequiredTimes = ["09:00:00", "11:00:00", "13:00:00", "15:00:00"];

                        // Dynamically generate requiredTimes: include default times that are not in eventStartTimes
                        let requiredTimes = defaultRequiredTimes.filter(time => !eventStartTimes.includes(time));
                        console.log(`Dynamically generated requiredTimes for ${selectedDate}:`, requiredTimes);

                        // Check if all required times are booked
                        let allSlotsBooked = requiredTimes.length > 0 && requiredTimes.every(time => bookedTimes.includes(time));

                        if (allSlotsBooked) {
                            Swal.fire("Fully Booked", `The selected date (${selectedDate}) is already full. Please choose another date.`, "error");
                            $('#calendar').fullCalendar('unselect');
                            return;
                        }

                        let priestName = $('#priest_name').val();
                        $('#service-info').html(`<strong>Priest:</strong> ${priestName}<br><br><strong>Selected Date:</strong> ${selectedDate}`);
                        $('#blessing-form').show();

                        // Populate time dropdown with available slots
                        let availableTimes = requiredTimes.filter(time => !bookedTimes.includes(time));
                        $('#blessing-time').empty();
                        if (availableTimes.length === 0) {
                            Swal.fire("No Available Slots", `All time slots for ${selectedDate} are booked. Please choose another date.`, "error");
                            $('#calendar').fullCalendar('unselect');
                            return;
                        }

                        availableTimes.forEach(time => {
                            let time12Hour = moment(time, 'HH:mm:ss').format('h:mm A');
                            $('#blessing-time').append(`<option value="${time12Hour}">${time12Hour}</option>`);
                        });
                        console.log(`Available times for ${selectedDate}:`, availableTimes.map(time => moment(time, 'HH:mm:ss').format('h:mm A')));

                        // Log the selected time when the user changes the time dropdown
                        $('#blessing-time').on('change', function() {
                            let selectedTime = $(this).val();
                            console.log(`Selected time for ${selectedDate}: ${selectedTime}`);
                        });

                        Swal.fire("Date Selected", `You have chosen ${selectedDay}, ${selectedDate} for a blessing with ${priestName}.`, "success");
                    }
                });
            }

            // Update priest name in service-info when select is changed
            $('#blessing-form #priest_name').on('change', function() {
                let priestName = $(this).val();
                let serviceInfo = $('#service-info').html();
                // Try to update only the priest line, or add it if not present
                if (serviceInfo.includes('Priest:')) {
                    serviceInfo = serviceInfo.replace(/<strong>Priest:<\/strong>.*?(<br>|$)/, `<strong>Priest:</strong> ${priestName}<br>`);
                } else {
                    serviceInfo += `<br><strong>Priest:</strong> ${priestName}`;
                }
                $('#service-info').html(serviceInfo);
                if ($('#blessing-form').is(':visible')) {
                    renderBlessingCalendar();
                }
            });


            $("#blessing-form").submit(function(e) {
                e.preventDefault();
                let formData = new FormData(this);

                let selectedDate = $("#service-info").text().replace("Selected Date:", "").trim();
                if (!selectedDate) {
                    Swal.fire("Error", "Please select a blessing date first.", "error");
                    return;
                }

                formData.append("blessing_date", selectedDate);

                // Disable button and show spinner
                let saveButton = $("#save-blessing");
                saveButton.prop("disabled", true);
                saveButton.html('<i class="fa fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: "save_blessing.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        let res = JSON.parse(response);

                        Swal.fire({
                            title: res.status.toUpperCase(),
                            text: res.message,
                            icon: res.status
                        }).then(() => {
                            if (res.status === "success") {
                                $("#blessing-form")[0].reset();
                            }
                        });
                    },
                    error: function() {
                        Swal.fire("Error", "Something went wrong. Please try again.", "error");
                    },
                    complete: function() {
                        // Re-enable button and restore original text after SweetAlert is closed
                        saveButton.prop("disabled", false);
                        saveButton.html("Save Blessing");
                    }
                });
            });


            $('#pamisa-form').submit(function(e) {
                e.preventDefault();
                $('#loading-spinner').css('display', 'flex');

                let formData = new FormData();
                formData.append('name_of_intended', $('#name-of-intended').val().trim());
                formData.append('name_of_requestor', $('#name-of-requestor').val().trim());
                formData.append('pamisa_type', $('#pamisa-type').val());
                formData.append('selected_time', $('#pamisa-time').val());

                // Extract and validate selected_date
                let selectedDateMatch = $('#service-info').text().match(/\d{4}-\d{2}-\d{2}/);
                let selectedDate = selectedDateMatch ? selectedDateMatch[0] : '';
                formData.append('selected_date', selectedDate);

                // Add GCash receipt file
                formData.append('gcash_receipt', $('#gcash-receipt')[0].files[0]);

                // Client-side validation
                if (!formData.get('name_of_intended') || 
                    !formData.get('name_of_requestor') || 
                    !formData.get('pamisa_type') || 
                    !formData.get('selected_date') || 
                    !formData.get('selected_time')) {
                    $('#loading-spinner').hide();
                    Swal.fire("Error", "Please fill out all required fields, including a valid date.", "error");
                    return;
                }

                // Optional: Validate date format (redundant here since match ensures YYYY-MM-DD, but kept for robustness)
                const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
                if (selectedDate && !dateRegex.test(selectedDate)) {
                    $('#loading-spinner').hide();
                    Swal.fire("Error", "Please provide a valid date in YYYY-MM-DD format.", "error");
                    return;
                }

                if (!formData.get('gcash_receipt')) {
                    $('#loading-spinner').hide();
                    Swal.fire("Error", "Please upload a GCash receipt.", "error");
                    return;
                }

                // Log formData entries for debugging
                for (let [key, value] of formData.entries()) {
                    console.log(key, value);
                }

                $.ajax({
                    url: 'save_pamisa.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        console.log("response: "+ response);
                        
                        let res;
                        try {
                            res = JSON.parse(response);
                        } catch (e) {
                            $('#loading-spinner').hide();
                            Swal.fire("Error", "Invalid server response. Please try again.", "error");
                            return;
                        }
                        $('#loading-spinner').hide();

                        if (res.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: res.message
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: res.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#loading-spinner').hide();
                        Swal.fire("Oops...", "Something went wrong. Please try again!", "error");
                        console.error('AJAX error:', status, error);
                    }
                });
            });

            $('#baptismBtn').click(function() {
                $('#pamisa-form').hide();
                $('#baptism-form').show();
                $('#blessing-form').hide();
                $('#service-info').html('<strong>Service:</strong> Blessing<br><br><span style="color: red;">Please select a Sunday</span>');
                $('#proceed-payment').hide();
                $('#calendar').fullCalendar('destroy');
                renderCalendar();
            });

            $('#blessing').click(function() {
                $('#pamisa-form, #baptism-form').hide();
                $('#blessing-form').show();
                $('#service-info').html('<strong>Service:</strong> Blessing<br><br><span style="color: red;">Select Priest then Date</span>');
                $('#proceed-payment').hide();

                $('#calendar').fullCalendar('destroy');

                // Check if priest_name is selected
                var priestName = $('#blessing-form #priest_name').val();
                if (!priestName) {
                    $('#calendar').html('<div style="color: red; text-align: center; font-weight: bold; padding: 30px;">Please select priest</div>');
                    return;
                }
                renderBlessingCalendar();
            });

            // Re-render calendar when priest is selected
            $('#blessing-form #priest_name').on('change', function() {
                if ($('#blessing-form').is(':visible')) {
                    $('#calendar').fullCalendar('destroy');
                    var priestName = $(this).val();
                    if (!priestName) {
                        $('#calendar').html('<div style="color: red; text-align: center; font-weight: bold; padding: 30px;">Please select priest</div>');
                        return;
                    } else {
                        $('#calendar').html('<div style="color: green; text-align: center; font-weight: bold; padding: 30px;">Selected Priest: <strong>' + priestName + '</strong></div>');
                        renderBlessingCalendar();
                    }

                }
            });


            $('#addMore').click(function() {
                $('#ninongNinangFields').append(`
                    <div>
                        <input type="text" name="ninong_ninang[]" class="input-field" placeholder="First name, Last name"required style="width: 100%; padding: 12px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
                        <br><button type="button" class="remove">Remove</button>
                    </div>

                 `);
            });

            $(document).on("click", ".remove", function() {
                $(this).parent().remove();
            });


            // Baptism Form submission
            $('#baptism-form').submit(function(e) {
                e.preventDefault();
                $('#loading-spinner').css('display', 'flex');

                let ninongsNinangs = [];
                $('input[name="ninong_ninang[]"]').each(function() {
                    let value = $(this).val().trim();
                    if (value) {
                        ninongsNinangs.push(value);
                    }
                });

                let parentsNames = [];
                $('input[name="parents_name[]"]').each(function() {
                    let value = $(this).val().trim();
                    if (value) {
                        parentsNames.push(value);
                    }
                });

                let selectedDateMatch = $('#service-info').text().match(/\d{4}-\d{2}-\d{2}/);
                let selectedDate = selectedDateMatch ? selectedDateMatch[0] : null;

                // Basic client-side validation
                if (!$('#baptized-name').val().trim()) {
                    $('#loading-spinner').hide();
                    Swal.fire("Error", "Please enter the name of the baptized person.", "error");
                    return;
                }
                if (ninongsNinangs.length < 2) {
                    $('#loading-spinner').hide();
                    Swal.fire("Error", "Please enter at least two Ninong/Ninang names.", "error");
                    return;
                }
                if (!selectedDate) {
                    $('#loading-spinner').hide();
                    Swal.fire("Error", "Please select a valid date.", "error");
                    return;
                }

                let data = {
                    baptized_name: $('#baptized-name').val().trim(),
                    parents_name: parentsNames, // Can be empty array, will be handled as NULL in PHP
                    ninongs_ninangs: ninongsNinangs,
                    selected_date: selectedDate
                };

                $.ajax({
                    url: 'save_baptism.php',
                    type: 'POST',
                    data: JSON.stringify(data),
                    contentType: "application/json",
                    success: function(response) {
                        $('#loading-spinner').hide();
                        let res;
                        let additionalNinongs = Math.max(0, ninongsNinangs.length - 2);
                        let totalAmount = 500 + (additionalNinongs * 30);

                        try {
                            res = JSON.parse(response);
                            if (res.status === "success") {
                                $('#total-payment').text(`Total Amount: ₱${totalAmount}`);
                                $('#baptism-payment-amount').text(`Total to Pay: ₱${totalAmount}`);

                                Swal.fire({
                                    icon: "success",
                                    title: "Success",
                                    text: "Baptism request saved. Please proceed to payment.",
                                    allowOutsideClick: false
                                }).then(() => {
                                    $('#payment-modal-baptism').fadeIn();
                                });
                            } else {
                                Swal.fire("Error", res.message, "error");
                            }
                        } catch (e) {
                            $('#total-payment').text(`Total Amount: ₱${totalAmount}`);
                            $('#baptism-payment-amount').text(`Total to Pay: ₱${totalAmount}`);

                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: "Baptism request saved. Please proceed to payment.",
                                allowOutsideClick: false
                            }).then(() => {
                                $('#payment-modal-baptism').fadeIn();
                            });
                        }
                    },
                    error: function() {
                        $('#loading-spinner').hide();
                        Swal.fire("Oops...", "Something went wrong. Please try again!", "error");
                    }
                });
            });

            $('#proceed-payment-baptism').click(function() {
                $('#payment-modal-baptism').fadeIn();
            });

            $('#close-modal-baptism').click(function() {
                $('#payment-modal-baptism').fadeOut();
            });

            $('#proceed-payment').click(function() {
                $('#payment-modal').fadeIn();
            });

            $('#close-modal').click(function() {
                $('#payment-modal').fadeOut();
            });

            $('#submit-payment').click(function() {
                let fileInput = $('#gcash-receipt')[0].files[0];
                if (!fileInput) {
                    Swal.fire("Error", "Please upload a GCash receipt.", "error");
                    return;
                }

                let formData = new FormData();
                formData.append('gcash_receipt', fileInput);

                $('#loading-spinner').css('display', 'flex');

                $.ajax({
                    url: 'upload_payment.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#loading-spinner').hide();
                        Swal.fire("Payment Submitted", "Your payment has been received. Please check your email.", "success");
                    },
                    error: function() {
                        $('#loading-spinner').hide();
                        Swal.fire("Oops...", "Something went wrong. Please try again!", "error");
                    }
                });
            });

            $('#submit-payment-baptism').click(function() {
                let fileInput = $('#gcash-receipt-baptism')[0].files[0];
                if (!fileInput) {
                    Swal.fire("Error", "Please upload a GCash receipt for Baptism.", "error");
                    return;
                }

                // Get the payment amount from the DOM and log it
                let paymentAmountText = $('#baptism-payment-amount').text();
                console.log('Baptism Payment Amount:', paymentAmountText);
                // Total to Pay: ₱560

                let formData = new FormData();
                formData.append('gcash_receipt_baptism', fileInput);
                formData.append('payment_amount', paymentAmountText);

                $('#loading-spinner').css('display', 'flex');

                $.ajax({
                    url: 'upload_payment_baptism.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#loading-spinner').hide();

                        try {
                            let jsonResponse = JSON.parse(response);

                            if (jsonResponse.status === "success") {
                                Swal.fire("Payment Submitted", "Your Baptism payment has been received. Please check your email.", "success")
                                    .then(() => {
                                        $('#payment-modal-baptism').fadeOut();
                                    });
                            } else {
                                Swal.fire("Error", jsonResponse.message, "error");
                            }
                        } catch (e) {
                            console.log("Invalid JSON response:", response);
                            Swal.fire("Error", "Invalid response from the server. Please contact support.", "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#loading-spinner').hide();
                        console.error("AJAX Error:", error);
                        Swal.fire("Oops...", "Something went wrong. Please try again!", "error");
                    }
                });
            });


            function fetchAvailableSlots(selectedDate) {
                return $.getJSON('fetch_slots.php', {
                    date: selectedDate
                });
            }

            $('#calendar').fullCalendar({
                selectable: true,
                select: function(start) {
                    let selectedDate = start.format('YYYY-MM-DD');

                    fetchAvailableSlots(selectedDate).done(function(data) {
                        if (data.slots_remaining > 0) {
                            $('#service-info').html(`<strong>Selected Date:</strong> ${selectedDate} (Slots left: ${data.slots_remaining})`);
                        } else {
                            Swal.fire("Fully Booked", "No slots left for this date.", "warning");
                            $('#calendar').fullCalendar('unselect');
                        }
                    });
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#notification-link').on('click', function(e) {
                $.ajax({
                    url: 'clear_notifications.php',
                    method: 'POST',
                    success: function() {
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

        .input-field {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 250px;
            margin-right: 10px;
        }

        .remove {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease-in-out;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        }

        .remove:hover {
            background-color: #c82333;
        }

        #ninongNinangFields div {
            margin-bottom: 10px;
        }

        input {
            padding: 8px;
            width: 80%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 25px;
            cursor: pointer;
        }

        .submit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 6px;
        }

        .close-btn {
            background-color: #d32f2f;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 6px;
        }

        .submit-btn:hover,
        .close-btn:hover {
            opacity: 0.8;
        }

        .notice {
            color: red;
            font-weight: bold;
            font-size: 15px;
        }

        .payment-info {
            color: black;
            font-size: 15px;
        }

        @media (max-width: 600px) {
            .modal-content {
                width: 95%;
                max-width: 400px;
            }
        }
    </style>

    <script src="scriptd.js"></script>
    <footer>
        <div class="footer-container">
            <div class="footer-about">
                <h3>About Parish of the Holy Cross</h3>
                <p>
                    The Parish of the Holy Cross is a sacred place of worship, where the community comes together to
                    celebrate faith, hope, and love. Whether you're seeking spiritual growth, a peaceful moment of
                    reflection, or a place to connect with others, our church provides a welcoming environment for
                    all.
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