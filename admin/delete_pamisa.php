<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['requestId'])) {
    $requestId = $_POST['requestId'];

    $queryCheck = "SELECT 'pamisa_requests' AS table_name FROM pamisa_requests WHERE id = ?
                   UNION 
                   SELECT 'walkin_pamisa' AS table_name FROM walkin_pamisa WHERE id = ?";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->bind_param("ii", $requestId, $requestId);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($row = $resultCheck->fetch_assoc()) {
        $tableName = $row['table_name'];

        $queryDelete = "DELETE FROM $tableName WHERE id = ?";
        $stmtDelete = $conn->prepare($queryDelete);
        $stmtDelete->bind_param("i", $requestId);

        if ($stmtDelete->execute()) {
            echo "Mass request deleted successfully.";
        } else {
            echo "Error deleting mass request.";
        }
    } else {
        echo "Error: Record not found.";
    }
}
?>
