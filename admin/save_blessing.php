<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');

$name_of_blessed = $_POST['name_of_blessed'];
$name_of_requestor = $_POST['name_of_requestor'];
$blessing_time = $_POST['blessing_time'];
$type_of_blessing = $_POST['type_of_blessing'];
$blessing_date = $_POST['blessing_date'];
$status = "Approved";

$receipt_path = null; 

$sql_priest_check = "SELECT priest_name FROM priest_schedule WHERE date = ?";
$stmt_priest = $conn->prepare($sql_priest_check);
$stmt_priest->bind_param("s", $blessing_date);
$stmt_priest->execute();
$stmt_priest->store_result();

if ($stmt_priest->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "No priest schedule found for the selected date."]);
    $stmt_priest->close();
    exit();
}

$stmt_priest->bind_result($scheduled_priest);
$stmt_priest->fetch();
$stmt_priest->close();

if (strtolower(trim($scheduled_priest)) === 'all priests unavailable') {
    echo json_encode(["status" => "error", "message" => "No priests are available on the selected date."]);
    exit();
}

if (!empty($_FILES["donation_receipt"]["name"])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $receipt_file = $_FILES["donation_receipt"];
    $receipt_path = $target_dir . basename($receipt_file["name"]);
    $file_type = strtolower(pathinfo($receipt_path, PATHINFO_EXTENSION));

    $allowed_types = ["jpg", "jpeg", "png", "pdf"];
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(["status" => "error", "message" => "Invalid file format. Only JPG, PNG, and PDF allowed."]);
        exit();
    }

    if (!move_uploaded_file($receipt_file["tmp_name"], $receipt_path)) {
        echo json_encode(["status" => "error", "message" => "Failed to upload receipt."]);
        exit();
    }
}

$sql_check = "SELECT COUNT(*) FROM walkin_blessing WHERE blessing_date = ? AND blessing_time = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ss", $blessing_date, $blessing_time);
$stmt_check->execute();
$stmt_check->bind_result($count);
$stmt_check->fetch();
$stmt_check->close();

if ($count > 0) {
    echo json_encode(["status" => "error", "message" => "This date and time are already booked. Please choose another slot."]);
    exit();
}

$sql = "INSERT INTO walkin_blessing (name_of_blessed, name_of_requestor, blessing_time, type_of_blessing, blessing_date, receipt_path, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Database error while saving blessing request."]);
    exit();
}

$stmt->bind_param("sssssss", $name_of_blessed, $name_of_requestor, $blessing_time, $type_of_blessing, $blessing_date, $receipt_path, $status);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Blessing request was successfully sent and is being reviewed."]);
} else {
    echo json_encode(["status" => "error", "message" => "Error saving blessing request."]);
}

$stmt->close();
$conn->close();
?>
