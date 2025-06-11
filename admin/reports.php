<?php
session_start();
include 'db_connection.php';

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

function getDateRangeData($conn, $table, $date_column, $count_column, $start_date, $end_date, $fixed_price = null)
{
    $sql = "SELECT 
                COUNT($count_column) AS event_count,
                " . ($fixed_price ? "COUNT($count_column) * $fixed_price" : "SUM($count_column)") . " AS total_earnings
            FROM $table
            WHERE $date_column BETWEEN ? AND ?";

    if ($table === 'blessings_requests') {
        $sql .= " AND receipt_path IS NOT NULL";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    return [
        'count' => $data['event_count'] ?? 0,
        'earnings' => $data['total_earnings'] ?? 0
    ];
}

function getRequestDetails($conn, $table, $date_column, $start_date, $end_date)
{
    $sql = "SELECT * FROM $table WHERE $date_column BETWEEN ? AND ? ORDER BY $date_column ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    return $stmt->get_result();
}

// Initialize filter state
$filter_type = $_SESSION['filter_type'] ?? 'month';
$start_date = $_SESSION['start_date'] ?? null;
$end_date = $_SESSION['end_date'] ?? null;
$filter_month = $_SESSION['filter_month'] ?? date('m');
$filter_year = $_SESSION['filter_year'] ?? date('Y');
$filter_year_only = $_SESSION['filter_year_only'] ?? date('Y');
$overview_title = "Earnings Overview for " . date('F Y');

// Set date range based on filter
if ($filter_type === 'month') {
    $start_date = "$filter_year-$filter_month-01";
    $end_date = date('Y-m-t', strtotime("$filter_year-$filter_month-01"));
    $overview_title = "Earnings Overview for " . date('F Y', strtotime("$filter_year-$filter_month-01"));
} elseif ($filter_type === 'year') {
    $start_date = "$filter_year_only-01-01";
    $end_date = "$filter_year_only-12-31";
    $overview_title = "Earnings Overview for $filter_year_only";
} elseif ($filter_type === 'date_range' && $start_date && $end_date) {
    $start_date_formatted = date('F j, Y', strtotime($start_date));
    $end_date_formatted = date('F j, Y', strtotime($end_date));
    $overview_title = "Earnings Overview for $start_date_formatted to $end_date_formatted";
}

// Process filter form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Reset all session variables
    unset($_SESSION['filter_type'], $_SESSION['start_date'], $_SESSION['end_date'], 
          $_SESSION['filter_month'], $_SESSION['filter_year'], $_SESSION['filter_year_only']);

    if (isset($_POST['filter_type'])) {
        $filter_type = $_POST['filter_type'];
        $_SESSION['filter_type'] = $filter_type;

        if ($filter_type === 'date_range' && isset($_POST['start_date']) && isset($_POST['end_date'])) {
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $_SESSION['start_date'] = $start_date;
            $_SESSION['end_date'] = $end_date;
            $start_date_formatted = date('F j, Y', strtotime($start_date));
            $end_date_formatted = date('F j, Y', strtotime($end_date));
            $overview_title = "Earnings Overview for $start_date_formatted to $end_date_formatted";
        } elseif ($filter_type === 'month' && isset($_POST['filter_month']) && isset($_POST['filter_year'])) {
            $filter_month = $_POST['filter_month'];
            $filter_year = $_POST['filter_year'];
            $_SESSION['filter_month'] = $filter_month;
            $_SESSION['filter_year'] = $filter_year;
            $start_date = "$filter_year-$filter_month-01";
            $end_date = date('Y-m-t', strtotime("$filter_year-$filter_month-01"));
            $overview_title = "Earnings Overview for " . date('F Y', strtotime("$filter_year-$filter_month-01"));
        } elseif ($filter_type === 'year' && isset($_POST['filter_year_only'])) {
            $filter_year_only = $_POST['filter_year_only'];
            $_SESSION['filter_year_only'] = $filter_year_only;
            $start_date = "$filter_year_only-01-01";
            $end_date = "$filter_year_only-12-31";
            $overview_title = "Earnings Overview for $filter_year_only";
        }
    }
}

// Calculate earnings for the selected period
$baptism_data = getDateRangeData($conn, 'baptism_requests', 'selected_date', 'price', $start_date, $end_date);
$pamisa_data = getDateRangeData($conn, 'pamisa_requests', 'selected_date', 'price', $start_date, $end_date);
$wedding_data = getDateRangeData($conn, 'wedding_requests', 'wedding_date', 'id', $start_date, $end_date, 7000);
$blessings_data = getDateRangeData($conn, 'blessings_requests', 'blessing_date', 'id', $start_date, $end_date, 500);

$total_earnings = $baptism_data['earnings'] + $pamisa_data['earnings'] + $wedding_data['earnings'] + $blessings_data['earnings'];
$baptism_earnings = $baptism_data['earnings'];
$pamisa_earnings = $pamisa_data['earnings'];
$wedding_earnings = $wedding_data['earnings'];
$blessings_earnings = $blessings_data['earnings'];

$range_data = [
    'baptism' => $baptism_data,
    'pamisa' => $pamisa_data,
    'wedding' => $wedding_data,
    'blessings' => $blessings_data,
    'total_earnings' => $total_earnings
];

$baptism_details = getRequestDetails($conn, 'baptism_requests', 'selected_date', $start_date, $end_date);
$pamisa_details = getRequestDetails($conn, 'pamisa_requests', 'selected_date', $start_date, $end_date);
$wedding_details = getRequestDetails($conn, 'wedding_requests', 'wedding_date', $start_date, $end_date);
$blessings_details = getRequestDetails($conn, 'blessings_requests', 'blessing_date', $start_date, $end_date);

// Fetch other data
$sql_baptism_slots = "SELECT date, slots_remaining FROM baptism_slots ORDER BY date ASC";
$result_baptism_slots = $conn->query($sql_baptism_slots);

$sql_pamisa = "SELECT selected_date, name_of_intended, pamisa_type, status FROM pamisa_requests ORDER BY selected_date ASC";
$result_pamisa = $conn->query($sql_pamisa);

$sql_wedding = "SELECT wedding_date, bride_name, groom_name, status FROM wedding_requests ORDER BY wedding_date ASC";
$result_wedding = $conn->query($sql_wedding);

$sql_blessings = "SELECT blessing_date, name_of_blessed, type_of_blessing, status FROM blessings_requests ORDER BY blessing_date ASC";
$result_blessings = $conn->query($sql_blessings);

// Handle date filter form for other tables
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['filter_date']) && isset($_POST['table'])) {
    $filter_date = $_POST['filter_date'];
    $selected_table = $_POST['table'];

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
    <?php include 'sidebar.php'; ?><br>

    <div class="admin-greeting">Good Day, Admin!</div>
    <div id="datetime" class="datetime"></div>

    <section class="about-us">
        <h2 style="color: black; font-size: 20px;">Your Reports</h2>
        <p class="justified">
            The Reports section allows you to access and review detailed records of service reservations, church events, and other administrative activities. Stay informed and keep track of essential parish data efficiently.
        </p>
    </section>

    <div id="earningsOverViewThisMonth">
        <center>
            <h2 style="color: black;"><?php echo $overview_title; ?></h2><br>
        </center>

        <center><button onclick="printEarnings()">Print Summary</button></center>

        <section class="overview-summary">
            <div class="overview-boxes">
                <div class="box">
                    <h3>All Total Earnings</h3>
                    <p>₱<?php echo number_format($total_earnings, 2); ?></p>
                </div>
                <div class="box">
                    <h3>Total Baptism Earnings</h3>
                    <p>₱<?php echo number_format($baptism_earnings, 2); ?></p>
                </div>
                <div class="box">
                    <h3>Total Mass Earnings</h3>
                    <p>₱<?php echo number_format($pamisa_earnings, 2); ?></p>
                </div>
                <div class="box">
                    <h3>Total Wedding Earnings</h3>
                    <p>₱<?php echo number_format($wedding_earnings, 2); ?></p>
                </div>
                <div class="box">
                    <h3>Total Blessings Earnings</h3>
                    <p>₱<?php echo number_format($blessings_earnings, 2); ?></p>
                </div>
            </div>
            <center style="margin-top: 100px;">
                <p style="margin-top: 10px; margin-bottom: 1rem; color: black; font-weight: bold;">Select Filter to Print Events and Earnings</p>
            </center>
        </section>
    </div>

    <script>
        function printEarnings() {
            var printContent = document.body.innerHTML;
            var originalContent = document.body.innerHTML;
            var printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write('<html><head><title>Print Earnings</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: Arial, sans-serif; color: black; }');
            printWindow.document.write('.overview-boxes { display: flex; flex-wrap: wrap; gap: 10px; }');
            printWindow.document.write('.box { border: 1px solid black; padding: 10px; text-align: center; width: 200px; }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<h2 style="text-align: center;"><?php echo $overview_title; ?></h2>');
            printWindow.document.write(document.querySelector('.overview-summary').outerHTML);
            printWindow.document.write('</body></html>');

            printWindow.document.close();
            printWindow.print();
        }
    </script>

    <section id="selectDatesToPrintEventsAndEarnings" class="earnings-section">
        <form method="POST" style="background: white; padding: 15px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); font-family: Arial, sans-serif; width: max-content; margin: auto;">
            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; font-size: 14px; margin-right: 10px;">Filter Type:</label>
                <select name="filter_type" id="filter_type" onchange="toggleFilterFields()" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                    <option value="date_range" <?php echo $filter_type === 'date_range' ? 'selected' : ''; ?>>Date Range</option>
                    <option value="month" <?php echo $filter_type === 'month' ? 'selected' : ''; ?>>Month and Year</option>
                    <option value="year" <?php echo $filter_type === 'year' ? 'selected' : ''; ?>>Year Only</option>
                </select>
            </div>

            <div id="date_range_fields" style="display: <?php echo $filter_type === 'date_range' ? 'flex' : 'none'; ?>; align-items: center; gap: 10px; margin-bottom: 10px;">
                <label for="start_date" style="font-weight: bold; font-size: 14px;">From:</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($start_date ?? ''); ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">

                <label for="end_date" style="font-weight: bold; font-size: 14px;">To:</label>
                <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($end_date ?? ''); ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
            </div>

            <div id="month_year_fields" style="display: <?php echo $filter_type === 'month' ? 'flex' : 'none'; ?>; align-items: center; gap: 10px; margin-bottom: 10px;">
                <label for="filter_month" style="font-weight: bold; font-size: 14px;">Month:</label>
                <select name="filter_month" id="filter_month" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                    <?php
                    for ($m = 1; $m <= 12; $m++) {
                        $month_name = date('F', mktime(0, 0, 0, $m, 1));
                        $selected = $filter_month == sprintf("%02d", $m) ? 'selected' : '';
                        echo "<option value='" . sprintf("%02d", $m) . "' $selected>$month_name</option>";
                    }
                    ?>
                </select>

                <label for="filter_year" style="font-weight: bold; font-size: 14px;">Year:</label>
                <input type="number" name="filter_year" id="filter_year" value="<?php echo htmlspecialchars($filter_year); ?>" min="2000" max="2099" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; width: 80px;">
            </div>

            <div id="year_only_fields" style="display: <?php echo $filter_type === 'year' ? 'flex' : 'none'; ?>; align-items: center; gap: 10px; margin-bottom: 10px;">
                <label for="filter_year_only" style="font-weight: bold; font-size: 14px;">Year:</label>
                <input type="number" name="filter_year_only" id="filter_year_only" value="<?php echo htmlspecialchars($filter_year_only); ?>" min="2000" max="2099" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; width: 80px;">
            </div>

            <button type="submit" style="padding: 8px 15px; background-color: #007bff; color: white; font-weight: bold; border: none; border-radius: 5px; font-size: 14px; cursor: pointer;">
                Filter
            </button>
        </form>
        <br>

        <script>
            function toggleFilterFields() {
                const filterType = document.getElementById('filter_type').value;
                document.getElementById('date_range_fields').style.display = filterType === 'date_range' ? 'flex' : 'none';
                document.getElementById('month_year_fields').style.display = filterType === 'month' ? 'flex' : 'none';
                document.getElementById('year_only_fields').style.display = filterType === 'year' ? 'flex' : 'none';
            }
        </script>

        <?php if ($range_data): ?>
            <div class="table-container">
                <table border="1">
                    <tr>
                        <th>Category</th>
                        <th>Count</th>
                        <th>Earnings</th>
                    </tr>
                    <tr>
                        <td>Baptism</td>
                        <td><?php echo $range_data['baptism']['count']; ?></td>
                        <td>₱<?php echo number_format($range_data['baptism']['earnings'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Mass</td>
                        <td><?php echo $range_data['pamisa']['count']; ?></td>
                        <td>₱<?php echo number_format($range_data['pamisa']['earnings'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Wedding</td>
                        <td><?php echo $range_data['wedding']['count']; ?></td>
                        <td>₱<?php echo number_format($range_data['wedding']['earnings'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Blessing</td>
                        <td><?php echo $range_data['blessings']['count']; ?></td>
                        <td>₱<?php echo number_format($range_data['blessings']['earnings'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <th><?php echo array_sum(array_column($range_data, 'count')); ?></th>
                        <th>₱<?php echo number_format($range_data['total_earnings'], 2); ?></th>
                    </tr>
                </table>
            </div>
            <center>
                <button onclick="printRangeEarnings()" style="margin-top: 10px; padding: 10px 20px; background-color: #28a745; color: white; font-weight: bold; border: none; border-radius: 5px; font-size: 14px; cursor: pointer;">
                    Print Earnings
                </button>
            </center>

            <script>
                function printRangeEarnings() {
                    var printContents = document.querySelector('.table-container').innerHTML;
                    var originalContents = document.body.innerHTML;

                    document.body.innerHTML = `
                    <html>
                        <head>
                            <title>Print Date Range Earnings</title>
                            <style>
                                body { font-family: Arial, sans-serif; text-align: center; font-size: 12px; margin-left: -264px;}
                                .table-wrapper { display: flex; justify-content: center; width: 100%; }
                                table { border-collapse: collapse; margin: 10px auto; font-size: 10px; }
                                th, td { padding: 5px; text-align: center; border: 1px solid black; }
                                h2, h3 { margin-top: 20px; font-size: 14px; }
                                .summary { margin: 20px 0; font-size: 12px; }
                            </style>
                        </head>
                        <body>
                            <h1>PARISH OF THE HOLY CROSS</h1>
                            <p>4009 Gen. T. de Leon, Valenzuela City, 1442 Metro Manila</p>
                            <h2><?php echo $overview_title; ?></h2>
                            <h3>Baptism Requests</h3>
                            <div class="table-wrapper">
                                <table>
                                    <tr>
                                        <?php
                                        $baptism_columns = ['selected_date' => 'Date', 'baptized_name' => 'Child Name', 'status' => 'Status'];
                                        foreach ($baptism_columns as $col => $label) {
                                            echo "<th>$label</th>";
                                        }
                                        ?>
                                    </tr>
                                    <?php
                                    $baptism_details->data_seek(0); // Reset result pointer
                                    while ($row = $baptism_details->fetch_assoc()): ?>
                                        <tr>
                                            <?php foreach ($baptism_columns as $col => $label): ?>
                                                <td><?php echo htmlspecialchars($row[$col] ?? 'N/A'); ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endwhile; ?>
                                </table>
                            </div>
                            <h3>Mass Requests</h3>
                            <div class="table-wrapper">
                                <table>
                                    <tr>
                                        <?php
                                        $pamisa_columns = ['selected_date' => 'Date', 'name_of_intended' => 'Intended Name', 'pamisa_type' => 'Type', 'status' => 'Status'];
                                        foreach ($pamisa_columns as $col => $label) {
                                            echo "<th>$label</th>";
                                        }
                                        ?>
                                    </tr>
                                    <?php
                                    $pamisa_details->data_seek(0); // Reset result pointer
                                    while ($row = $pamisa_details->fetch_assoc()): ?>
                                        <tr>
                                            <?php foreach ($pamisa_columns as $col => $label): ?>
                                                <td><?php echo htmlspecialchars($row[$col] ?? 'N/A'); ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endwhile; ?>
                                </table>
                            </div>
                            <h3>Wedding Requests</h3>
                            <div class="table-wrapper">
                                <table>
                                    <tr>
                                        <?php
                                        $wedding_columns = ['wedding_date' => 'Wedding Date', 'bride_name' => 'Bride Name', 'groom_name' => 'Groom Name', 'status' => 'Status'];
                                        foreach ($wedding_columns as $col => $label) {
                                            echo "<th>$label</th>";
                                        }
                                        ?>
                                    </tr>
                                    <?php
                                    $wedding_details->data_seek(0); // Reset result pointer
                                    while ($row = $wedding_details->fetch_assoc()): ?>
                                        <tr>
                                            <?php foreach ($wedding_columns as $col => $label): ?>
                                                <td><?php echo htmlspecialchars($row[$col] ?? 'N/A'); ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endwhile; ?>
                                </table>
                            </div>
                            <h3>Blessings Requests</h3>
                            <div class="table-wrapper">
                                <table>
                                    <tr>
                                        <?php
                                        $blessings_columns = ['blessing_date' => 'Date', 'name_of_blessed' => 'Blessed Name', 'type_of_blessing' => 'Type', 'status' => 'Status'];
                                        foreach ($blessings_columns as $col => $label) {
                                            echo "<th>$label</th>";
                                        }
                                        ?>
                                    </tr>
                                    <?php
                                    $blessings_details->data_seek(0); // Reset result pointer
                                    while ($row = $blessings_details->fetch_assoc()): ?>
                                        <tr>
                                            <?php foreach ($blessings_columns as $col => $label): ?>
                                                <td><?php echo htmlspecialchars($row[$col] ?? 'N/A'); ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endwhile; ?>
                                </table>
                            </div>
                            <h3>Summary of Earnings Report for <?php echo $overview_title; ?></h3>
                            <div class="table-wrapper">
                                ${printContents}
                            </div>
                        </body>
                    </html>
                `;

                    window.print();
                    document.body.innerHTML = originalContents;
                }
            </script>
        <?php endif; ?>
    </section>

    <section class="upcoming-events">
        <div class="section-container" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px; font-family: Arial, sans-serif;">
            <h3 style="margin-bottom: 10px;">Baptism Slots</h3>
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
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $row['slots_remaining'] . "/50"; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <button onclick="printTable('baptismTable')" style="padding: 8px 15px; background-color: #007bff; color: white; font-weight: bold; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; margin-bottom: 10px;">Print Baptism</button>
        </div>

        <div class="section-container" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px; font-family: Arial, sans-serif;">
            <h3 style="margin-bottom: 10px;">Mass Requests</h3>
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
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo $row['selected_date']; ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;"> <?php echo $row['name_of_intended']; ?> </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo $row['pamisa_type']; ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo $row['status']; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <button onclick="printTable('pamisaTable')" style="padding: 8px 15px; background-color: #007bff; color: white; font-weight: bold; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; margin-bottom: 10px;">
                Print Mass
            </button>
        </div>

        <div class="section-container" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-bottom: 20px; font-family: Arial, sans-serif;">
            <h3 style="margin-bottom: 10px;">
                Wedding Requests</h3>
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
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo $row['wedding_date']; ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo $row['bride_name']; ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo $row['groom_name']; ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo $row['status']; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <button onclick="printTable('weddingTable')" style="padding: 8px 15px; background-color: #007bff; color: white; font-weight: bold; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; margin-bottom: 10px;">
                Print Wedding
            </button>
        </div>

        <div class="section-container" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-bottom: 20px; font-family: Arial, sans-serif;">
            <h3 style="margin-bottom: 10px;">Blessings Requests</h3>
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
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo $row['blessing_date']; ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo $row['name_of_blessed']; ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo $row['type_of_blessing']; ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <?php echo $row['status']; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <button onclick="printTable('blessingsTable')" style="padding: 8px 15px; background-color: #007bff; color: white; font-weight: bold; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; margin-bottom: 10px;">
                Print Blessings
            </button>
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
    </section>

    <footer>
        <div class="footer-container">
            <div class="footer-about">
                <h3>About Parish of the Holy Cross</h3>
                <p>
                    The Parish of the Holy Cross is a sacred place of worship, where the community comes together to celebrate faith, hope, and love. Whether you're seeking spiritual growth, a peaceful moment of reflection, or a place to connect with others, our church provides a welcoming environment for all.
                </p>
            </div>
            <div class="footer-contact">
                <h3>Contact Us</h3>
                <p>Email: holycrossparish127@yahoo.com</p>
                <p>Phone: 28671588581</p>
                <p>Address: Gen. T. de Leon, Valenzuela, Philippines, 1442 </p>
            </div>
            <div class="footer-socials">
                <h3>Follow Us</h3>
                <a href="http://www.facebook.com/ParishoftheHolyCrossValenzuelaCityOfficial/">Facebook</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 Parish of the Holy Cross. All rights reserved.</p>
        </div>
    </footer>

    <script>
        <?php if (!empty($alertMessage)) echo ($alertMessage); ?>

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

        .overview-boxes {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .box {
            background-color: #2c3e50;
            color: white;
            border-radius: 10px;
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
            color: rgb(68, 157, 0);
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
            border: 1px solid #ddd;
        }
    </style>
</body>

</html>