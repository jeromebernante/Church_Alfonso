<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['requestId'])) {
    $requestId = $_POST['requestId'];

    $query = "DELETE FROM blessings_requests WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requestId);

    if ($stmt->execute()) {
        echo "Blessing request deleted successfully.";
    } else {
        echo "Error deleting request.";
    }
}
?>
