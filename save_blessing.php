<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/Exception.php';
require 'PHPMailer/SMTP.php';
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

$user_id = $_SESSION['user_id'];
$name_of_blessed = $_POST['name_of_blessed'];
$name_of_requestor = $_POST['name_of_requestor'];
$blessing_time = $_POST['blessing_time'];
$priest_name = $_POST['priest_name'];
$type_of_blessing = $_POST['type_of_blessing'];
$blessing_date = $_POST['blessing_date'];
$status = "Accepted";


// Step 1: Error if user didn't select a valid date
if (strpos($blessing_date, 'Please select a date') !== false) {
    echo json_encode(["status" => "error", "message" => "Please select a valid date."]);
    exit();
}

// Step 2: Extract date from the start of the string
$extracted_date = substr($blessing_date, 0, 10);

// Step 3: Validate date format and logic
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $extracted_date) || 
    !checkdate((int)substr($extracted_date, 5, 2), (int)substr($extracted_date, 8, 2), (int)substr($extracted_date, 0, 4))) {
    echo json_encode(["status" => "error", "message" => "Invalid date format."]);
    exit();
}

// Step 4: Check if date is in the past
$today = date('Y-m-d');
if ($extracted_date < $today) {
    echo json_encode(["status" => "error", "message" => "Date is in the past. Please choose a future date."]);
    exit();
}



$sql_check_priest = "SELECT priest_name FROM priest_schedule WHERE date = ?";
$stmt_check_priest = $conn->prepare($sql_check_priest);
$stmt_check_priest->bind_param("s", $blessing_date);
$stmt_check_priest->execute();
$stmt_check_priest->bind_result($priest_name);
$priest_unavailable = false;
$date_exists = false;

while ($stmt_check_priest->fetch()) {
    $date_exists = true;
    if ($priest_name === 'All Priests Unavailable') {
        $priest_unavailable = true;
        break;
    }
}
$stmt_check_priest->close();

if ($priest_unavailable) {
    echo json_encode(["status" => "error", "message" => "No priests are available on this date. Please choose another date."]);
    exit();
}

if (!$date_exists) {
    $status = "Accepted";
}

$target_dir = "uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$receipt_file = $_FILES["donation_receipt"];
$receipt_path = $target_dir . basename($receipt_file["name"]);
$file_type = strtolower(pathinfo($receipt_path, PATHINFO_EXTENSION));

$allowed_types = ["jpg", "jpeg", "png", "pdf"];
if (!in_array($file_type, $allowed_types)) {
    echo json_encode(["status" => "error", "message" => "Invalid file format. Only JPG, PNG, and PDF allowed."]);
    exit();
}

if (move_uploaded_file($receipt_file["tmp_name"], $receipt_path)) {
    $sql_user = "SELECT email FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $stmt_user->bind_result($user_email);
    $stmt_user->fetch();
    $stmt_user->close();

    $sql_check = "SELECT COUNT(*) FROM blessings_requests WHERE blessing_date = ? AND blessing_time = ? AND priest_name = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("sss", $blessing_date, $blessing_time, $priest_name);

    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();


    // Combine date and time to check if it's in the past
    $combined_datetime = strtotime($blessing_date . ' ' . $blessing_time);
    $current_datetime = time();

    // If datetime is in the past, reject the request
    if ($combined_datetime < $current_datetime) {
        echo json_encode(["status" => "error", "message" => "The selected blessing date and time are in the past. Please choose a future time."]);
        exit();
    }


    if ($count > 0) {
        echo json_encode(["status" => "error", "message" => "This date and time are already booked. Please choose another slot."]);
        exit();
    }

    $sql = "INSERT INTO blessings_requests (user_id, name_of_blessed, priest_name, name_of_requestor, blessing_time, type_of_blessing, blessing_date, receipt_path, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $notification_message = "Database error while saving blessing request.";
        $notification_status = "error";
        goto save_notification;
    }

    $stmt->bind_param("issssssss", $user_id, $name_of_blessed, $priest_name, $name_of_requestor, $blessing_time, $type_of_blessing, $blessing_date, $receipt_path, $status);

    if ($stmt->execute()) {
        $notification_message = "Success! Your request is now approved. Status: Accepted";
        $notification_status = "success";
    } else {
        $notification_message = "Error saving blessing request.";
        $notification_status = "error";
    }
} else {
    $notification_message = "Failed to upload receipt.";
    $notification_status = "error";
}

save_notification:
$sql_notification = "INSERT INTO notifications (user_id, message, status) VALUES (?, ?, ?)";
$stmt_notification = $conn->prepare($sql_notification);
$stmt_notification->bind_param("iss", $user_id, $notification_message, $notification_status);
$stmt_notification->execute();
$stmt_notification->close();

// Save notification for admin
$admin_message = "A new blessing request was received and approved automatically by the system.";
$sql_admin_notification = "INSERT INTO admin_notifications (message, status) VALUES (?, 'unread')";
$stmt_admin_notification = $conn->prepare($sql_admin_notification);
$stmt_admin_notification->bind_param("s", $admin_message);
$stmt_admin_notification->execute();
$stmt_admin_notification->close();

$stmt->close();
$conn->close();

echo json_encode(["status" => $notification_status, "message" => $notification_message]);

if ($status === "Accepted") {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'parishoftheholycrossonline@gmail.com';
        $mail->Password = 'xbfh zzfy ibtw klre';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('parishoftheholycrossonline@gmail.com', 'Parish of the Holy Cross');
        $mail->addAddress($user_email);
        $mail->isHTML(true);
        $mail->Subject = "Blessing Request Accepted";
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;'>
            <div style='text-align: center; padding-bottom: 20px;'>
                <h2 style='color: #2c3e50;'>Blessing Request Approved</h2>
                <p style='color: #16a085; font-size: 18px; font-weight: bold;'>Your request has been <span style='color: #27ae60;'>APPROVED</span></p>
            </div>

            <div style='background: #ffffff; padding: 15px; border-radius: 8px; box-shadow: 0px 2px 5px rgba(0,0,0,0.1);'>
                <p style='font-size: 16px; color: #333;'>Dear <strong>$name_of_requestor</strong>,</p>
                <p style='font-size: 16px; color: #555;'>We are pleased to inform you that your blessing request has been <strong>ACCEPTED</strong>. Below are the details:</p>

                <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                    <tr>
                        <td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Blessing for:</td>
                        <td style='padding: 10px; color: #34495e;'>$name_of_blessed</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Blessing for:</td>
                        <td style='padding: 10px; color: #34495e;'>$priest_name</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Type of Blessing:</td>
                        <td style='padding: 10px; color: #34495e;'>$type_of_blessing</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Date:</td>
                        <td style='padding: 10px; color: #34495e;'>$blessing_date</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Time:</td>
                        <td style='padding: 10px; color: #34495e;'>$blessing_time</td>
                    </tr>
                </table>
        </div>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email confirmation failed: " . $mail->ErrorInfo);
    }
}
