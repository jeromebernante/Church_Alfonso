<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $requestId = $_POST['requestId'];
    $type = $_POST['type'];

    if ($type === "Online") {
        $query = "DELETE FROM wedding_requests WHERE id=?";
    } else {
        $query = "DELETE FROM walkin_wedding_requests WHERE id=?";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requestId);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>
