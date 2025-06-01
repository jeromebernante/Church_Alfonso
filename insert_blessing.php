<?php
include 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['user_id'];
    $name_of_blessed = $_POST['name_of_blessed'];
    $name_of_requestor = $_POST['name_of_requestor'];
    $selected_date = $_POST['selected_date'];
    $selected_time = $_POST['selected_time'];

    // Validate that the selected day is Friday or Saturday
    $dayOfWeek = date('l', strtotime($selected_date));
    if ($dayOfWeek !== "Friday" && $dayOfWeek !== "Saturday") {
        echo json_encode(["status" => "error", "message" => "Blessings are only available on Fridays and Saturdays."]);
        exit();
    }

    // Validate allowed time slots
    $allowed_times = ["09:00:00", "11:00:00", "13:00:00", "15:00:00"];
    if (!in_array($selected_time, $allowed_times)) {
        echo json_encode(["status" => "error", "message" => "Invalid time slot selected. Choose 9 AM, 11 AM, 1 PM, or 3 PM."]);
        exit();
    }

    // Check available slots for the selected date and time
    $sql_check = "SELECT slots_remaining FROM blessings_slots WHERE date = ? AND time = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $selected_date, $selected_time);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row = $result_check->fetch_assoc();
    
    if (!$row || $row['slots_remaining'] <= 0) {
        echo json_encode(["status" => "error", "message" => "No slots remaining for this date and time."]);
        exit();
    }

    // Insert booking into the blessings table
    $sql_insert = "INSERT INTO blessings (user_id, name_of_blessed, name_of_requestor, blessing_type, selected_date, selected_time, status, payment) 
                   VALUES (?, ?, ?, 'General Blessing', ?, ?, 'pending', 'unpaid')";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("issss", $user_id, $name_of_blessed, $name_of_requestor, $selected_date, $selected_time);

    if ($stmt_insert->execute()) {
        // Decrease slot count
        $sql_update = "UPDATE blessings_slots SET slots_remaining = slots_remaining - 1 WHERE date = ? AND time = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ss", $selected_date, $selected_time);
        $stmt_update->execute();

        echo json_encode(["status" => "success", "message" => "Blessing successfully booked!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to book blessing. Please try again."]);
    }

    $stmt_check->close();
    $stmt_insert->close();
    $stmt_update->close();
    $conn->close();
}
?>
