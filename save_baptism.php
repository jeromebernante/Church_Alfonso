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

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['baptized_name']) || empty($data['ninongs_ninangs']) || empty($data['selected_date'])) {
    echo json_encode(["status" => "error", "message" => "Invalid or incomplete data."]);
    exit();
}

$user_id = $_SESSION['user_id'];
$baptized_name = trim($data['baptized_name']);
// Check if parents_name is empty or null, and set to NULL if so
$parents_name = !empty($data['parents_name']) && is_array($data['parents_name']) ? json_encode($data['parents_name']) : null;
$ninongs_ninangs = json_encode($data['ninongs_ninangs']);
$selected_date = $data['selected_date'];

// Validate input lengths
if (strlen($baptized_name) > 255 || (isset($data['parents_name']) && count($data['parents_name']) > 2) || count($data['ninongs_ninangs']) < 2) {
    echo json_encode(["status" => "error", "message" => "Invalid input lengths."]);
    exit();
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
    echo json_encode(["status" => "error", "message" => "Invalid date format."]);
    exit();
}

// Validate that the date is not in the past
$current_date = date('Y-m-d');
if ($selected_date < $current_date) {
    echo json_encode(["status" => "error", "message" => "Selected date cannot be in the past."]);
    exit();
}

$sql = "SELECT priest_name FROM priest_schedule WHERE date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_date);
$stmt->execute();
$result = $stmt->get_result();
$priest = $result->fetch_assoc();

if ($priest && $priest['priest_name'] === 'All Priests Unavailable') {
    echo json_encode(["status" => "error", "message" => "No priests available on this date. Please select another date."]);
    exit();
}

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

$sql = "SELECT slots_remaining FROM baptism_slots WHERE date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(["status" => "error", "message" => "Selected date is not available."]);
    exit();
}

if ($row['slots_remaining'] <= 0) {
    echo json_encode(["status" => "error", "message" => "No slots left for this date."]);
    exit();
}

$conn->begin_transaction();

$status = "Pending";

$sql = "INSERT INTO baptism_requests (user_id, baptized_name, parents_name, ninongs_ninangs, selected_date, status) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssss", $user_id, $baptized_name, $parents_name, $ninongs_ninangs, $selected_date, $status);

if ($stmt->execute()) {
    $sql = "UPDATE baptism_slots SET slots_remaining = slots_remaining - 1 WHERE date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selected_date);
    $stmt->execute();

    $conn->commit();

    // Insert Admin Notification
    $admin_message = "A new baptism request was received and payment is pending.";
    $admin_notif_sql = "INSERT INTO admin_notifications (message, status) VALUES (?, 'unread')";
    $admin_notif_stmt = $conn->prepare($admin_notif_sql);

    if (!$admin_notif_stmt) {
        error_log("Admin notification SQL prepare error: " . $conn->error);
    } else {
        $admin_notif_stmt->bind_param("s", $admin_message);
        if (!$admin_notif_stmt->execute()) {
            error_log("Admin notification SQL execute error: " . $admin_notif_stmt->error);
        }
        $admin_notif_stmt->close();
    }

    // Insert User Notification
    $notif_sql = "INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'success')";
    $notif_stmt = $conn->prepare($notif_sql);
    $message = "Baptism request for $baptized_name on $selected_date was successfully submitted.";
    $notif_stmt->bind_param("is", $user_id, $message);
    $notif_stmt->execute();
    $notif_stmt->close();

    // Send Confirmation Email
    $mail = new PHPMailer(true);
    $email_status = "success";
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
        $mail->Subject = 'Baptism Request Confirmation';
        // Adjust email content to handle NULL parents_name
        $parents_name_display = $parents_name ? implode(", ", json_decode($parents_name, true)) : "Not provided";
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;'>
                <div style='text-align: center; padding-bottom: 20px;'>
                    <h2 style='color: #2c3e50;'>Baptism Request Confirmation</h2>
                    <p style='color: #16a085; font-size: 18px; font-weight: bold;'>Your request status: <span style='color: #e74c3c;'>$status</span></p>
                </div>
        
                <div style='background: #ffffff; padding: 15px; border-radius: 8px; box-shadow: 0px 2px 5px rgba(0,0,0,0.1);'>
                    <p style='font-size: 16px; color: #333;'>Dear <strong>$parents_name_display</strong>,</p>
                    <p style='font-size: 16px; color: #555;'>Thank you for submitting a Baptism request. Here are the details of your request:</p>
        
                    <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                        <tr>
                            <td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Baptized Name:</td>
                            <td style='padding: 10px; color: #34495e;'>$baptized_name</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Parents' Names:</td>
                            <td style='padding: 10px; color: #34495e;'>$parents_name_display</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Ninongs & Ninangs:</td>
                            <td style='padding: 10px; color: #34495e;'>" . implode(", ", json_decode($ninongs_ninangs, true)) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; font-weight: bold; color: #2c3e50;'>Date:</td>
                            <td style='padding: 10px; color: #34495e;'>$selected_date</td>
                        </tr>
                    </table>
        
                    <p style='text-align: center; font-size: 14px; color: #777; margin-top: 20px;'>If you have any questions, feel free to contact us.</p>
                </div>
            </div>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        $email_status = "failed";
    }

    echo json_encode([
        "status" => "success",
        "message" => "Baptism request saved successfully!" . ($email_status === "failed" ? " However, the confirmation email could not be sent." : " Confirmation email sent.")
    ]);
} else {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Failed to save request."]);
}

$stmt->close();
$conn->close();
?>