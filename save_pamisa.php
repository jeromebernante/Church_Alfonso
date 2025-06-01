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
$name_of_intended = $_POST['name_of_intended'];
$name_of_requestor = $_POST['name_of_requestor'];
$pamisa_type = $_POST['pamisa_type'];
$selected_date = $_POST['selected_date'];
$selected_time = $_POST['selected_time'];
$price = 100;

date_default_timezone_set('Asia/Manila');

$current_date = date('Y-m-d');
$current_time = date('H:i');

if ($selected_date === $current_date && $selected_time <= $current_time) {
    echo json_encode([
        "status" => "error",
        "message" => "You cannot book a Pamisa for a time that has already started today."
    ]);
    exit();
}

// Check for priest schedule only for "all priests unavailable"
$sql_priest_check = "SELECT priest_name FROM priest_schedule WHERE date = ?";
$stmt_priest = $conn->prepare($sql_priest_check);
$stmt_priest->bind_param("s", $selected_date);
$stmt_priest->execute();
$stmt_priest->store_result();
$stmt_priest->bind_result($scheduled_priest);
$stmt_priest->fetch();
$stmt_priest->close();

if (isset($scheduled_priest) && strtolower(trim($scheduled_priest)) === 'all priests unavailable') {
    echo json_encode(["status" => "error", "message" => "No priests are available on the selected date."]);
    exit();
}

// Check for duplicate time slot
$sql_check = "SELECT COUNT(*) FROM pamisa_requests WHERE selected_date = ? AND selected_time = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ss", $selected_date, $selected_time);
$stmt_check->execute();
$stmt_check->bind_result($count);
$stmt_check->fetch();
$stmt_check->close();

if ($count > 0) {
    echo json_encode(["status" => "error", "message" => "The selected date and time are already taken."]);
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
$sql = "INSERT INTO pamisa_requests (user_id, name_of_intended, name_of_requestor, pamisa_type, selected_date, selected_time, price, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    $notification_message = "Database error while saving pamisa request.";
    $notification_status = "error";
    goto save_notification;
}

$stmt->bind_param("isssssi", $user_id, $name_of_intended, $name_of_requestor, $pamisa_type, $selected_date, $selected_time, $price);
if ($stmt->execute()) {
    $notification_message = "Pamisa request was successfully sent. Please click 'Proceed to Payment' to pay!";
    $notification_status = "success";

    // Admin notification
    $admin_notification_message = "A new Pamisa request was received and is pending payment.";
    $sql_admin_notification = "INSERT INTO admin_notifications (message, status) VALUES (?, 'unread')";
    $stmt_admin_notification = $conn->prepare($sql_admin_notification);
    $stmt_admin_notification->bind_param("s", $admin_notification_message);
    $stmt_admin_notification->execute();
    $stmt_admin_notification->close();

    // Send Email
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
                <p style='text-align: center; font-size: 14px; color: #777; margin-top: 20px;'>If you have any questions or problems with payment, feel free to contact us.</p>
                <p style='text-align: center; font-size: 14px; color: #555;'><strong>Parish of the Holy Cross</strong></p>
            </div>
        </div>";
        $mail->send();
    } catch (Exception $e) {
        $notification_message = "Pamisa request saved, but email could not be sent.";
        $notification_status = "success";
    }
} else {
    $notification_message = "Error saving pamisa request.";
    $notification_status = "error";
}

save_notification:
$sql_notification = "INSERT INTO notifications (user_id, message, status) VALUES (?, ?, ?)";
$stmt_notification = $conn->prepare($sql_notification);
$stmt_notification->bind_param("iss", $user_id, $notification_message, $notification_status);
$stmt_notification->execute();
$stmt_notification->close();

// Redundant admin notification (safe double-insert)
$admin_notification_message = "A new Pamisa request was received and is pending payment.";
$sql_admin_notification = "INSERT INTO admin_notifications (message, status) VALUES (?, 'unread')";
$stmt_admin_notification = $conn->prepare($sql_admin_notification);
$stmt_admin_notification->bind_param("s", $admin_notification_message);
$stmt_admin_notification->execute();
$stmt_admin_notification->close();

$stmt->close();
$conn->close();

echo json_encode(["status" => $notification_status, "message" => $notification_message]);
?>
