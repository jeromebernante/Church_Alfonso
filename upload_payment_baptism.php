<?php
ob_start();
session_start();
include 'db_connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (!isset($_SESSION['user_id'])) {
    saveNotification($conn, null, "User not logged in.", "error");
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT br.id, u.email, u.name 
          FROM baptism_requests br
          JOIN users u ON br.user_id = u.id
          WHERE br.user_id = ? 
          ORDER BY br.id DESC LIMIT 1";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    saveNotification($conn, $user_id, "SQL Prepare Error: " . $conn->error, "error");
    echo json_encode(["status" => "error", "message" => "SQL Prepare Error: " . $conn->error]);
    exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    saveNotification($conn, $user_id, "No pending baptism request found for the user.", "error");
    echo json_encode(["status" => "error", "message" => "No pending baptism request found for the user."]);
    exit();
}

$baptism_request_id = $row['id'];
$email = $row['email'];
$name_of_requestor = $row['name'];
$stmt->close();

if (!isset($_FILES['gcash_receipt_baptism']) || $_FILES['gcash_receipt_baptism']['error'] !== UPLOAD_ERR_OK) {
    saveNotification($conn, $user_id, "File upload error.", "error");
    echo json_encode(["status" => "error", "message" => "File upload error."]);
    exit();
}

$fileTmpPath = $_FILES['gcash_receipt_baptism']['tmp_name'];
$fileName = time() . "_" . $_FILES['gcash_receipt_baptism']['name'];
$fileType = $_FILES['gcash_receipt_baptism']['type'];
$allowedTypes = ['image/jpeg', 'image/png'];

if (!in_array($fileType, $allowedTypes)) {
    saveNotification($conn, $user_id, "Invalid file type. Only JPG and PNG are allowed.", "error");
    echo json_encode(["status" => "error", "message" => "Invalid file type. Only JPG and PNG are allowed."]);
    exit();
}

$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$destPath = $uploadDir . $fileName;

if (!move_uploaded_file($fileTmpPath, $destPath)) {
    saveNotification($conn, $user_id, "Failed to save the file.", "error");
    echo json_encode(["status" => "error", "message" => "Failed to save the file."]);
    exit();
}

$sql = "INSERT INTO baptism_payments (baptism_request_id, user_id, receipt_path, date_uploaded) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    saveNotification($conn, $user_id, "SQL Prepare Error: " . $conn->error, "error");
    echo json_encode(["status" => "error", "message" => "SQL Prepare Error: " . $conn->error]);
    exit();
}

$stmt->bind_param("iis", $baptism_request_id, $user_id, $destPath);

if ($stmt->execute()) {
    saveNotification($conn, $user_id, "Baptism payment uploaded successfully.", "success");

    // Save admin notification
    saveAdminNotification($conn, "A new baptism payment has been uploaded by $name_of_requestor (Request ID: $baptism_request_id).");

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'parishoftheholycrossonline@gmail.com';
        $mail->Password = 'xbfh zzfy ibtw klre';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('parishoftheholycrossonline@gmail.com', 'Parish of the Holy Cross');
        $mail->addAddress($email, $name_of_requestor);
        $mail->isHTML(true);
        $mail->Subject = "Baptism Request Accepted - Confirmation Email";

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;'>
                <div style='text-align: center; padding-bottom: 20px;'>
                    <h2 style='color: #2c3e50;'>Baptism Request Approved ✅</h2>
                    <p style='color: #16a085; font-size: 18px; font-weight: bold;'>Your request has been <span style='color: #27ae60;'>APPROVED</span>!</p>
                </div>

                <div style='background: #ffffff; padding: 15px; border-radius: 8px; box-shadow: 0px 2px 5px rgba(0,0,0,0.1);'>
                    <p style='font-size: 16px; color: #333;'>Dear <strong>$name_of_requestor</strong>,</p>
                    <p style='font-size: 16px; color: #555;'>We are pleased to inform you that your baptism request has been <strong>APPROVED</strong>. Below are the details:</p>

                    <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                        <tr><td style='padding: 10px; font-weight: bold;'>Request ID:</td><td>$baptism_request_id</td></tr>
                        <tr><td style='padding: 10px; font-weight: bold;'>Date Approved:</td><td>" . date("Y-m-d H:i:s") . "</td></tr>
                   </table>

                    <p style='font-size: 16px; color: #555; margin-top: 15px;'>
                        Please arrive at the church on time and bring any required documents if needed. If you have any questions, feel free to contact us.
                    </p>

                    <p style='text-align: center; margin-top: 20px; font-size: 16px; color: #333; font-weight: bold;'>
                        Thank you, and God bless! 🙏
                    </p>
                </div>
            </div>
        ";

        if ($mail->send()) {
            echo json_encode(["status" => "success", "message" => "Baptism payment uploaded successfully. Confirmation email sent!"]);
        } else {
            saveNotification($conn, $user_id, "Email sending failed.", "error");
            echo json_encode(["status" => "success", "message" => "Your baptism request is Approved."]);
        }
    } catch (Exception $e) {
        saveNotification($conn, $user_id, "Mailer Error: " . $mail->ErrorInfo, "error");
        echo json_encode(["status" => "success", "message" => "Your baptism request is now Approved."]);
    }
}

$stmt->close();
$conn->close();
ob_end_flush();

function saveNotification($conn, $user_id, $message, $status) {
    $sql = "INSERT INTO notifications (user_id, message, status, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $message, $status);
    $stmt->execute();
    $stmt->close();
}

function saveAdminNotification($conn, $message) {
    $sql = "INSERT INTO admin_notifications (message, status, created_at) VALUES (?, 'unread', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $message);
    $stmt->execute();
    $stmt->close();
}
?>
