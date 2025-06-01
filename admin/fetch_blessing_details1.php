<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $requestId = $_POST['requestId'];
    $type = $_POST['type'];

    if ($type === "Online") {
        $table = "blessings_requests";
    } else {
        $table = "walkin_blessing";
    }

    $query = "SELECT * FROM $table WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo "<p><b>Requestor:</b> {$row['name_of_requestor']}</p>";
        echo "<p><b>Date:</b> " . date("F j, Y", strtotime($row['blessing_date'])) . "</p>";
        echo "<p><b>Additional Info:</b> {$row['additional_info']}</p>";
    } else {
        echo "<p style='color: red;'>Details not found.</p>";
    }
}
?>
