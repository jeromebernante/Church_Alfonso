<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];

    $check_email_stmt = $conn->prepare("SELECT user_id FROM user_type_church WHERE email = ? AND user_id != ?");
    $check_email_stmt->bind_param("si", $email, $user_id);
    $check_email_stmt->execute();
    $check_email_stmt->store_result();

    if ($check_email_stmt->num_rows > 0) {
        echo 'duplicate';
    } else {
        $stmt = $conn->prepare("UPDATE user_type_church SET username = ?, email = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $username, $email, $user_id);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }

        $stmt->close();
    }

    $check_email_stmt->close();
    $conn->close();
}
?>
