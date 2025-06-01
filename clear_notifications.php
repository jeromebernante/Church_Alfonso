<?php
session_start();
include 'db_connection.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "UPDATE notifications SET status = 'error' WHERE user_id = ? AND status = 'success'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}
?>
