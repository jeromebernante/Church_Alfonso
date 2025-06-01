<?php
session_start();
include 'db_connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/Exception.php';
require 'PHPMailer/SMTP.php';

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'];

$stmt = $conn->prepare("SELECT password, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($stored_password, $user_email);
$stmt->fetch();
$stmt->close();

if (!password_verify($current_password, $stored_password)) {
    echo json_encode(["status" => "error", "message" => "Incorrect password."]);
    exit();
}

$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['new_profile_data'] = $_POST;

$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com'; 
$mail->SMTPAuth = true;
$mail->Username = 'parishoftheholycrossonline@gmail.com';
$mail->Password = 'xbfh zzfy ibtw klre';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('parishoftheholycrossonline@gmail.com', 'Parish of the Holy Cross');
$mail->addAddress($user_email);
$mail->Subject = "Your OTP Code";
$mail->isHTML(true);

$mail->Body = "
    <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4; text-align: center; border-radius: 10px;'>
        <div style='max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);'>
            <h2 style='color: #2c3e50;'>🔐 Secure OTP Code</h2>
            <p style='font-size: 16px; color: #333;'>Hello,</p>
            <p style='font-size: 16px; color: #555;'>You requested a One-Time Password (OTP) for verification. Please use the code below:</p>
            <p style='font-size: 24px; font-weight: bold; color: #e74c3c; background: #f9f9f9; padding: 10px; border-radius: 5px; display: inline-block;'>
                $otp
            </p>
            <p style='font-size: 14px; color: #777;'>This code is valid for a limited time. Do not share it with anyone.</p>
            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
            <p style='font-size: 14px; color: #555;'>Parish of the Holy Cross</p>
        </div>
    </div>";


if ($mail->send()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to send OTP."]);
}
?>