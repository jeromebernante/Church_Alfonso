<?php
include 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['date'])) {
    echo json_encode(["status" => "error", "message" => "Date required."]);
    exit();
}

$date = $_GET['date'];

$sql = "SELECT slots_remaining FROM blessings_slots WHERE date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    echo json_encode(["slots_remaining" => $row['slots_remaining']]);
} else {
    echo json_encode(["slots_remaining" => 0]);
}

$stmt->close();
$conn->close();
?>
