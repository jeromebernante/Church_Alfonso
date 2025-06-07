<?php
session_start();
include 'db_connection.php';

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

function getWeekDates($year, $week)
{
    $weekStart = strtotime("{$year}-W{$week}-1");
    $weekEnd = strtotime("+6 days", $weekStart);
    return [date("Y-m-d", $weekStart), date("Y-m-d", $weekEnd)];
}

function getWeeklyEarnings($conn, $table, $date_column, $count_column, $fixed_price = null)
{
    $sql = "SELECT YEARWEEK($date_column, 1) AS week, 
                   " . ($fixed_price ? "COUNT($count_column) * $fixed_price" : "SUM($count_column)") . " AS total_earnings
            FROM $table
            WHERE YEAR($date_column) = YEAR(CURDATE()) 
            GROUP BY week
            ORDER BY week ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $weekly_earnings = [];
    while ($row = $result->fetch_assoc()) {
        $weekNum = substr($row['week'], 4);
        list($start_date, $end_date) = getWeekDates(date("Y"), $weekNum);
        $weekLabel = "Week $weekNum ($start_date to $end_date)";
        $weekly_earnings[$weekLabel] = $row['total_earnings'] ?? 0;
    }
    return $weekly_earnings;
}

$weekly_baptism = getWeeklyEarnings($conn, 'baptism_requests', 'selected_date', 'price');
$weekly_pamisa = getWeeklyEarnings($conn, 'pamisa_requests', 'selected_date', 'price');
$weekly_wedding = getWeeklyEarnings($conn, 'wedding_requests', 'wedding_date', 'id', 7000);
$weekly_blessings = getWeeklyEarnings($conn, 'blessings_requests', 'blessing_date', 'id', 500);

$all_weeks = array_unique(array_merge(
    array_keys($weekly_baptism),
    array_keys($weekly_pamisa),
    array_keys($weekly_wedding),
    array_keys($weekly_blessings)
));
sort($all_weeks);

$total_earnings = array_sum($weekly_baptism) + array_sum($weekly_pamisa) + array_sum($weekly_wedding) + array_sum($weekly_blessings);

$sql_weekly = "SELECT SUM(price) AS weekly_total FROM (
    SELECT price FROM baptism_requests WHERE YEARWEEK(selected_date, 1) = YEARWEEK(CURDATE(), 1)
    UNION ALL
    SELECT price FROM pamisa_requests WHERE YEARWEEK(selected_date, 1) = YEARWEEK(CURDATE(), 1)
    UNION ALL
    SELECT 7000 FROM wedding_requests WHERE YEARWEEK(wedding_date, 1) = YEARWEEK(CURDATE(), 1)
    UNION ALL
    SELECT 500 FROM blessings_requests WHERE YEARWEEK(blessing_date, 1) = YEARWEEK(CURDATE(), 1)
) as earnings";
$result_weekly = $conn->query($sql_weekly);
$weekly_earnings = ($result_weekly->num_rows > 0) ? $result_weekly->fetch_assoc()['weekly_total'] : 0;

$sql_monthly = "SELECT SUM(price) AS monthly_total FROM (
    SELECT price FROM baptism_requests WHERE YEAR(selected_date) = YEAR(CURDATE()) AND MONTH(selected_date) = MONTH(CURDATE())
    UNION ALL
    SELECT price FROM pamisa_requests WHERE YEAR(selected_date) = YEAR(CURDATE()) AND MONTH(selected_date) = MONTH(CURDATE())
    UNION ALL
    SELECT 7000 FROM wedding_requests WHERE YEAR(wedding_date) = YEAR(CURDATE()) AND MONTH(wedding_date) = MONTH(CURDATE())
    UNION ALL
    SELECT 500 FROM blessings_requests WHERE YEAR(blessing_date) = YEAR(CURDATE()) AND MONTH(blessing_date) = MONTH(CURDATE())
) as earnings";
$result_monthly = $conn->query($sql_monthly);
$monthly_earnings = ($result_monthly->num_rows > 0) ? $result_monthly->fetch_assoc()['monthly_total'] : 0;


$filtered_week = isset($_POST['filter_week']) ? $_POST['filter_week'] : null;

$sql_baptism = "SELECT SUM(price) AS total_baptism_earnings FROM baptism_requests";
$result_baptism = $conn->query($sql_baptism);
$baptism_earnings = ($result_baptism->num_rows > 0) ? $result_baptism->fetch_assoc()['total_baptism_earnings'] : 0;

$sql_pamisa = "SELECT SUM(price) AS total_pamisa_earnings FROM pamisa_requests";
$result_pamisa = $conn->query($sql_pamisa);
$pamisa_earnings = ($result_pamisa->num_rows > 0) ? $result_pamisa->fetch_assoc()['total_pamisa_earnings'] : 0;

$sql_wedding = "SELECT COUNT(payment_receipt) AS total_weddings FROM wedding_requests";
$result_wedding = $conn->query($sql_wedding);
$wedding_earnings = ($result_wedding->num_rows > 0) ? $result_wedding->fetch_assoc()['total_weddings'] * 7000 : 0;

$sql_blessings = "SELECT COUNT(receipt_path) AS total_blessings FROM blessings_requests";
$result_blessings = $conn->query($sql_blessings);
$blessings_earnings = ($result_blessings->num_rows > 0) ? $result_blessings->fetch_assoc()['total_blessings'] * 500 : 0;

$sql_baptism_slots = "SELECT date, slots_remaining FROM baptism_slots ORDER BY date ASC";
$result_baptism_slots = $conn->query($sql_baptism_slots);

$sql_pamisa = "SELECT selected_date, name_of_intended, pamisa_type, status FROM pamisa_requests ORDER BY selected_date ASC";
$result_pamisa = $conn->query($sql_pamisa);

$sql_wedding = "SELECT wedding_date, bride_name, groom_name, status FROM wedding_requests ORDER BY wedding_date ASC";
$result_wedding = $conn->query($sql_wedding);

$sql_blessings = "SELECT blessing_date, name_of_blessed, type_of_blessing, status FROM blessings_requests ORDER BY blessing_date ASC";
$result_blessings = $conn->query($sql_blessings);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $filter_date = isset($_POST['filter_date']) ? $_POST['filter_date'] : null;
    $selected_table = isset($_POST['table']) ? $_POST['table'] : null;

    if (!$filter_date || !$selected_table) {
        echo "<p> </p>";
    } else {
        $table_columns = [
            'baptism_slots' => ['date', 'slots_remaining'],
            'pamisa_requests' => ['selected_date', 'name_of_intended', 'pamisa_type', 'status'],
            'wedding_requests' => ['wedding_date', 'bride_name', 'groom_name', 'status'],
            'blessings_requests' => ['blessing_date', 'name_of_blessed', 'type_of_blessing', 'status']
        ];

        if (!isset($table_columns[$selected_table])) {
            echo "<p>Invalid table selection.</p>";
        } else {
            $date_column = [
                'baptism_slots' => 'date',
                'pamisa_requests' => 'selected_date',
                'wedding_requests' => 'wedding_date',
                'blessings_requests' => 'blessing_date'
            ][$selected_table];

            $columns = implode(", ", $table_columns[$selected_table]);
            $sql = "SELECT $columns FROM $selected_table WHERE $date_column = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $filter_date);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<h3>Results for " . ucfirst(str_replace("_", " ", $selected_table)) . " on " . $filter_date . "</h3>";
                echo "<div id='printSection'><table border='1'><tr>";

                foreach ($table_columns[$selected_table] as $col) {
                    echo "<th>" . ucfirst(str_replace("_", " ", $col)) . "</th>";
                }
                echo "</tr>";

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($table_columns[$selected_table] as $col) {
                        echo "<td>" . htmlspecialchars($row[$col]) . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table></div>";

                echo "<button onclick='printFilteredResults()'>Print</button>";
            } else {
                echo "<p>No records found for the selected date.</p>";
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parish of the Holy Cross</title>
    <link rel="stylesheet" href="stylesd.css">
    <link rel="stylesheet" href="buttons.css">
    <link rel="icon" href="imgs/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="scriptd.js"></script>
</head>

<body id="bodyTag">
    <header class="header" id="header">

    </header>
    <?php include 'viewer_sidebar.php'; ?><br>

    <?php
    // Assuming the username is stored in the session
    $username = $_SESSION['username'];
    $name = '';

    if ($username === 'frroxas') {
        $name = 'Rev. Fr. Apolinario Roxas, Jr.';
    } elseif ($username === 'frroel') {
        $name = 'Rev. Fr. Roel Aldwin C. Valmadrid';
    } else {
        $name = 'Guest'; // Fallback for unknown usernames
    }
    ?>

    <div class="admin-greeting">Good Day, <?php echo $name; ?>!</div>
    <div id="datetime" class="datetime"></div>

    <section class="about-us">
        <h2 style="color: black; font-size: 20px;">Your Reports</h2>
        <p class="justified">
            The Reports section allows you to access and review detailed records of service reservations, church events,
            and print reports. Stay informed and keep track of essential parish data efficiently.
        </p>
    </section>





    <section class="upcoming-events">
        <br>
        <h2>All Events</h2>


        <form id="filterForm"
            style="display: flex; align-items: center; gap: 10px; background: white; padding: 10px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); font-family: Arial, sans-serif; width: max-content; margin: auto;">

            <label for="filter_date" style="font-weight: bold;">Date:</label>
            <input type="date" id="filter_date" name="filter_date"
                style="padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">

            <label for="table" style="font-weight: bold;">Table:</label>
            <select id="table" name="table"
                style="padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                <option value="baptism_slots">Baptism Slots</option>
                <option value="pamisa_requests">Pamisa Requests</option>
                <option value="wedding_requests">Wedding Requests</option>
                <option value="blessings_requests">Blessings Requests</option>
            </select>

            <button type="button" onclick="filterResults()"
                style="padding: 8px 15px; background-color: #007bff; color: white; font-weight: bold; border: none; border-radius: 5px; font-size: 14px; cursor: pointer;">Filter</button>

        </form>


        <script>
            function filterResults() {
                var filterDate = document.getElementById('filter_date').value;
                var selectedTable = document.getElementById('table').value;

                if (!filterDate) {
                    alert("Please select a date.");
                    return;
                }

                var newWindow = window.open("", "_blank", "width=800,height=600");

                if (!newWindow) {
                    alert("Please allow pop-ups for this site.");
                    return;
                }

                var formData = new FormData();
                formData.append("filter_date", filterDate);
                formData.append("table", selectedTable);

                fetch("fetch_filtered_results.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        newWindow.document.open();
                        newWindow.document.write(html);
                        newWindow.document.close();
                    })
                    .catch(error => console.error("Error:", error));
            }
        </script>
        <br>

        <div class="section-container"
            style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px; font-family: Arial, sans-serif;">
            <h3 style="margin-bottom: 10px;">Baptism Slots</h3>
            <button onclick="printTable('baptismTable')"
                style="padding: 8px 15px; background-color: #007bff; color: white; font-weight: bold; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; margin-bottom: 10px;">Print
                Baptism</button>

            <div class="table-container" style="overflow-x: auto;">
                <table id="baptismTable" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #007bff; color: white;">
                            <th style="padding: 10px; border: 1px solid #ddd;">Date</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Slots Remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_baptism_slots->fetch_assoc()): ?>
                            <tr style="text-align: center;">
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['date']; ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo $row['slots_remaining'] . "/50"; ?></td>

                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Pamisa Requests -->
        <div class="section-container"
            style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px; font-family: Arial, sans-serif;">
            <h3 style="margin-bottom: 10px;">Pamisa Requests</h3>
            <button onclick="printTable('pamisaTable')"
                style="padding: 8px 15px; background-color: #007bff; color: white; font-weight: bold; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; margin-bottom: 10px;">Print
                Pamisa</button>

            <div class="table-container" style="overflow-x: auto;">
                <table id="pamisaTable" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #007bff; color: white;">
                            <th style="padding: 10px; border: 1px solid #ddd;">Date</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Intended Name</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Type</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_pamisa->fetch_assoc()): ?>
                            <tr style="text-align: center;">
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['selected_date']; ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['name_of_intended']; ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['pamisa_type']; ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['status']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Wedding Requests -->
        <div class="section-container"
            style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px; font-family: Arial, sans-serif;">
            <h3 style="margin-bottom: 10px;">Wedding Requests</h3>
            <button onclick="printTable('weddingTable')"
                style="padding: 8px 15px; background-color: #007bff; color: white; font-weight: bold; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; margin-bottom: 10px;">Print
                Wedding</button>

            <div class="table-container" style="overflow-x: auto;">
                <table id="weddingTable" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #007bff; color: white;">
                            <th style="padding: 10px; border: 1px solid #ddd;">Wedding Date</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Bride Name</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Groom Name</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_wedding->fetch_assoc()): ?>
                            <tr style="text-align: center;">
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['wedding_date']; ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['bride_name']; ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['groom_name']; ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['status']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Blessings Requests -->
        <div class="section-container"
            style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px; font-family: Arial, sans-serif;">
            <h3 style="margin-bottom: 10px;">Blessings Requests</h3>
            <button onclick="printTable('blessingsTable')"
                style="padding: 8px 15px; background-color: #007bff; color: white; font-weight: bold; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; margin-bottom: 10px;">Print
                Blessings</button>

            <div class="table-container" style="overflow-x: auto;">
                <table id="blessingsTable" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #007bff; color: white;">
                            <th style="padding: 10px; border: 1px solid #ddd;">Date</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Blessed Name</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Type</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_blessings->fetch_assoc()): ?>
                            <tr style="text-align: center;">
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['blessing_date']; ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['name_of_blessed']; ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['type_of_blessing']; ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['status']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>

        <script>
            function printTable(tableId) {
                var table = document.getElementById(tableId);
                var section = table.closest(".section-container");
                var title = section.querySelector("h3").innerText;

                var printContent = `
            <html>
            <head>
                <title>${title}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    h2 { text-align: center; margin-bottom: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                    th { background-color: #2C3E50; color: white; }
                </style>
            </head>
            <body>
                <h2>${title}</h2>
                ${table.outerHTML}
            </body>
            </html>`;

                var newWindow = window.open("", "", "width=800,height=600");
                newWindow.document.write(printContent);
                newWindow.document.close();
                newWindow.print();
            }

            function printFilteredResults() {
                var printContent = document.getElementById("printSection").innerHTML;
                var newWindow = window.open("", "", "width=800,height=600");
                newWindow.document.write("<html><head><title>Print</title>");
                newWindow.document.write("<style>");
                newWindow.document.write("table {width: 100%; border-collapse: collapse;}");
                newWindow.document.write("th, td {border: 1px solid #000; padding: 8px; text-align: left;}");
                newWindow.document.write("th {background-color: #2C3E50; color: white;}");
                newWindow.document.write("</style></head><body>");
                newWindow.document.write(printContent);
                newWindow.document.write("</body></html>");
                newWindow.document.close();
                newWindow.print();
            }
        </script>


        <footer>
            <div class="footer-container">
                <div class="footer-about">
                    <h3>About Parish of the Holy Cross</h3>
                    <p>
                        The Parish of the Holy Cross is a sacred place of worship, where the community comes together to
                        celebrate faith, hope, and love. Whether you're seeking spiritual growth, a peaceful moment of
                        reflection, or a place to connect with others, our church provides a welcoming environment for
                        all.
                    </p>

                </div>
                <div class="footer-contact">
                    <h3>Contact Us</h3>
                    <p>Email: holycrossparish127@yahoo.com</p>
                    <p>Phone: 28671581</p>
                    <p>Address: Gen. T. De Leon, Valenzuela, Philippines, 1442 </p>
                </div>
                <div class="footer-socials">
                    <h3>Follow Us</h3>
                    <a href="https://www.facebook.com/ParishoftheHolyCrossValenzuelaCityOfficial/">Facebook</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Parish of the Holy Cross. All rights reserved.</p>
            </div>
        </footer>


        <script>
            <?php if (!empty($alertMessage))
                echo $alertMessage; ?>

            function updateDateTime() {
                let now = new Date();
                let options = {
                    timeZone: 'Asia/Manila',
                    hour12: true,
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                document.getElementById('datetime').innerHTML = new Intl.DateTimeFormat('en-PH', options).format(now);
            }

            updateDateTime();
            setInterval(updateDateTime, 60000);
        </script>
        <style>
            .upcoming-events {
                margin: 20px;
                text-align: center;
            }

            .event-section {
                margin-bottom: 30px;
            }

            .table-container {
                max-height: 250px;
                overflow-y: auto;
                border: 1px solid #ddd;
                border-radius: 5px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                padding: 10px;
                border-bottom: 1px solid #ddd;
            }

            th {
                background-color: #2C3E50;
                color: white;
            }

            button {
                margin: 10px;
                padding: 8px 15px;
                background-color: #2C3E50;
                color: white;
                border: none;
                cursor: pointer;
                border-radius: 5px;
            }

            button:hover {
                background-color: #1A252F;
            }

            .overview-section {
                margin: 20px;
                text-align: center;
            }

            .overview-boxes {
                display: flex;
                justify-content: space-around;
                flex-wrap: wrap;
            }

            .box h3 {
                margin-bottom: 10px;
            }

            .overview-section {
                margin: 20px;
                text-align: center;
            }

            .overview-boxes {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 20px;
            }

            .box {
                background-color: #2c3e50;
                color: white;
                border-radius: 15px;
                padding: 25px;
                width: 300px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                text-align: center;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .box:hover {
                transform: translateY(-5px);
                box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
            }

            .box h3 {
                font-size: 18px;
                margin-bottom: 10px;
                color: #f8c471;
            }

            .box p {
                font-size: 22px;
                font-weight: bold;
                margin: 0;
            }

            .header {
                background: #2c3e50;
                color: white;
            }


            .datetime {
                text-align: center;
                font-size: 18px;
                color: #555;
                margin-bottom: 20px;
            }

            .admin-greeting {
                text-align: center;
                font-size: 35px;
                font-weight: bold;
                color: rgb(88, 177, 90);
            }

            body {
                font-family: Arial, sans-serif;
                background-color: rgb(241, 243, 240);
            }

            .table-container {
                max-height: 400px;
                overflow-y: auto;
                border: 1px solid #ccc;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                padding: 10px;
                text-align: center;
                border: 1px solid black;
            }
        </style>
</body>

</html>