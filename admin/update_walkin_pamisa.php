<?php
include 'db_connection.php';

file_put_contents("debug_log.txt", print_r($_POST, true), FILE_APPEND); 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['requestId'])) {
    $requestId = $_POST['requestId'];
    $nameOfIntended = $_POST['name_of_intended'];
    $pamisaType = $_POST['pamisa_type'];
    $selectedTime = $_POST['selected_time'];
    $price = $_POST['price'];

    file_put_contents("debug_log.txt", "Updating ID: $requestId \n", FILE_APPEND);

    $query = "UPDATE walkin_pamisa SET name_of_intended = ?, pamisa_type = ?, selected_time = ?, price = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssdi", $nameOfIntended, $pamisaType, $selectedTime, $price, $requestId);

    if ($stmt->execute()) {
        echo "Walk-in Mass details updated successfully.";
    } else {
        echo "Error updating Walk-in Mass details: " . $stmt->error;
        error_log("SQL Error: " . $stmt->error);
    }
}
?>
