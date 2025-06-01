<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['date'])) {
    $date = $_POST['date'];

    $checkQuery = "SELECT * FROM baptism_slots WHERE date = '$date'";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows > 0) {
        echo "Date already exists!";
    } else {
        $insertQuery = "INSERT INTO baptism_slots (date, slots_remaining) VALUES ('$date', 50)";
        if ($conn->query($insertQuery)) {
            echo "Baptism slot added successfully!";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

$conn->close();
?>
