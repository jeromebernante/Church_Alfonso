<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db_connection.php';

$query = "
    SELECT 'Baptism' AS type, COUNT(*) AS count FROM baptism_requests
    UNION ALL
    SELECT 'Blessings', COUNT(*) FROM blessings_requests
    UNION ALL
    SELECT 'Wedding', COUNT(*) FROM wedding_requests
    UNION ALL
    SELECT 'Pamisa', COUNT(*) FROM pamisa_requests
";

$result = $conn->query($query);
$data = [];
$totalRequests = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[$row['type']] = $row['count'];
        $totalRequests += $row['count'];
    }
} else {
    die("Query failed: " . $conn->error);
}

if (empty($data)) {
    die("No request data found.");
}

$chartData = [];
foreach ($data as $key => $value) {
    $percentage = $totalRequests > 0 ? round(($value / $totalRequests) * 100, 2) : 0;
    $chartData[] = ['label' => $key, 'count' => $value, 'percentage' => $percentage];
}

$chartDataJSON = json_encode($chartData);

$ratesQuery = "SELECT id, service_name, rate, additional_info FROM rates";
$ratesResult = $conn->query($ratesQuery);

if (!$ratesResult) {
    die("Query failed: " . $conn->error);
}

$ratesData = [];
while ($row = $ratesResult->fetch_assoc()) {
    $ratesData[] = $row;
}

if (empty($ratesData)) {
    die("No rates data found.");
}

$conn->close();
$ratesDataJSON = json_encode($ratesData);

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
        <div class="header_toggle">
            <i class='bx bx-menu' id="header-toggle"></i>
        </div>
    </header>
    <?php include 'viewer_sidebar.php'; ?><br>
    <div class="admin-greeting">Good Day, <?php echo $_SESSION['username'] ?>!</div>
    <div id="datetime" class="datetime"></div>

    <section class="about-us">
        <h2 style="color: black; font-size: 20px;">Your Dashboard</h2>
        <p class="justified">
            The Dashboard allows you to access and review detailed records of service reservations and church events.
            Stay informed and keep track of essential parish data efficiently.
        </p>
    </section>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div style="width: 60%; height: 30%; margin: auto;">
        <canvas id="requestChart"></canvas>
    </div>

    <section class="about-us">
        <h2 style="color: black; font-size: 20px; margin-top: 15px;">Service Rates</h2>
        <p class="justified">
            The Service Rates section displays the pricing for different church services, such as <b>Baptism, Wedding,
                Blessings, and Pamisa</b>. These rates help parishioners understand the associated costs before making a
            reservation.
        </p>
    </section>

    <table>
        <thead>
            <tr>
                <th>Service Type</th>
                <th>Rate (₱)</th>
                <th>Additional Info</th>
            </tr>
        </thead>
        <tbody id="ratesTableBody"></tbody>
    </table>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const chartData = <?php echo $chartDataJSON; ?>;
            const labels = chartData.map(data => `${data.label} (${data.percentage}%)`);
            const counts = chartData.map(data => data.count);

            const ctx = document.getElementById('requestChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Number of Requests',
                        data: counts,
                        backgroundColor: ['#3498db', '#e74c3c', '#2ecc71', '#f1c40f'],
                        borderColor: '#333',
                        borderWidth: 1
                    }]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });

            // Load Rates Data
            const ratesData = <?php echo $ratesDataJSON; ?>;
            const tableBody = document.getElementById("ratesTableBody");

            ratesData.forEach(rate => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${rate.service_name}</td>
                    <td>${rate.rate}</td>
                    <td>${rate.additional_info}</td>
                `;
                tableBody.appendChild(row);
            });
        });

        function updateDateTime() {
            let now = new Date();
            let options = { timeZone: 'Asia/Manila', hour12: true, weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            document.getElementById('datetime').innerHTML = new Intl.DateTimeFormat('en-PH', options).format(now);
        }

        updateDateTime();
        setInterval(updateDateTime, 60000);
    </script>










    <footer>
        <div class="footer-container">
            <div class="footer-about">
                <h3>About Parish of the Holy Cross</h3>
                <p>
                    The Parish of the Holy Cross is a sacred place of worship, where the community comes together to
                    celebrate faith, hope, and love. Whether you're seeking spiritual growth, a peaceful moment of
                    reflection, or a place to connect with others, our church provides a welcoming environment for all.
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
            let options = { timeZone: 'Asia/Manila', hour12: true, weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            document.getElementById('datetime').innerHTML = new Intl.DateTimeFormat('en-PH', options).format(now);
        }

        updateDateTime();
        setInterval(updateDateTime, 60000);

    </script>
    <style>
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

        /* Centering the table */
        table {
            width: 70%;
            /* Adjust width */
            margin: 30px auto;
            /* Centering */
            border-collapse: collapse;
            font-family: 'Poppins', sans-serif;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* Header Row Styling */
        th {
            background: #3498db;
            color: white;
            font-size: 18px;
            padding: 14px;
            text-align: center;
        }

        /* Table Body Styling */
        td {
            padding: 12px;
            text-align: center;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
        }

        /* Alternating row colors */
        tr:nth-child(even) {
            background: #f8f9fa;
        }

        tr:nth-child(odd) {
            background: #ffffff;
        }

        /* Hover effect */
        tr:hover {
            background: #e3f2fd;
            transition: 0.3s;
        }

        /* Button styling */
        button {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 6px;
            font-size: 14px;
            transition: 0.3s;
        }

        button:hover {
            background: #27ae60;
            transform: scale(1.05);
        }

        /* Editable fields style */
        td[contenteditable="true"] {
            background: #f0f8ff;
            outline: none;
            transition: 0.3s;
        }

        td[contenteditable="true"]:focus {
            background: #d9edf7;
            border: 1px solid #3498db;
        }
    </style>
</body>

</html>