<?php
include '../db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['requestId'])) {
    $requestId = $_POST['requestId'];
    $nameOfIntended = $_POST['name_of_intended'];
    $pamisaType = $_POST['pamisa_type'];
    $selectedTime = $_POST['selected_time'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $query = "UPDATE pamisa_requests SET name_of_intended = ?, pamisa_type = ?, selected_time = ?, price = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssisi", $nameOfIntended, $pamisaType, $selectedTime, $price, $status, $requestId);

    if ($stmt->execute()) {
        echo "Mass details updated successfully.";
    } else {
        echo "Error updating mass details: " . $stmt->error;
        error_log("SQL Error: " . $stmt->error);
    }
}
?>
