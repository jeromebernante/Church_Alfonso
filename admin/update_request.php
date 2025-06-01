<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $requestId = $_POST['requestId'];
    $type = $_POST['type'];

    if ($type === "Online") {
        $table = "wedding_requests";
    } else {
        $table = "walkin_wedding_requests";
    }

    if ($action === "delete") {
        $query = "DELETE FROM $table WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $requestId);
    } elseif ($action === "edit") {
        $brideName = $_POST['bride_name'];
        $groomName = $_POST['groom_name'];
        $contact = $_POST['contact'];
        $weddingDate = $_POST['wedding_date'];

        $query = "UPDATE $table SET bride_name = ?, groom_name = ?, contact = ?, wedding_date = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $brideName, $groomName, $contact, $weddingDate, $requestId);
    }

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
