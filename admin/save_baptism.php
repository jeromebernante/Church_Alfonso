<?php
session_start();
include '../db_connection.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['baptized_name']) || empty($data['parents_name']) || empty($data['ninongs_ninangs']) || empty($data['selected_date'])) {
    echo json_encode(["status" => "error", "message" => "Invalid or incomplete data."]);
    exit();
}

$baptized_name = trim($data['baptized_name']);
$parents_name = json_encode($data['parents_name']);
$ninongs_ninangs = json_encode($data['ninongs_ninangs']);
$selected_date = $data['selected_date'];

// Validate input lengths
if (strlen($baptized_name) > 255 || count($data['parents_name']) > 2 || count($data['ninongs_ninangs']) < 2) {
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

$status = "Accepted";

$sql = "INSERT INTO baptism_requests (user_id, baptized_name, parents_name, ninongs_ninangs, selected_date, status) 
        VALUES (?, ?, ?, ?, ?, ?)";
$user_id = 0;
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssss", $user_id, $baptized_name, $parents_name, $ninongs_ninangs, $selected_date, $status);

if ($stmt->execute()) {
    $sql = "UPDATE baptism_slots SET slots_remaining = slots_remaining - 1 WHERE date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selected_date);
    $stmt->execute();

    $conn->commit();
    echo json_encode([
        "status" => "success",
        "message" => "Baptism request saved successfully!"
    ]);
} else {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Failed to save request."]);
}

$stmt->close();
$conn->close();
?>