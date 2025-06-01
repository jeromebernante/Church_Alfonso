<?php
session_start();
include 'db_connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit();
}

$user_id = $_SESSION['user_id'];
$bride_name = $_POST['brideName'] ?? '';
$groom_name = $_POST['groomName'] ?? '';
$priest_name = $_POST['priest_name'] ?? '';
$contact = $_POST['contact'] ?? '';
$wedding_date = $_POST['weddingDate'] ?? '';
$receipt = $_FILES['gcashReceipt'] ?? null;

if (!$bride_name || !$groom_name || !$contact || !$wedding_date || !$receipt) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit();
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $wedding_date)) {
    echo json_encode(["status" => "error", "message" => "Invalid date format."]);
    exit();
}

// Check priest availability
$sql_check_priest = "SELECT priest_name FROM priest_schedule WHERE date = ?";
$stmt_check_priest = $conn->prepare($sql_check_priest);
$stmt_check_priest->bind_param("s", $wedding_date);
$stmt_check_priest->execute();
$stmt_check_priest->store_result();

$priest_unavailable = false;
$date_exists = false;

if ($stmt_check_priest->num_rows > 0) {
    $stmt_check_priest->bind_result($priest_name);
    while ($stmt_check_priest->fetch()) {
        $date_exists = true;
        if (strtolower(trim($priest_name)) === 'all priests unavailable') {
            $priest_unavailable = true;
            break;
        }
    }
}
$stmt_check_priest->close();

if ($priest_unavailable) {
    echo json_encode(["status" => "error", "message" => "No priests are available on this date. Please choose another date."]);
    exit();
}

$status = $date_exists ? "Pending" : "Accepted";

// Upload receipt
$target_dir = "uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$receipt_name = time() . "_" . basename($receipt["name"]);
$target_file = $target_dir . $receipt_name;
$file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$allowed_types = ["jpg", "jpeg", "png", "pdf"];

if (!in_array($file_type, $allowed_types)) {
    echo json_encode(["status" => "error", "message" => "Invalid file type. Only JPG, PNG, or PDF allowed."]);
    exit();
}

if (!move_uploaded_file($receipt["tmp_name"], $target_file)) {
    echo json_encode(["status" => "error", "message" => "Failed to upload receipt."]);
    exit();
}

// Get user email
$sql = "SELECT email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(["status" => "error", "message" => "User not found."]);
    exit();
}
$user_email = $user['email'];

// Insert wedding request
$conn->begin_transaction();
$sql = "INSERT INTO wedding_requests (user_id, bride_name, groom_name, priest_name, contact, wedding_date, payment_receipt, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssssss", $user_id, $bride_name, $groom_name, $priest_name, $contact, $wedding_date, $receipt_name, $status);

if ($stmt->execute()) {
    // Save user notification
    $notification_message = "Wedding Request Submitted for $bride_name & $groom_name on $wedding_date. Status: $status";
    $notif_sql = "INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')";
    $notif_stmt = $conn->prepare($notif_sql);
    $notif_stmt->bind_param("is", $user_id, $notification_message);
    $notif_stmt->execute();

    // Save admin notification
    $admin_message = "A new wedding request was received and approved automatically by the system.";
    $admin_notif_sql = "INSERT INTO admin_notifications (message, status) VALUES (?, 'unread')";
    $admin_notif_stmt = $conn->prepare($admin_notif_sql);
    $admin_notif_stmt->bind_param("s", $admin_message);
    $admin_notif_stmt->execute();

    $conn->commit();

    // Send confirmation email
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
        $mail->Subject = 'Wedding Request Confirmation';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;'>
                <div style='text-align: center; padding-bottom: 20px;'>
                    <h2 style='color: #2c3e50;'>Wedding Request Confirmation</h2>
                    <p style='color: #16a085; font-size: 18px; font-weight: bold;'>Your request is now <span style='color: #e74c3c;'>$status</span></p>
                </div>
                <div style='background: #ffffff; padding: 15px; border-radius: 8px; box-shadow: 0px 2px 5px rgba(0,0,0,0.1);'>
                    <p style='font-size: 16px; color: #333;'>Dear <strong>$bride_name & $groom_name</strong>,</p>
                    <p style='font-size: 16px; color: #555;'>Thank you for submitting a Wedding request. Here are the details of your request:</p>
                    <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                        <tr><td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Bride's Name:</td><td style='padding: 10px; color: #34495e;'>$bride_name</td></tr>
                        <tr><td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Groom's Name:</td><td style='padding: 10px; color: #34495e;'>$groom_name</td></tr>
                        <tr><td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Priest's Name:</td><td style='padding: 10px; color: #34495e;'>$priest_name</td></tr>
                        <tr><td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Contact Number:</td><td style='padding: 10px; color: #34495e;'>$contact</td></tr>
                        <tr><td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Wedding Date:</td><td style='padding: 10px; color: #34495e;'>$wedding_date</td></tr>
                        <tr><td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Status:</td><td style='padding: 10px; color: #34495e;'>$status</td></tr>
                    </table>
                </div>
            </div>
        ";
        $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
    }

    echo json_encode(["status" => "success", "message" => "Wedding request saved successfully!"]);
} else {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Failed to save request."]);
}

$stmt->close();
$conn->close();
?>