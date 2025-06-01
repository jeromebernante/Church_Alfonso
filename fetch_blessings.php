<?php
include 'db_connection.php';

// Get priest_name from GET parameter, if set
$priest_name = isset($_GET['priest_name']) ? trim($_GET['priest_name']) : null;

// Prepare SQL with conditional WHERE clause
$query = "
    SELECT blessing_date, blessing_time 
    FROM blessings_requests 
    WHERE status != 'Cancelled' AND priest_name = ?";

// Execute query
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $priest_name);
$stmt->execute();
$result = $stmt->get_result();

$events = array();
$booked_slots = array();

while ($row = $result->fetch_assoc()) {
    $iso_time = date("H:i:s", strtotime($row['blessing_time']));

    $date = $row['blessing_date'];
    if (!isset($booked_slots[$date])) {
        $booked_slots[$date] = array();
    }
    $booked_slots[$date][] = $iso_time;

    $events[] = array(
        "title"   => $iso_time,
        "start"   => $date . "T" . $iso_time,
        "allDay"  => false
    );
}

echo json_encode(["events" => $events, "booked_slots" => $booked_slots]);

$conn->close();
?>
