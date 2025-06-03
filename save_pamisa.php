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
$name_of_intended = $_POST['name_of_intended'] ?? '';
$name_of_requestor = $_POST['name_of_requestor'] ?? '';
$pamisa_type = $_POST['pamisa_type'] ?? '';
$selected_date = $_POST['selected_date'] ?? '';
$selected_time = $_POST['selected_time'] ?? '';
$price = 100;

// Validate inputs
if (empty($name_of_intended) || empty($name_of_requestor) || empty($pamisa_type) || empty($selected_date) || empty($selected_time)) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit();
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date) || !strtotime($selected_date)) {
    echo json_encode(["status" => "error", "message" => "Invalid date format."]);
    exit();
}

// Handle file upload
$target_dir = "uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$receipt_file = $_FILES["gcash_receipt"];
$receipt_path = $target_dir . basename($receipt_file["name"]);
$file_type = strtolower(pathinfo($receipt_path, PATHINFO_EXTENSION));

$allowed_types = ["jpg", "jpeg", "png", "pdf"];
if (!in_array($file_type, $allowed_types)) {
    echo json_encode(["status" => "error", "message" => "Invalid file format. Only JPG, PNG, and PDF allowed."]);
    exit();
}

if ($receipt_file["size"] > 5 * 1024 * 1024) { // 5MB limit
    echo json_encode(["status" => "error", "message" => "File size exceeds 5MB limit."]);
    exit();
}

if (move_uploaded_file($receipt_file["tmp_name"], $receipt_path)) {
    // Time validation
    date_default_timezone_set('Asia/Manila');
    $current_date = date('Y-m-d');
    $current_time = date('H:i');
    $selected_time_24 = date('H:i', strtotime($selected_time));

    if ($selected_date === $current_date && $selected_time_24 <= $current_time) {
        unlink($receipt_path); // Remove uploaded file if validation fails
        echo json_encode(["status" => "error", "message" => "You cannot book a Pamisa for a time that has already started today."]);
        exit();
    }

    // Check priest availability
    $sql_priest_check = "SELECT priest_name FROM priest_schedule WHERE date = ?";
    $stmt_priest = $conn->prepare($sql_priest_check);
    $stmt_priest->bind_param("s", $selected_date);
    $stmt_priest->execute();
    $stmt_priest->store_result();
    $stmt_priest->bind_result($scheduled_priest);
    $stmt_priest->fetch();
    $stmt_priest->close();

    if (isset($scheduled_priest) && strtolower(trim($scheduled_priest)) === 'all priests unavailable') {
        unlink($receipt_path); // Remove uploaded file if validation fails
        echo json_encode(["status" => "error", "message" => "No priests are available on the selected date."]);
        exit();
    }

    // Get user email
    $sql_user = "SELECT email FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $stmt_user->bind_result($user_email);
    $stmt_user->fetch();
    $stmt_user->close();

    // Insert Pamisa request
    $sql = "INSERT INTO pamisa_requests (user_id, name_of_intended, name_of_requestor, pamisa_type, selected_date, selected_time, price, status, payment_receipt) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        unlink($receipt_path); // Remove uploaded file if database error
        error_log("Database error: " . $conn->error);
        $notification_message = "Database error while saving pamisa request.";
        $notification_status = "error";
        goto save_notification;
    }

    $stmt->bind_param("isssssis", $user_id, $name_of_intended, $name_of_requestor, $pamisa_type, $selected_date, $selected_time, $price, $receipt_path);
    if ($stmt->execute()) {
        $notification_message = "Pamisa request was successfully sent. Please wait for payment confirmation.";
        $notification_status = "success";

        // Admin notification
        $admin_notification_message = "A new Pamisa request was received and is pending payment verification.";
        $sql_admin_notification = "INSERT INTO admin_notifications (message, status) VALUES (?, 'unread')";
        $stmt_admin_notification = $conn->prepare($sql_admin_notification);
        $stmt_admin_notification->bind_param("s", $admin_notification_message);
        $stmt_admin_notification->execute();
        $stmt_admin_notification->close();

        // Send Email (unchanged PHPMailer code)
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
            $mail->Subject = "Pamisa Request Received";
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;'>
                <div style='text-align: center; padding-bottom: 20px;'>
                    <h2 style='color: #2c3e50;'>Pamisa Request Confirmation</h2>
                    <p style='color: #16a085; font-size: 18px; font-weight: bold;'>Your request is currently <span style='color: #e74c3c;'>PENDING</span></p>
                </div>
                <div style='background: #ffffff; padding: 15px; border-radius: 8px; box-shadow: 0px 2px 5px rgba(0,0,0,0.1);'>
                    <p style='font-size: 16px; color: #333;'>Dear <strong>$name_of_requestor</strong>,</p>
                    <p style='font-size: 16px; color: #555;'>Thank you for submitting a Pamisa request. Here are the details of your request:</p>
                    <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                        <tr><td style='padding: 10px; font-weight: bold;'>Intended for:</td><td style='padding: 10px;'>$name_of_intended</td></tr>
                        <tr><td style='padding: 10px; font-weight: bold;'>Pamisa Type:</td><td style='padding: 10px;'>$pamisa_type</td></tr>
                        <tr><td style='padding: 10px; font-weight: bold;'>Date:</td><td style='padding: 10px;'>$selected_date</td></tr>
                        <tr><td style='padding: 10px; font-weight: bold;'>Time:</td><td style='padding: 10px;'>$selected_time</td></tr>
                        <tr><td style='padding: 10px; font-weight: bold;'>Amount Due:</td><td style='padding: 10px; font-size: 18px; color: #e74c3c;'><strong>PHP $price per name</strong></td></tr>
                    </table>
                    <p style='font-size: 16px; color: #555;'>Your payment receipt has been received and is pending verification.</p>
                    <p style='text-align: center; font-size: 14px; color: #777; margin-top: 20px;'>If you have any questions, feel free to contact us.</p>
                    <p style='text-align: center; font-size: 14px; color: #555;'><strong>Parish of the Holy Cross</strong></p>
                </div>
            </div>";
            $mail->send();
        } catch (Exception $e) {
            $notification_message = "Pamisa request saved, but email could not be sent.";
            $notification_status = "success";
            error_log("PHPMailer error: " . $e->getMessage());
        }
    } else {
        unlink($receipt_path); // Remove uploaded file if database insert fails
        $notification_message = "Error saving pamisa request.";
        $notification_status = "error";
        error_log("Database error: " . $stmt->error);
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
?>