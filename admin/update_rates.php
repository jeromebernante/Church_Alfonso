<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $rate = $_POST['rate'];
    $additional_info = $_POST['additional_info'];

    $query = "UPDATE rates SET rate = '$rate', additional_info = '$additional_info' WHERE id = $id";

    if ($conn->query($query)) {
        echo "Success";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>
