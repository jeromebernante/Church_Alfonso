<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['requestId'])) {
    $requestId = $_POST['requestId'];
    $nameOfBlessed = $_POST['name_of_blessed'];
    $typeOfBlessing = $_POST['type_of_blessing'];
    $blessingTime = $_POST['blessing_time'];

    $queryCheck = "SELECT 'blessings_requests' AS table_name FROM blessings_requests WHERE id = ?
                   UNION 
                   SELECT 'walkin_blessing' AS table_name FROM walkin_blessing WHERE id = ?";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->bind_param("ii", $requestId, $requestId);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($row = $resultCheck->fetch_assoc()) {
        $tableName = $row['table_name'];

        $queryUpdate = "UPDATE $tableName SET name_of_blessed = ?, type_of_blessing = ?, blessing_time = ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($queryUpdate);
        $stmtUpdate->bind_param("sssi", $nameOfBlessed, $typeOfBlessing, $blessingTime, $requestId);

        if ($stmtUpdate->execute()) {
            echo "Details updated successfully.";
        } else {
            echo "Error updating details.";
        }
    } else {
        echo "Error: Record not found.";
    }
}
?>
