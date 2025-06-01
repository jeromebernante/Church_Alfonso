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
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_FILES['gcash_receipt']) || $_FILES['gcash_receipt']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "File upload error."]);
    exit();
}

$fileTmpPath = $_FILES['gcash_receipt']['tmp_name'];
$fileName = time() . "_" . $_FILES['gcash_receipt']['name'];
$fileType = $_FILES['gcash_receipt']['type'];

$allowedTypes = ['image/jpeg', 'image/png'];
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(["status" => "error", "message" => "Invalid file type."]);
    exit();
}

$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
$destPath = $uploadDir . $fileName;

if (!move_uploaded_file($fileTmpPath, $destPath)) {
    echo json_encode(["status" => "error", "message" => "Failed to save the file."]);
    exit();
}

$sql = "UPDATE pamisa_requests 
        SET payment_receipt = ?, status = 'Paid' 
        WHERE user_id = ? AND status = 'Pending' 
        ORDER BY id DESC LIMIT 1";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Database error."]);
    exit();
}

$stmt->bind_param("si", $destPath, $user_id);

if ($stmt->execute()) {
    $fetch_sql = "SELECT pr.*, u.email, u.name 
                  FROM pamisa_requests pr 
                  JOIN users u ON pr.user_id = u.id 
                  WHERE pr.user_id = ? AND pr.status = 'Paid' 
                  ORDER BY pr.id DESC LIMIT 1";

    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param("i", $user_id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        $email = $data['email'];
        $name_of_requestor = $data['name'];
        $name_of_intended = $data['intended_for'];
        $pamisa_type = $data['pamisa_type'];
        $selected_date = $data['selected_date'];
        $selected_time = $data['selected_time'];
        $price = $data['amount'];

        $notif_message = "Payment uploaded successfully and your request is already approved.";
        $notif_sql = "INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'unread')";
        $notif_stmt = $conn->prepare($notif_sql);
        $notif_stmt->bind_param("is", $user_id, $notif_message);
        $notif_stmt->execute();

        // Save admin notification
        $admin_notification_message = "A new payment for a Pamisa request has been received and approved.";
        $sql_admin_notification = "INSERT INTO admin_notifications (message, status) VALUES (?, 'unread')";
        $stmt_admin_notification = $conn->prepare($sql_admin_notification);
        $stmt_admin_notification->bind_param("s", $admin_notification_message);
        $stmt_admin_notification->execute();
        $stmt_admin_notification->close();

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
            $mail->Subject = "Pamisa Request Approved - Confirmation Email";
        
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;'>
                <div style='text-align: center; padding-bottom: 20px;'>
                    <h2 style='color: #2c3e50;'>Pamisa Request Approved ✅</h2>
                    <p style='color: #16a085; font-size: 18px; font-weight: bold;'>Your request has been <span style='color: #27ae60;'>APPROVED</span>!</p>
                </div>
        
                <div style='background: #ffffff; padding: 15px; border-radius: 8px; box-shadow: 0px 2px 5px rgba(0,0,0,0.1);'>
                    <p style='font-size: 16px; color: #333;'>Dear <strong>$name_of_requestor</strong>,</p>
                    <p style='font-size: 16px; color: #555;'>We are pleased to inform you that your Pamisa request has been <strong>APPROVED</strong>. Below are the details:</p>
        
                    <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                        <tr><td style='padding: 10px; font-weight: bold;'>Intended for:</td><td>$name_of_intended</td></tr>
                        <tr><td style='padding: 10px; font-weight: bold;'>Pamisa Type:</td><td>$pamisa_type</td></tr>
                        <tr><td style='padding: 10px; font-weight: bold;'>Date:</td><td>$selected_date</td></tr>
                        <tr><td style='padding: 10px; font-weight: bold;'>Time:</td><td>$selected_time</td></tr>
                        <tr><td style='padding: 10px; font-weight: bold;'>Amount Paid:</td><td><strong>PHP 100 per name $price</strong></td></tr>
                    </table>
        
                    <p style='font-size: 16px; color: #555; margin-top: 15px;'>
                        Your Pamisa has been scheduled as per the details above. Please arrive on time and bring any necessary documents if required.
                    </p>
        
                    <p style='text-align: center; margin-top: 20px; font-size: 16px; color: #333; font-weight: bold;'>
                        Thank you for your request. God bless! 🙏
                    </p>
                </div>
            </div>
            ";
        
            if ($mail->send()) {
                echo json_encode(["status" => "success", "message" => "Payment uploaded successfully. Confirmation email sent and notification saved!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Email sending failed."]);
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Mailer Error: " . $mail->ErrorInfo]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Error uploading payment."]);
}

$stmt->close();
$conn->close();
ob_end_flush();
?>
