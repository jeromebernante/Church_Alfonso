<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$baptismQuery = "SELECT * FROM baptism_slots ORDER BY date ASC";
$baptismResult = $conn->query($baptismQuery);

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

    </header>
    <?php include 'sidebar.php'; ?><br>
    <div class="admin-greeting">Good Day, Admin!</div>
    <div id="datetime" class="datetime"></div> 

    <section class="about-us">
        <h2 style="color: black; font-size: 20px;">Your Dashboard</h2>
        <p class="justified">
            The Dashboard allows you to access and review detailed records of service reservations, church events, and other administrative activities. Stay informed and keep track of essential parish data efficiently.
        </p>
    </section>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div style="width: 60%; height: 30%; margin: auto;">
        <canvas id="requestChart"></canvas>
    </div>

    <section class="about-us">
    <h2 style="color: black; font-size: 20px; margin-top: 15px;">Service Rates Management</h3>
    <p class="justified">
        The Service Rates section displays the pricing for different church services, such as <b>Baptism, Wedding, Blessings, and Mass</b>. These rates help parishioners understand the associated costs before making a reservation. You can modify the rates and additional information directly in the table by clicking on the respective fields.

    </p>

</section>
<table>
    <thead>
        <tr>
            <th>Service Type</th>
            <th>Rate (₱)</th>
            <th>Additional Info</th>
            <th>Action</th>
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
            const ratesData = <?php echo $ratesDataJSON; ?>;
            const tableBody = document.getElementById("ratesTableBody");

            ratesData.forEach(rate => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${rate.service_name}</td>
                    <td contenteditable="true" data-id="${rate.id}" data-field="rate">${rate.rate}</td>
                    <td contenteditable="true" data-id="${rate.id}" data-field="additional_info">${rate.additional_info}</td>
                    <td><button onclick="saveRate(${rate.id})">Save</button></td>
                `;
                tableBody.appendChild(row);
            });
        });

        function saveRate(id) {
            const rateCell = document.querySelector(`[data-id='${id}'][data-field='rate']`);
            const infoCell = document.querySelector(`[data-id='${id}'][data-field='additional_info']`);

            const updatedRate = rateCell.innerText;
            const updatedInfo = infoCell.innerText;

            $.ajax({
                url: 'update_rates.php',
                type: 'POST',
                data: { id: id, rate: updatedRate, additional_info: updatedInfo },
                success: function (response) {
                    Swal.fire({ title: "Success!", text: "Rate updated successfully.", icon: "success", confirmButtonText: "OK" });
                },
                error: function () {
                    Swal.fire({ title: "Error!", text: "Failed to update rate.", icon: "error", confirmButtonText: "Try Again" });
                }
            });
        }

        function updateDateTime() {
            let now = new Date();
            let options = { timeZone: 'Asia/Manila', hour12: true, weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            document.getElementById('datetime').innerHTML = new Intl.DateTimeFormat('en-PH', options).format(now);
        }

        updateDateTime();
        setInterval(updateDateTime, 60000);
    </script>

<center><h2>Add Baptism Slots</h2></center>

<table class="scrollable-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Slots Remaining</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $baptismResult->fetch_assoc()) : ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['date']; ?></td>
                <td><?php echo $row['slots_remaining']; ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<style>
    .scrollable-table {
        width: 25%;
        margin: 20px auto;
        border-collapse: collapse;
        display: block;
        overflow-y: auto;
        max-height: 300px; 
    }

    .scrollable-table thead {
        display: table;
        width: 100%;
        background: #3498db;
        color: white;
        position: sticky;
        top: 0;
    }

    .scrollable-table tbody {
        display: block;
        width: 100%;
        max-height: 300px;
        overflow-y: auto;
    }

    .scrollable-table th, .scrollable-table td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #ddd;
        width: 33.33%; 
    }

    .scrollable-table tbody tr:nth-child(even) {
        background: #f8f9fa;
    }

    .scrollable-table tbody tr:hover {
        background: #e3f2fd;
    }
</style>


<center>
    <form id="baptismForm" class="baptism-form">
        <label for="baptismDate">📅 Select Date:</label>
        <input type="date" id="baptismDate" name="baptismDate" required>
        <button type="button" onclick="addBaptismDate()">➕ Add Date</button>
    </form>
    <div id="message"></div>
</center>

<style>
    .baptism-form {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.2);
        display: inline-block;
        text-align: center;
        margin-top: 20px;
    }

    .baptism-form label {
        font-size: 18px;
        font-weight: bold;
        margin-right: 10px;
        color: #333;
    }

    .baptism-form input {
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 6px;
        outline: none;
        transition: 0.3s;
    }

    .baptism-form input:focus {
        border-color: #3498db;
        box-shadow: 0px 0px 8px rgba(52, 152, 219, 0.5);
    }

    .baptism-form button {
        background: #2ecc71;
        color: white;
        border: none;
        padding: 10px 15px;
        font-size: 16px;
        border-radius: 6px;
        cursor: pointer;
        transition: 0.3s;
        margin-left: 10px;
    }

    .baptism-form button:hover {
        background: #27ae60;
        transform: scale(1.05);
    }

    #message {
        margin-top: 15px;
        font-size: 16px;
        font-weight: bold;
        color: #d63031;
    }
</style>


<script>
function addBaptismDate() {
    let baptismDate = document.getElementById("baptismDate").value;

    if (!baptismDate) {
        alert("Please select a date.");
        return;
    }

    $.ajax({
        url: 'add_baptism_slot.php',
        type: 'POST',
        data: { date: baptismDate },
        success: function(response) {
            Swal.fire({ title: "Success!", text: response, icon: "success", confirmButtonText: "OK" })
                .then(() => location.reload());
        },
        error: function() {
            Swal.fire({ title: "Error!", text: "Failed to add date.", icon: "error", confirmButtonText: "Try Again" });
        }
    });
}
</script>




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
<?php if (!empty($alertMessage)) echo $alertMessage; ?>

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
    width: 70%; /* Adjust width */
    margin: 30px auto; /* Centering */
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
