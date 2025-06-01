<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $admin_id = $_SESSION['user_id'];

    $query = "SELECT password FROM user_type_church WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($storedPassword);
    $stmt->fetch();
    $stmt->close();

    if ($password === $storedPassword) {
        echo 'success';
    } else {
        echo 'error';
    }
}
?>
