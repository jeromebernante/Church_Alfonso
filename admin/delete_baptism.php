<?php
include 'db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id']) && isset($data['type'])) {
    $id = intval($data['id']);
    $table = ($data['type'] === 'Walk-in') ? 'walkin_baptism' : 'baptism_requests';

    $query = "DELETE FROM $table WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Record deleted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Deletion failed: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid data!"]);
}
?>
