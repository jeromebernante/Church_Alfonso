<?php
ob_start();
session_start();
include 'db_connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . mysqli_connect_error()]);
    exit();
}

if (!isset($_FILES['gcash_receipt_baptism'])) {
    echo json_encode(["status" => "error", "message" => "No file uploaded."]);
    exit();
}

if ($_FILES['gcash_receipt_baptism']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "File upload error: " . $_FILES['gcash_receipt_baptism']['error']]);
    exit();
}

$fileTmpPath = $_FILES['gcash_receipt_baptism']['tmp_name'];
$fileName = time() . "_" . basename($_FILES['gcash_receipt_baptism']['name']);
$fileType = $_FILES['gcash_receipt_baptism']['type'];
$allowedTypes = ['image/jpeg', 'image/png'];

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(["status" => "error", "message" => "Invalid file type. Only JPG and PNG are allowed."]);
    exit();
}

$uploadDir = "uploads/";
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
    echo json_encode(["status" => "error", "message" => "Failed to create upload directory."]);
    exit();
}

$destPath = $uploadDir . $fileName;

if (!move_uploaded_file($fileTmpPath, $destPath)) {
    echo json_encode(["status" => "error", "message" => "Failed to save the file."]);
    exit();
}

$query = "SELECT id FROM walkin_baptism ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(["status" => "error", "message" => "No walk-in baptism requests found in the database."]);
    exit();
}

$baptism_request_id = $row['id'];
$stmt->close();

$sql = "UPDATE walkin_baptism SET receipt_path = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    exit();
}

$stmt->bind_param("si", $destPath, $baptism_request_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Baptism receipt uploaded successfully.", "receipt_url" => $destPath]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to save receipt."]);
}

$stmt->close();
$conn->close();
ob_end_flush();
?>
