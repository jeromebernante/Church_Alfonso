<?php
include 'db_connection.php';

$query = "
    SELECT blessing_date, blessing_time FROM blessings_requests WHERE status != 'Cancelled'
    UNION ALL
    SELECT blessing_date, blessing_time FROM walkin_blessing
";

$result = $conn->query($query);

$events = [];
$booked_slots = [];

while ($row = $result->fetch_assoc()) {
    $date = $row['blessing_date'];
    $time = date("h:i A", strtotime($row['blessing_time'])); 

    $booked_slots[$date][] = $time;

    $events[] = [
        "title"  => $time,
        "start"  => "{$date}T{$time}",
        "allDay" => false
    ];
}

echo "<h2>Booked Slots</h2>";
echo "<ul>";

foreach ($booked_slots as $date => $times) {
    echo "<li><strong>" . date("F j, Y", strtotime($date)) . "</strong>:<br>";
    echo "<ul>";
    foreach ($times as $time) {
        echo "<li>⏰ " . $time . "</li>";
    }
    echo "</ul></li>";
}

echo "</ul>";

$conn->close();
?>
