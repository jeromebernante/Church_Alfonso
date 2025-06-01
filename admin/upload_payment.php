<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_FILES['gcash_receipt']) || $_FILES['gcash_receipt']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "File upload error."]);
    exit();
}

$fileTmpPath = $_FILES['gcash_receipt']['tmp_name'];
$fileName = time() . "_" . $_FILES['gcash_receipt']['name'];
$fileType = $_FILES['gcash_receipt']['type'];

$allowedTypes = ['image/jpeg', 'image/png'];
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(["status" => "error", "message" => "Invalid file type. Only JPG and PNG are allowed."]);
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

$sql = "UPDATE walkin_pamisa
        SET payment_receipt = ?
        WHERE status = 'Approved' 
        ORDER BY id DESC LIMIT 1";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Database error."]);
    exit();
}

$stmt->bind_param("s", $destPath);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Payment uploaded successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error updating payment."]);
}

$stmt->close();
$conn->close();
?>
