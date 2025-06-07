<?php
session_start();

include '../db_connection.php';

$user_id = 0;
$name_of_blessed = $_POST['name_of_blessed'] ?? '';
$name_of_requestor = $_POST['name_of_requestor'] ?? '';
$blessing_time = $_POST['blessing_time'] ?? '';
$priest_name = $_POST['priest_name'] ?? '';
$type_of_blessing = $_POST['type_of_blessing'] ?? '';
$blessing_date = $_POST['blessing_date'] ?? '';
$status = "Accepted";

// Extract the date (last 10 characters)
$extracted_date = substr($blessing_date, -10);

// Check priest availability
$sql_check_priest = "SELECT priest_name FROM priest_schedule WHERE date = ?";
$stmt_check_priest = $conn->prepare($sql_check_priest);
$stmt_check_priest->bind_param("s", $extracted_date);
$stmt_check_priest->execute();
$stmt_check_priest->bind_result($scheduled_priest);
$priest_unavailable = false;
$date_exists = false;

while ($stmt_check_priest->fetch()) {
    $date_exists = true;
    if ($scheduled_priest === 'All Priests Unavailable') {
        $priest_unavailable = true;
        break;
    }
}
$stmt_check_priest->close();

if ($priest_unavailable) {
    echo json_encode(["status" => "error", "message" => "No priests are available on this date. Please choose another date."]);
    exit();
}

// Check for existing booking
$sql_check = "SELECT COUNT(*) FROM blessings_requests WHERE blessing_date = ? AND blessing_time = ? AND priest_name = ? AND status != 'Cancelled'";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("sss", $extracted_date, $blessing_time, $priest_name);
$stmt_check->execute();
$stmt_check->bind_result($count);
$stmt_check->fetch();
$stmt_check->close();

if ($count > 0) {
    echo json_encode(["status" => "error", "message" => "This time slot is already booked for the selected priest and date."]);
    exit();
}

// Insert blessing request
$sql = "INSERT INTO blessings_requests (user_id, name_of_blessed, priest_name, name_of_requestor, blessing_time, type_of_blessing, blessing_date, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Database error while saving blessing request."]);
    exit();
}

$stmt->bind_param("isssssss", $user_id, $name_of_blessed, $priest_name, $name_of_requestor, $blessing_time, $type_of_blessing, $extracted_date, $status);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Success! Your request is now approved. Status: Accepted"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error saving blessing request."]);
}

$stmt->close();
$conn->close();
?>