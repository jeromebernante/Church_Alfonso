<?php
session_start();
include 'db_connection.php';
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
    <style>
        .event-box,
        .blessing-box,
        .pamisa-box,
        .wedding-box {
            border: 2px solid #4CAF50;
            padding: 15px;
            margin: 10px;
            border-radius: 8px;
            cursor: pointer;
            background-color: #f9f9f9;
        }

        .event-box:hover,
        .blessing-box:hover,
        .pamisa-box:hover,
        .wedding-box:hover {
            background-color: #e0f7fa;
        }

        .details-container {
            display: none;
            padding: 10px;
            margin-top: 5px;
            border-top: 1px solid #ddd;
        }

        #baptismContainer {
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }

        #blessingContainer,
        #pamisaContainer,
        #weddingContainer {
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            margin-bottom: 20px;
        }

        #show-history {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 5px;
            transition: 0.3s ease-in-out;
        }

        #show-history:hover {
            background: #2980b9;
        }

        #show-history.active {
            background: #e74c3c;
        }

        #show-history.active:hover {
            background: #c0392b;
        }

        #history-container {
            display: none;
            background: #f4f4f4;
            padding: 15px;
            margin-top: 10px;
            border-radius: 5px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body id="bodyTag">
    <header class="header" id="header">
        <div class="header_toggle">
            <i class='bx bx-menu' id="header-toggle"></i>
        </div>
    </header>
    <?php include 'viewer_sidebar.php'; ?><br>

    <div class="admin-header"
        style="display: flex; justify-content: space-between; align-items: center; background: #f4f6f8; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 20px;">
        <div class="admin-greeting" style="font-size: 1.4em; font-weight: bold; color: #333;">
            Good Day, <?php echo $_SESSION['username']; ?>!
        </div>
        <button id="show-history"
            style="padding: 10px 18px; background-color: #4A90E2; color: white; border: none; border-radius: 8px; font-size: 1em; cursor: pointer; transition: background-color 0.3s;">
            Show History
        </button>
    </div>

    <div id="datetime" class="datetime" style="text-align: right; font-size: 0.95em; color: #666; margin-bottom: 20px;">
    </div>

    <section class="about-us"
        style="background: #ffffff; padding: 20px 24px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
        <h2 style="font-size: 1.5em; color: #1f2937; margin-bottom: 10px;">Event Management</h2>
        <p class="justified" style="font-size: 1em; color: #444; line-height: 1.6; text-align: justify;">
            You can only view the Event Management section. Here, you can monitor scheduled and upcoming events. While
            admins can review details, coordinate, and update statuses, your access is limited to viewing only.
        </p>
    </section>


    <div id="history-container"></div>

    <script>
        $(document).ready(function () {
            $('#show-history').click(function () {
                $('#history-container').slideToggle(300);
                $(this).toggleClass('active');
                $(this).text($(this).hasClass('active') ? 'Close History' : 'Show History');

                if ($(this).hasClass('active')) {
                    $.ajax({
                        url: 'fetch_history.php',
                        type: 'GET',
                        success: function (response) {
                            $('#history-container').html(response);
                        }
                    });
                }
            });
        });
    </script>
    <br>
    <h2 style="text-align: center; color: white; background-color: green; padding: 10px; border-radius: 5px;">Upcoming
        Events</h2>

    <div
        style="margin: 30px auto; background: #f9f9f9; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.06); max-width: 700px;">

        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">

            <!-- Specific Date Filter -->
            <div style="flex: 1; min-width: 180px;">
                <label for="globalSpecificDateFilter"
                    style="display: block; font-size: 1.1em; font-weight: 600; color: #4A90E2; margin-bottom: 6px;">Specific
                    Date:</label>
                <input type="date" id="globalSpecificDateFilter" oninput="applyGlobalFilter()"
                    style="width: 100%; font-size: 1em; padding: 10px 14px; border-radius: 8px; border: 1px solid #ccc; outline: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: 0.3s;">
            </div>

            <!-- Start Date Filter -->
            <div style="flex: 1; min-width: 180px;">
                <label for="globalStartDateFilter"
                    style="display: block; font-size: 1.1em; font-weight: 600; color: #4A90E2; margin-bottom: 6px;">Start
                    Date:</label>
                <input type="date" id="globalStartDateFilter" oninput="applyGlobalFilter()"
                    style="width: 100%; font-size: 1em; padding: 10px 14px; border-radius: 8px; border: 1px solid #ccc; outline: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: 0.3s;">
            </div>

            <!-- End Date Filter -->
            <div style="flex: 1; min-width: 180px;">
                <label for="globalEndDateFilter"
                    style="display: block; font-size: 1.1em; font-weight: 600; color: #4A90E2; margin-bottom: 6px;">End
                    Date:</label>
                <input type="date" id="globalEndDateFilter" oninput="applyGlobalFilter()"
                    style="width: 100%; font-size: 1em; padding: 10px 14px; border-radius: 8px; border: 1px solid #ccc; outline: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: 0.3s;">
            </div>

            <!-- Type Filter -->
            <div style="flex: 1; min-width: 180px;">
                <label for="globalTypeFilter"
                    style="display: block; font-size: 1.1em; font-weight: 600; color: #4A90E2; margin-bottom: 6px;">Type:</label>
                <select id="globalTypeFilter" onchange="applyGlobalFilter()"
                    style="width: 100%; font-size: 1em; padding: 10px 14px; border-radius: 8px; border: 1px solid #ccc; outline: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: 0.3s;">
                    <option value="">All Types</option>
                    <option value="online">Online</option>
                    <option value="walk-in">Walk-in</option>
                </select>
            </div>

        </div>
    </div>





    <!-- Baptism Events -->
    <h2 style="text-align: center;">Upcoming Baptism Events</h2>
    <div id="baptismContainer">
        <?php
        $today = date("Y-m-d");

        $query = "SELECT * FROM baptism_slots WHERE date >= ? ORDER BY date ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $date = $row['date'];
                $slotsRemaining = $row['slots_remaining'];
                echo "<div class='event-box' data-date='$date' data-type='Walk-in' onclick='fetchRequests(\"$date\", this)' style='border:1px solid #ccc; padding:15px; margin:10px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);'>";
                echo "<h3>Baptism Date: " . date("F j, Y", strtotime($date)) . "</h3>";
                echo "<p>Slots Remaining: $slotsRemaining</p>";
                echo "<div class='details-container'></div>";
                echo "</div>";
            }
        } else {
            echo "<p>No upcoming baptism slots.</p>";
        }
        ?>
    </div>

    <!-- Blessings -->
    <h2 style="text-align: center;">Upcoming Blessings</h2>
    <div id="blessingContainer">
        <?php
        $query = "
    SELECT id, name_of_requestor, blessing_date, 'Online' AS type FROM blessings_requests WHERE blessing_date >= ?
    UNION
    SELECT id, name_of_requestor, blessing_date, 'Walk-in' AS type FROM walkin_blessing WHERE blessing_date >= ?
    ORDER BY blessing_date ASC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $today, $today);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $requestId = $row['id'];
                $requestor = $row['name_of_requestor'];
                $blessingDate = $row['blessing_date'];
                $type = $row['type'];

                echo "<div class='blessing-box' data-date='$blessingDate' data-type='$type' onclick='fetchBlessingDetails(\"$requestId\", \"$type\", this)' style='border:1px solid #ccc; padding:15px; margin:10px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);'>";
                echo "<h3>Requested by: $requestor</h3>";
                echo "<p>Blessing Date: " . date("F j, Y", strtotime($blessingDate)) . "</p>";
                echo "<p><b>Type:</b> $type</p>";
                echo "<div class='details-container'></div>";
                echo "</div>";
            }
        } else {
            echo "<p>No upcoming Blessing requests.</p>";
        }
        ?>
    </div>

    <!-- Pamisa -->
    <h2 style="text-align: center;">Upcoming Pamisa Requests</h2>
    <div id="pamisaContainer">
        <?php
        $query = "
    SELECT id, name_of_requestor, selected_date, 'Online' AS type FROM pamisa_requests WHERE selected_date >= ?
    UNION
    SELECT id, name_of_requestor, selected_date, 'Walk-in' AS type FROM walkin_pamisa WHERE selected_date >= ?
    ORDER BY selected_date ASC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $today, $today);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $requestId = $row['id'];
                $requestor = $row['name_of_requestor'];
                $pamisaDate = $row['selected_date'];
                $type = $row['type'];

                echo "<div class='pamisa-box' data-date='$pamisaDate' data-type='$type' onclick='fetchPamisaDetails(\"$requestId\", \"$type\", this)' style='border:1px solid #ccc; padding:15px; margin:10px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);'>";
                echo "<h3>Requested by: $requestor</h3>";
                echo "<p>Pamisa Date: " . date("F j, Y", strtotime($pamisaDate)) . "</p>";
                echo "<p><b>Type:</b> $type</p>";
                echo "<div class='details-container'></div>";
                echo "</div>";
            }
        } else {
            echo "<p>No upcoming Pamisa requests.</p>";
        }
        ?>
    </div>

    <!-- Weddings -->
    <h2 style="text-align: center;">Upcoming Weddings</h2>
    <div id="weddingContainer">
        <?php
        $query = "
    SELECT id, bride_name, groom_name, wedding_date, contact, 'Online' AS type FROM wedding_requests WHERE wedding_date >= ?
    UNION
    SELECT id, bride_name, groom_name, wedding_date, contact, 'Walk-in' AS type FROM walkin_wedding_requests WHERE wedding_date >= ?
    ORDER BY wedding_date ASC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $today, $today);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $requestId = $row['id'];
                $brideName = $row['bride_name'];
                $groomName = $row['groom_name'];
                $weddingDate = $row['wedding_date'];
                $type = $row['type'];

                echo "<div class='wedding-box' data-date='$weddingDate' data-type='$type' onclick='fetchWeddingDetails(\"$requestId\", \"$type\", this)' style='border:1px solid #ccc; padding:15px; margin:10px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);'>";
                echo "<h3>$brideName & $groomName</h3>";
                echo "<p>Wedding Date: " . date("F j, Y", strtotime($weddingDate)) . "</p>";
                echo "<p><b>Type:</b> $type</p>";
                echo "<div class='details-container'></div>";
                echo "</div>";
            }
        } else {
            echo "<p>No upcoming Weddings.</p>";
        }
        ?>
    </div>

    <!-- JavaScript for Global Filtering -->
    <script>
        function applyGlobalFilter() {
            const specificDate = document.getElementById("globalSpecificDateFilter").value;
            const startDate = document.getElementById("globalStartDateFilter").value;
            const endDate = document.getElementById("globalEndDateFilter").value;
            const selectedType = document.getElementById("globalTypeFilter").value.toLowerCase();

            const allBoxes = document.querySelectorAll('.event-box, .blessing-box, .pamisa-box, .wedding-box');

            allBoxes.forEach(box => {
                const boxDate = box.getAttribute('data-date');
                const boxType = (box.getAttribute('data-type') || '').toLowerCase();

                let dateMatch = true;

                if (specificDate) {
                    dateMatch = boxDate === specificDate;
                } else if (startDate && endDate) {
                    dateMatch = boxDate >= startDate && boxDate <= endDate;
                } else if (startDate && !endDate) {
                    dateMatch = boxDate >= startDate;
                } else if (!startDate && endDate) {
                    dateMatch = boxDate <= endDate;
                }

                const typeMatch = !selectedType || boxType === selectedType;

                box.style.display = (dateMatch && typeMatch) ? "block" : "none";
            });
        }
    </script>


    <!-- <script>
        function filterBaptismSlots() {
            let dateFilter = document.getElementById("baptismSlotsFilter").value;
            let eventBoxes = document.querySelectorAll("#baptismContainer .event-box");
            eventBoxes.forEach(box => {
                let eventDate = box.getAttribute("data-date");
                if (dateFilter && eventDate !== dateFilter) {
                    box.style.display = "none";
                } else {
                    box.style.display = "block";
                }
            });
        }

        function filterBlessings() {
            let typeFilter = document.getElementById("blessingTypeFilter").value;
            let blessingBoxes = document.querySelectorAll("#blessingContainer .blessing-box");
            blessingBoxes.forEach(box => {
                let type = box.getAttribute("data-type");
                if (typeFilter && type !== typeFilter) {
                    box.style.display = "none";
                } else {
                    box.style.display = "block";
                }
            });
        }

        function filterPamisa() {
            let typeFilter = document.getElementById("pamisaTypeFilter").value;
            let pamisaBoxes = document.querySelectorAll("#pamisaContainer .pamisa-box");
            pamisaBoxes.forEach(box => {
                let type = box.getAttribute("data-type");
                if (typeFilter && type !== typeFilter) {
                    box.style.display = "none";
                } else {
                    box.style.display = "block";
                }
            });
        }

        function filterWeddings() {
            let typeFilter = document.getElementById("weddingTypeFilter").value;
            let weddingBoxes = document.querySelectorAll("#weddingContainer .wedding-box");
            weddingBoxes.forEach(box => {
                let type = box.getAttribute("data-type");
                if (typeFilter && type !== typeFilter) {
                    box.style.display = "none";
                } else {
                    box.style.display = "block";
                }
            });
        }
    </script> -->

    <style>
        /* Unified filter styling */
        #globalDateFilter,
        #globalTypeFilter {
            font-size: 1rem;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #ccc;
            width: 220px;
            margin: 10px 5px;
            transition: border 0.3s;
        }

        #globalDateFilter:focus,
        #globalTypeFilter:focus {
            border-color: #4A90E2;
            outline: none;
        }

        /* Event container */
        #baptismContainer,
        #blessingContainer,
        #pamisaContainer,
        #weddingContainer {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
        }

        /* Card style */
        .event-box,
        .blessing-box,
        .pamisa-box,
        .wedding-box {
            width: 320px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.07);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border-left: 6px solid #4A90E2;
        }

        .event-box:hover,
        .blessing-box:hover,
        .pamisa-box:hover,
        .wedding-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        /* Typography */
        h2 {
            font-size: 1.6rem;
            margin-bottom: 10px;
            color: #1f2937;
        }

        h3 {
            font-size: 1.2rem;
            margin-bottom: 8px;
            color: #333;
        }

        p {
            font-size: 0.95rem;
            color: #555;
            margin: 4px 0;
        }

        /* Optional: Details container (for expanding info later) */
        .details-container {
            margin-top: 10px;
            display: none;
            font-size: 0.9rem;
            color: #333;
        }

        /* Section titles */
        section-title {
            text-align: center;
            font-size: 1.5rem;
            margin: 30px 0 15px;
            color: #1f2937;
            border-bottom: 2px solid #4A90E2;
            display: inline-block;
            padding-bottom: 5px;
        }
    </style>

    <script>
        function fetchRequests(date, element) {
            var detailsContainer = $(element).find(".details-container");

            if (detailsContainer.is(":visible")) {
                detailsContainer.slideUp();
                return;
            }

            $.ajax({
                url: "fetch_requests.php",
                type: "POST",
                data: { date: date },
                success: function (response) {
                    detailsContainer.html(response).slideDown();
                }
            });
        }

        function fetchBlessingDetails(requestId, type, element) {
            var detailsContainer = $(element).find(".details-container");

            if (detailsContainer.is(":visible")) {
                detailsContainer.slideUp();
                return;
            }

            $.ajax({
                url: "fetch_blessing_details.php",
                type: "POST",
                data: { requestId: requestId, type: type },
                success: function (response) {
                    detailsContainer.html(response).slideDown();
                }
            });
        }

        function fetchPamisaDetails(requestId, type, element) {
            var detailsContainer = $(element).find(".details-container");

            if (detailsContainer.is(":visible")) {
                detailsContainer.slideUp();
                return;
            }

            $.ajax({
                url: "fetch_pamisa_details.php",
                type: "POST",
                data: { requestId: requestId, type: type },
                success: function (response) {
                    detailsContainer.html(response).slideDown();
                }
            });
        }
        function fetchWeddingDetails(requestId, type, element) {
            var detailsContainer = $(element).find(".details-container");

            if (detailsContainer.is(":visible")) {
                detailsContainer.slideUp();
                return;
            }

            $.ajax({
                url: "fetch_wedding_details.php",
                type: "POST",
                data: { requestId: requestId, type: type },
                success: function (response) {
                    detailsContainer.html(response).slideDown();
                }
            });
        }
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

        .baptism-box {
            border: 2px solid #4CAF50;
            padding: 15px;
            margin: 10px;
            border-radius: 8px;
            cursor: pointer;
            background-color: #f9f9f9;
        }

        .baptism-box:hover {
            background-color: #e0f7fa;
        }

        .details-container {
            display: none;
            padding: 10px;
            margin-top: 5px;
            border-top: 1px solid #ddd;
        }
    </style>
</body>

</html>