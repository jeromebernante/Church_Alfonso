<?php
session_start();
include 'db_connection.php';

$name_of_intended = $_POST['name_of_intended'];
$name_of_requestor = $_POST['name_of_requestor'];
$pamisa_type = $_POST['pamisa_type'];
$selected_date = $_POST['selected_date'];
$selected_time = $_POST['selected_time'];
$price = 100;

// Step 1: Check if any priest is scheduled for the selected date
$sql_priest_check = "SELECT priest_name FROM priest_schedule WHERE date = ?";
$stmt_priest = $conn->prepare($sql_priest_check);
$stmt_priest->bind_param("s", $selected_date);
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

// Step 2: Check if the selected date and time is already taken
$sql_check = "SELECT COUNT(*) FROM walkin_pamisa WHERE selected_date = ? AND selected_time = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ss", $selected_date, $selected_time);
$stmt_check->execute();
$stmt_check->bind_result($count);
$stmt_check->fetch();
$stmt_check->close();

if ($count > 0) {
    echo json_encode(["status" => "error", "message" => "The selected date and time are already taken."]);
    exit();
}

// Step 3: Insert the request
$sql = "INSERT INTO walkin_pamisa (name_of_intended, name_of_requestor, pamisa_type, selected_date, selected_time, price, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'Approved')";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Database error while preparing statement."]);
    exit();
}

$stmt->bind_param("sssssi", $name_of_intended, $name_of_requestor, $pamisa_type, $selected_date, $selected_time, $price);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Walk-in Pamisa request was successfully saved and approved."]);
} else {
    echo json_encode(["status" => "error", "message" => "Error saving Walk-in Pamisa request."]);
}

$stmt->close();
$conn->close();
?>
