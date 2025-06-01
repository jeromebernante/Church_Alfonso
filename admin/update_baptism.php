<?php
include 'db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id'])) {
    $id = intval($data['id']);
    $baptized_name = $data['baptized_name'];
    $parents_name = $data['parents_name'];
    $ninongs_ninangs = $data['ninongs_ninangs'];
    $status = $data['status'];
    $table = ($data['type'] === 'Walk-in') ? 'walkin_baptism' : 'baptism_requests';

    $query = "UPDATE $table SET baptized_name=?, parents_name=?, ninongs_ninangs=?, status=? WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $baptized_name, $parents_name, $ninongs_ninangs, $status, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Update successful!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing data!"]);
}
?>
