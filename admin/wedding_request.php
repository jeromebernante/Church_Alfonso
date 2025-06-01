<?php 
session_start();
include 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit();
}

$bride_name = $_POST['brideName'] ?? '';
$groom_name = $_POST['groomName'] ?? '';
$contact = $_POST['contact'] ?? '';
$wedding_date = $_POST['weddingDate'] ?? '';

if (!$bride_name || !$groom_name || !$contact || !$wedding_date) {
    echo json_encode(["status" => "error", "message" => "All fields are required except the GCash receipt."]);
    exit();
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $wedding_date)) {
    echo json_encode(["status" => "error", "message" => "Invalid date format."]);
    exit();
}

$sql_priest_check = "SELECT priest_name FROM priest_schedule WHERE date = ?";
$stmt_priest = $conn->prepare($sql_priest_check);
$stmt_priest->bind_param("s", $wedding_date);
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

$receipt_name = NULL;
if (!empty($_FILES['gcashReceipt']['name'])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $receipt_name = time() . "_" . basename($_FILES['gcashReceipt']["name"]);
    $target_file = $target_dir . $receipt_name;

    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ["jpg", "jpeg", "png", "pdf"];
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(["status" => "error", "message" => "Invalid file type. Only JPG, PNG, or PDF allowed."]);
        exit();
    }

    if (!move_uploaded_file($_FILES['gcashReceipt']["tmp_name"], $target_file)) {
        echo json_encode(["status" => "error", "message" => "Failed to upload receipt."]);
        exit();
    }
}

$conn->begin_transaction();
$sql = "INSERT INTO walkin_wedding_requests (bride_name, groom_name, contact, wedding_date, payment_receipt) 
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $bride_name, $groom_name, $contact, $wedding_date, $receipt_name);

if ($stmt->execute()) {
    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Wedding request saved successfully!"]);
} else {
    error_log("SQL Error: " . $stmt->error); 
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Failed to save request."]);
}

$stmt->close();
$conn->close();
?>
