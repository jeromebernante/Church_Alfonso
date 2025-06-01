<?php
session_start();
include 'db_connection.php';
$user_id = $_SESSION['user_id'];

$sql = "UPDATE notifications SET status = 'read' WHERE user_id = ? AND status = 'unread'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

echo "Notifications marked as read";
?>
