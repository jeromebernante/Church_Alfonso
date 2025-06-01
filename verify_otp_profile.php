<?php
session_start();
include 'db_connection.php';

if ($_POST['otp'] != $_SESSION['otp']) {
    echo json_encode(["status" => "error"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = $_SESSION['new_profile_data'];

$new_name = $data['name'];
$new_email = $data['email'];
$new_address = $data['address'];
$new_password = !empty($data['new_password']) ? password_hash($data['new_password'], PASSWORD_BCRYPT) : null;

if ($new_password) {
    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, address=?, password=? WHERE id=?");
    $stmt->bind_param("ssssi", $new_name, $new_email, $new_address, $new_password, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, address=? WHERE id=?");
    $stmt->bind_param("sssi", $new_name, $new_email, $new_address, $user_id);
}

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}
?>
