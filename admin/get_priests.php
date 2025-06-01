<?php
include 'db_connection.php';

header('Content-Type: application/json');

$result = $conn->query("SELECT name FROM priests");
$priests = [];

while ($row = $result->fetch_assoc()) {
    $priests[] = $row;
}

echo json_encode($priests);
?>
