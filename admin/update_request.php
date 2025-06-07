<?php
include '../db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['requestId'])) {
    $requestId = (int)$_POST['requestId'];
    $status = $_POST['status'];

    // Validate status
    if (!in_array($status, ['Pending', 'Accepted'])) {
        echo "Invalid status value.";
        exit;
    }

    $query = "UPDATE wedding_requests SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $requestId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Wedding status updated successfully.";
        } else {
            echo "No wedding request found with ID $requestId.";
        }
    } else {
        echo "Error updating wedding status: " . $stmt->error;
        error_log("SQL Error: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request or missing parameters.";
}
?>