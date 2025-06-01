<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $filter_date = $_POST['filter_date'];
    $selected_table = $_POST['table'];

    // Define the columns for each table
    $table_columns = [
        'baptism_slots' => ['date', 'slots_remaining'],
        'pamisa_requests' => ['selected_date', 'name_of_intended', 'pamisa_type', 'status'],
        'wedding_requests' => ['wedding_date', 'bride_name', 'groom_name', 'status'],
        'blessings_requests' => ['blessing_date', 'name_of_blessed', 'type_of_blessing', 'status']
    ];

    // Define column name for date filtering
    $date_column = [
        'baptism_slots' => 'date',
        'pamisa_requests' => 'selected_date',
        'wedding_requests' => 'wedding_date',
        'blessings_requests' => 'blessing_date'
    ][$selected_table];

    // Build SQL query dynamically with selected columns
    $columns = implode(", ", $table_columns[$selected_table]);
    $sql = "SELECT $columns FROM $selected_table WHERE $date_column = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $filter_date);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<html><head><title>Filtered Results</title>";
    echo "<style>
            body { font-family: Arial, sans-serif; padding: 20px; text-align: center; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { padding: 10px; border: 1px solid black; text-align: center; }
            th { background-color: #2C3E50; color: white; }
            button { margin-top: 15px; padding: 8px 15px; background-color: #2C3E50; color: white; border: none; cursor: pointer; border-radius: 5px; }
            button:hover { background-color: #1A252F; }
          </style>";
    echo "</head><body>";

    if ($result->num_rows > 0) {
        echo "<h3>Results for " . ucfirst(str_replace("_", " ", $selected_table)) . " on " . $filter_date . "</h3>";
        echo "<table border='1'><tr>";

        // Print table headers dynamically
        foreach ($table_columns[$selected_table] as $col) {
            echo "<th>" . ucfirst(str_replace("_", " ", $col)) . "</th>";
        }
        echo "</tr>";

        // Print table rows
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($table_columns[$selected_table] as $col) {
                echo "<td>" . htmlspecialchars($row[$col]) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";

        // Print button
        echo "<button onclick='window.print()'>Print</button>";
    } else {
        echo "<p>No records found for the selected date.</p>";
    }

    echo "</body></html>";

    $stmt->close();
}

$conn->close();
?>
