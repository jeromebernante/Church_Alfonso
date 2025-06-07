<?php
session_start();
include '../db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit();
}

$user_id = 0;
$bride_name = $_POST['brideName'] ?? '';
$groom_name = $_POST['groomName'] ?? '';
$priest_name = $_POST['priest_name'] ?? '';
$contact = $_POST['contact'] ?? '';
$wedding_date = $_POST['weddingDate'] ?? '';

if (!$bride_name || !$groom_name || !$contact || !$wedding_date) {
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

// Insert wedding request
$conn->begin_transaction();
$sql = "INSERT INTO wedding_requests (user_id, bride_name, groom_name, priest_name, contact, wedding_date, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issssss", $user_id, $bride_name, $groom_name, $priest_name, $contact, $wedding_date, $status);

if ($stmt->execute()) {
    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Wedding request saved successfully!"]);
} else {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Failed to save request."]);
}

$stmt->close();
$conn->close();
?>
