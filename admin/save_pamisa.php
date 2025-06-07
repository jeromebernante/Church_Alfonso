<?php
session_start();
include '../db_connection.php';

$user_id = 0;

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

// Time validation
date_default_timezone_set('Asia/Manila');
$current_date = date('Y-m-d');
$current_time = date('H:i');
$selected_time_24 = date('H:i', strtotime($selected_time));

if ($selected_date === $current_date && $selected_time_24 <= $current_time) {
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
    echo json_encode(["status" => "error", "message" => "No priests are available on the selected date."]);
    exit();
}

// Insert Pamisa request
$sql = "INSERT INTO pamisa_requests (user_id, name_of_intended, name_of_requestor, pamisa_type, selected_date, selected_time, price, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Accepted')";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    error_log("Database error: " . $conn->error);
    echo json_encode(["status" => "error", "message" => "Database error while saving pamisa request."]);
    exit();
}

$stmt->bind_param("isssssi", $user_id, $name_of_intended, $name_of_requestor, $pamisa_type, $selected_date, $selected_time, $price);
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Pamisa request was successfully sent."]);
} else {
    error_log("Database error: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Error saving pamisa request."]);
}

$stmt->close();
$conn->close();
?>