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
$name_of_blessed = $_POST['name_of_blessed'] ?? '';
$name_of_requestor = $_POST['name_of_requestor'] ?? '';
$blessing_time = $_POST['blessing_time'] ?? '';
$priest_name = $_POST['priest_name'] ?? '';
$type_of_blessing = $_POST['type_of_blessing'] ?? '';
$blessing_date = $_POST['blessing_date'] ?? '';
$status = "Accepted";

// Validate inputs
if (empty($name_of_blessed) || empty($name_of_requestor) || empty($blessing_time) || empty($priest_name) || empty($type_of_blessing) || empty($blessing_date)) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit();
}

// Extract and validate date
$extracted_date = substr($blessing_date, 0, 10);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $extracted_date) || !checkdate((int)substr($extracted_date, 5, 2), (int)substr($extracted_date, 8, 2), (int)substr($extracted_date, 0, 4))) {
    echo json_encode(["status" => "error", "message" => "Invalid date format."]);
    exit();
}

// Validate time and convert to 24-hour format
$blessing_time_24 = date('H:i:s', strtotime($blessing_time));
if (!$blessing_time_24) {
    echo json_encode(["status" => "error", "message" => "Invalid time format2."]);
    exit();
}

// Check if date/time is in the past
date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');
$current_time = date('H:i:s');
if ($extracted_date < $today || ($extracted_date === $today && $blessing_time_24 <= $current_time)) {
    echo json_encode(["status" => "error", "message" => "Cannot book a blessing for a past date or time."]);
    exit();
}

// Check priest availability
$sql_check_priest = "SELECT priest_name FROM priest_schedule WHERE date = ?";
$stmt_check_priest = $conn->prepare($sql_check_priest);
$stmt_check_priest->bind_param("s", $extracted_date);
$stmt_check_priest->execute();
$stmt_check_priest->bind_result($scheduled_priest);
$priest_unavailable = false;
$date_exists = false;

while ($stmt_check_priest->fetch()) {
    $date_exists = true;
    if ($scheduled_priest === 'All Priests Unavailable') {
        $priest_unavailable = true;
        break;
    }
}
$stmt_check_priest->close();

if ($priest_unavailable) {
    echo json_encode(["status" => "error", "message" => "No priests are available on this date. Please choose another date."]);
    exit();
}

// Check for existing booking
$sql_check = "SELECT COUNT(*) FROM blessings_requests WHERE blessing_date = ? AND blessing_time = ? AND priest_name = ? AND status != 'Cancelled'";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("sss", $extracted_date, $blessing_time_24, $priest_name);
$stmt_check->execute();
$stmt_check->bind_result($count);
$stmt_check->fetch();
$stmt_check->close();

if ($count > 0) {
    echo json_encode(["status" => "error", "message" => "This time slot is already booked for the selected priest and date."]);
    exit();
}

// Handle file upload
$target_dir = "uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

$receipt_file = $_FILES["donation_receipt"];
$receipt_path = $target_dir . uniqid() . '_' . basename($receipt_file["name"]);
$file_type = strtolower(pathinfo($receipt_path, PATHINFO_EXTENSION));

$allowed_types = ["jpg", "jpeg", "png", "pdf"];
if (!in_array($file_type, $allowed_types)) {
    echo json_encode(["status" => "error", "message" => "Invalid file format. Only JPG, PNG, and PDF allowed."]);
    exit();
}

if ($receipt_file["size"] > 5 * 1024 * 1024) {
    echo json_encode(["status" => "error", "message" => "File size exceeds 5MB limit."]);
    exit();
}

if (move_uploaded_file($receipt_file["tmp_name"], $receipt_path)) {
    // Get user email
    $sql_user = "SELECT email FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $stmt_user->bind_result($user_email);
    $stmt_user->fetch();
    $stmt_user->close();

    // Insert blessing request
    $sql = "INSERT INTO blessings_requests (user_id, name_of_blessed, priest_name, name_of_requestor, blessing_time, type_of_blessing, blessing_date, receipt_path, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        unlink($receipt_path);
        error_log("Database error: " . $conn->error);
        $notification_message = "Database error while saving blessing request.";
        $notification_status = "error";
        goto save_notification;
    }

    $stmt->bind_param("issssssss", $user_id, $name_of_blessed, $priest_name, $name_of_requestor, $blessing_time_24, $type_of_blessing, $extracted_date, $receipt_path, $status);

    if ($stmt->execute()) {
        $notification_message = "Success! Your request is now approved. Status: Accepted";
        $notification_status = "success";

        // Admin notification
        $admin_message = "A new blessing request was received and approved automatically by the system.";
        $sql_admin_notification = "INSERT INTO admin_notifications (message, status) VALUES (?, 'unread')";
        $stmt_admin_notification = $conn->prepare($sql_admin_notification);
        $stmt_admin_notification->bind_param("s", $admin_message);
        $stmt_admin_notification->execute();
        $stmt_admin_notification->close();
    } else {
        unlink($receipt_path);
        error_log("Database error: " . $stmt->error);
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

$stmt->close();
$conn->close();

echo json_encode(["status" => $notification_status, "message" => $notification_message]);

if ($status === "Accepted" && $notification_status === "success") {
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
                    <tr><td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Blessing for:</td><td style='padding: 10px; color: #34495e;'>$name_of_blessed</td></tr>
                    <tr><td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Priest:</td><td style='padding: 10px; color: #34495e;'>$priest_name</td></tr>
                    <tr><td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Type of Blessing:</td><td style='padding: 10px; color: #34495e;'>$type_of_blessing</td></tr>
                    <tr><td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Date:</td><td style='padding: 10px; color: #34495e;'>$extracted_date</td></tr>
                    <tr><td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Time:</td><td style='padding: 10px; color: #34495e;'>$blessing_time</td></tr>
                </table>
            </div>
        </div>";
        $mail->send();
    } catch (Exception $e) {
        error_log("Email confirmation failed: " . $mail->ErrorInfo);
    }
}
?>