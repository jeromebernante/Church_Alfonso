<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid data."]);
    exit();
}

$baptized_name = $data['baptized_name'];
$parents_name = $data['parents_name'];
$ninongs_ninangs = json_encode($data['ninongs_ninangs']);
$selected_date = $data['selected_date'];

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
    echo json_encode(["status" => "error", "message" => "Invalid date format."]);
    exit();
}

// Step 1: Check priest availability for the selected date
$sql_priest_check = "SELECT priest_name FROM priest_schedule WHERE date = ?";
$stmt_priest = $conn->prepare($sql_priest_check);
$stmt_priest->bind_param("s", $selected_date);
$stmt_priest->execute();
$stmt_priest->store_result();

if ($stmt_priest->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "No priest schedule found for the selected date."]);
    $stmt_priest->close();
    exit();
}

$stmt_priest->bind_result($scheduled_priest);
$stmt_priest->fetch();
$stmt_priest->close();

if (strtolower(trim($scheduled_priest)) === 'all priests unavailable') {
    echo json_encode(["status" => "error", "message" => "No priests are available on the selected date."]);
    exit();
}

// Step 2: Check baptism slots
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

// Step 3: Save baptism request and update slots
$conn->begin_transaction();

$sql = "INSERT INTO walkin_baptism (baptized_name, parents_name, ninongs_ninangs, selected_date) 
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Database error while saving baptism request."]);
    exit();
}

$stmt->bind_param("ssss", $baptized_name, $parents_name, $ninongs_ninangs, $selected_date);

if ($stmt->execute()) {
    $sql = "UPDATE baptism_slots SET slots_remaining = slots_remaining - 1 WHERE date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selected_date);
    $stmt->execute();

    $conn->commit();
    
    echo json_encode(["status" => "success", "message" => "Baptism request was successfully submitted."]);
} else {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Failed to save baptism request."]);
}

$stmt->close();
$conn->close();
?>
