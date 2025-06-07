<?php
include '../db_connection.php';

// Get priest_name from GET parameter, if set
$priest_name = isset($_GET['priest_name']) ? trim($_GET['priest_name']) : null;

// Prepare SQL with DISTINCT to avoid duplicates
$query = "
    SELECT DISTINCT blessing_date, blessing_time, name_of_blessed, name_of_requestor
    FROM blessings_requests 
    WHERE status != 'Cancelled' AND priest_name = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $priest_name);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
$booked_slots = [];

while ($row = $result->fetch_assoc()) {
    $iso_time = date("H:i:s", strtotime($row['blessing_time']));
    $date = $row['blessing_date'];
    $title = "Blessing: " . $row['name_of_blessed'] . " (by " . $row['name_of_requestor'] . ")";

    if (!isset($booked_slots[$date])) {
        $booked_slots[$date] = [];
    }
    if (!in_array($iso_time, $booked_slots[$date])) {
        $booked_slots[$date][] = $iso_time;
    }

    $events[] = [
        "title" => $title,
        "start" => $date . "T" . $iso_time,
        "allDay" => false,
        "backgroundColor" => "#ff0000",
        "borderColor" => "#ff0000",
        "textColor" => "#ffffff"
    ];
}

echo json_encode(["events" => $events, "booked_slots" => $booked_slots]);

$stmt->close();
$conn->close();
?>