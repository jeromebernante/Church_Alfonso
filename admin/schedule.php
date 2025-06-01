<?php
session_start();
include 'db_connection.php'; 

header('Content-Type: application/json');

$action = isset($_GET["action"]) ? $_GET["action"] : "";

if ($action === "getPriests") {
    $result = $conn->query("SELECT name FROM priests");
    $priests = [];

    while ($row = $result->fetch_assoc()) {
        $priests[] = $row["name"];
    }

    echo json_encode(["priests" => $priests]);
    exit;
}

if ($action === "addPriest") {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data["priest_name"])) {
        $priest_name = $conn->real_escape_string($data["priest_name"]);
        $conn->query("INSERT INTO priests (name) VALUES ('$priest_name')");
        echo json_encode(["status" => "success"]);
        exit;
    }
}

if ($action === "deletePriest") {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data["priest_name"])) {
        $priest_name = $conn->real_escape_string($data["priest_name"]);
        $conn->query("DELETE FROM priests WHERE name = '$priest_name'");
        echo json_encode(["status" => "success"]);
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data["date"]) && isset($data["priest_name"])) {
        $date = $conn->real_escape_string($data["date"]);
        $priest_name = $conn->real_escape_string($data["priest_name"]);

        if ($priest_name === "Available") {
            $stmt = $conn->prepare("DELETE FROM priest_schedule WHERE date = ?");
            $stmt->bind_param("s", $date);
        } else {
            $stmt = $conn->prepare("INSERT INTO priest_schedule (date, priest_name) 
                                    VALUES (?, ?) 
                                    ON DUPLICATE KEY UPDATE priest_name = VALUES(priest_name)");
            $stmt->bind_param("ss", $date, $priest_name);
        }

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }

        $stmt->close();
        exit;
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query("SELECT date, priest_name FROM priest_schedule");
    $schedule = [];

    while ($row = $result->fetch_assoc()) {
        $schedule[$row["date"]] = $row["priest_name"];
    }

    $result = $conn->query("SELECT name FROM priests");
    $priests = [];
    while ($row = $result->fetch_assoc()) {
        $priests[] = $row["name"];
    }

    echo json_encode(["schedule" => $schedule, "priests" => $priests]);
    exit;
}

echo json_encode(["status" => "error"]);
exit;
?>
