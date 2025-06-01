<?php
include 'db_connection.php';

$query1 = "SELECT selected_date, selected_time FROM pamisa_requests WHERE status != 'Cancelled'";
$result1 = $conn->query($query1);

$query2 = "SELECT selected_date, selected_time FROM walkin_pamisa";
$result2 = $conn->query($query2);

$events = array();

while ($row = $result1->fetch_assoc()) {
    $events[] = array(
        "title"  => $row['selected_time'],
        "start"  => $row['selected_date'],
        "allDay" => true
    );
}

while ($row = $result2->fetch_assoc()) {
    $events[] = array(
        "title"  => $row['selected_time'],
        "start"  => $row['selected_date'],
        "allDay" => true
    );
}

echo json_encode($events);

$conn->close();
?>
