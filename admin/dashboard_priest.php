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
        .event-box, .blessing-box, .pamisa-box, .wedding-box {
            border: 2px solid #4CAF50;
            padding: 15px;
            margin: 10px;
            border-radius: 8px;
            cursor: pointer;
            background-color: #f9f9f9;
        }
        .event-box:hover, .blessing-box:hover, .pamisa-box:hover, .wedding-box:hover {
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

        #blessingContainer, #pamisaContainer, #weddingContainer {
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

    </header>
    <?php include 'priest_sidebar.php'; ?><br>

    <div class="admin-header">
        <div class="admin-greeting">Good Day, Priest!</div>
        <button id="show-history">Show History</button>
    </div>

    <div id="datetime" class="datetime"></div> 

    <section class="about-us">
        <h2>Event Management</h2>
        <p class="justified">
        You can only view the Event Management section. Here, you can monitor scheduled and upcoming events. While admins can review details, coordinate, and update statuses, your access is limited to viewing only.
        </p>
    </section>

    <div id="history-container"></div>

    <script>
        $(document).ready(function() {
            $('#show-history').click(function() {
                $('#history-container').slideToggle(300);
                $(this).toggleClass('active');
                $(this).text($(this).hasClass('active') ? 'Close History' : 'Show History');
                
                if ($(this).hasClass('active')) {
                    $.ajax({
                        url: 'fetch_history.php',
                        type: 'GET',
                        success: function(response) {
                            $('#history-container').html(response);
                        }
                    });
                }
            });
        });
    </script>
<br>
<h2 style="text-align: center; color: white; background-color: green; padding: 10px; border-radius: 5px;">Upcoming Events</h2>

<!-- Baptism Slots -->
<h2 style="text-align: center;">Upcoming Baptism Events</h2>
<div>
    <!-- Filter Form for Baptism -->
    <center><label for="baptismSlotsFilter" style="font-size: 1.2em; font-weight: bold; color: #4A90E2; margin-bottom: 10px;">Filter by Date:</label></center>
    <center><input type="date" id="baptismSlotsFilter" oninput="filterBaptismSlots()" style="font-size: 1em; padding: 8px 12px; border-radius: 5px; border: 1px solid #ccc; outline: none; width: 200px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: border-color 0.3s, box-shadow 0.3s;"></center>
</div>

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
            echo "<div class='event-box' data-date='$date' onclick='fetchRequests(\"$date\", this)'>";
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

<!-- Blessings Requests (Online & Walk-in) -->
<h2 style="text-align: center;">Upcoming Blessings</h2>
<div>
    <!-- Filter Form for Blessings -->
    <center><label for="blessingTypeFilter" style="font-size: 1.2em; font-weight: bold; color: #4A90E2; margin-bottom: 10px;">Filter by Type:</label></center>
    <center><select id="blessingTypeFilter" onchange="filterBlessings()" style="font-size: 1em; padding: 8px 12px; border-radius: 5px; border: 1px solid #ccc; outline: none; width: 220px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: border-color 0.3s, box-shadow 0.3s;">
        <option value="">All Types</option>
        <option value="Online">Online</option>
        <option value="Walk-in">Walk-in</option>
    </select></center>
</div>

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
            $blessingDate = date("F j, Y", strtotime($row['blessing_date']));
            $type = $row['type'];

            echo "<div class='blessing-box' data-type='$type' onclick='fetchBlessingDetails(\"$requestId\", \"$type\", this)'>";
            echo "<h3>Requested by: $requestor</h3>";
            echo "<p>Blessing Date: $blessingDate</p>";
            echo "<p><b>Type:</b> $type</p>";
            echo "<div class='details-container'></div>";
            echo "</div>";
        }
    } else {
        echo "<p>No upcoming Blessing requests.</p>";
    }
    ?>
</div>

<!-- Pamisa Requests (Online & Walk-in) -->
<h2 style="text-align: center;">Upcoming Pamisa Requests</h2>
<div>
    <!-- Filter Form for Pamisa -->
    <center><label for="pamisaTypeFilter" style="font-size: 1.2em; font-weight: bold; color: #4A90E2; margin-bottom: 10px;">Filter by Type:</label></center>
    <center><select id="pamisaTypeFilter" onchange="filterPamisa()" style="font-size: 1em; padding: 8px 12px; border-radius: 5px; border: 1px solid #ccc; outline: none; width: 220px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: border-color 0.3s, box-shadow 0.3s;">
        <option value="">All Types</option>
        <option value="Online">Online</option>
        <option value="Walk-in">Walk-in</option>
    </select></center>
</div>

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
            $pamisaDate = date("F j, Y", strtotime($row['selected_date']));
            $type = $row['type'];

            echo "<div class='pamisa-box' data-type='$type' onclick='fetchPamisaDetails(\"$requestId\", \"$type\", this)'>";
            echo "<h3>Requested by: $requestor</h3>";
            echo "<p>Mass Date: $pamisaDate</p>";
            echo "<p><b>Type:</b> $type</p>";
            echo "<div class='details-container'></div>";
            echo "</div>";
        }
    } else {
        echo "<p>No upcoming Mass requests.</p>";
    }
    ?>
</div>

<!-- Weddings Requests (Online & Walk-in) -->
<h2 style="text-align: center;">Upcoming Weddings</h2>
<div>
    <!-- Filter Form for Weddings -->
    <center><label for="weddingTypeFilter" style="font-size: 1.2em; font-weight: bold; color: #4A90E2; margin-bottom: 10px;">Filter by Type:</label></center>
    <center><select id="weddingTypeFilter" onchange="filterWeddings()" style="font-size: 1em; padding: 8px 12px; border-radius: 5px; border: 1px solid #ccc; outline: none; width: 220px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: border-color 0.3s, box-shadow 0.3s;">
        <option value="">All Types</option>
        <option value="Online">Online</option>
        <option value="Walk-in">Walk-in</option>
    </select></center>
</div>

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
            $weddingDate = date("F j, Y", strtotime($row['wedding_date']));
            $type = $row['type'];

            echo "<div class='wedding-box' data-type='$type' onclick='fetchWeddingDetails(\"$requestId\", \"$type\", this)'>";
            echo "<h3>$brideName & $groomName</h3>";
            echo "<p>Wedding Date: $weddingDate</p>";
            echo "<p><b>Type:</b> $type</p>";
            echo "<div class='details-container'></div>";
            echo "</div>";
        }
    } else {
        echo "<p>No upcoming Weddings.</p>";
    }
    ?>
</div>

<script>
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
</script>


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
                success: function(response) {
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
                success: function(response) {
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
                success: function(response) {
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
            success: function(response) {
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
