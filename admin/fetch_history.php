<?php
include 'db_connection.php';
?>

<!-- Baptism Slots -->
<h2 style="text-align: center; color: white; background-color: red; padding: 10px; border-radius: 5px;">Past Events</h2>
<h2 style="text-align: center;">Past Baptism Requests</h2>
<div class="baptism-container">
    <center>
        <label for="baptismSlotsFilter" class="filter-label">Filter by Date:</label>
        <input type="date" id="baptismSlotsFilter" oninput="filterBaptismSlots()" class="filter-input">
    </center>

    <div id="baptismContainer">
        <?php
        $today = date("Y-m-d");

        $query = "SELECT * FROM baptism_slots WHERE date < ? ORDER BY date DESC";
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
            echo "<p class='no-events'>No past baptism slots.</p>";
        }
        ?>
    </div>
</div>

<!-- Blessings Requests (Past) -->

<h2 style="text-align: center;">Past Blessing Requests</h2>
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
        SELECT id, name_of_requestor, blessing_date, 'Online' AS type FROM blessings_requests WHERE blessing_date < ?
        UNION
        SELECT id, name_of_requestor, blessing_date, 'Walk-in' AS type FROM walkin_blessing WHERE blessing_date < ?
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
<h2 style="text-align: center;">Past Pamisa Requests</h2>
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
    SELECT id, name_of_requestor, selected_date, 'Online' AS type FROM pamisa_requests WHERE selected_date < ?
    UNION
    SELECT id, name_of_requestor, selected_date, 'Walk-in' AS type FROM walkin_pamisa WHERE selected_date < ?
    ORDER BY selected_date DESC";

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
        echo "<p>No past Mass requests.</p>";
    }
    ?>
</div>
<!-- Weddings Requests (Online & Walk-in) -->
<h2 style="text-align: center;">Past Wedding Requests</h2>
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
        SELECT id, bride_name, groom_name, wedding_date, contact, 'Online' AS type FROM wedding_requests WHERE wedding_date <  ?
        UNION
        SELECT id, bride_name, groom_name, wedding_date, contact, 'Walk-in' AS type FROM walkin_wedding_requests WHERE wedding_date <  ?
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
        echo "<p>No past Weddings.</p>";
    }
    ?>
</div>

<style>

    .baptism-container {
        background-color: #f0f0f0;
    }

    .filter-label {
        font-size: 1.2em;
        font-weight: bold;
        color: #4A90E2;
        margin-bottom: 10px;
    }

    .filter-input {
        font-size: 1em;
        padding: 8px 12px;
        border-radius: 5px;
        border: 1px solid #ccc;
        outline: none;
        width: 200px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: border-color 0.3s, box-shadow 0.3s;
    }
</style>

<script>
    function fetchBlessingDetails1(requestId, type, element) {
    let detailsContainer = element.querySelector(".details-container");

    if (detailsContainer.innerHTML.trim() !== "") {
        detailsContainer.innerHTML = "";
        return;
    }

    $.ajax({
        url: "fetch_blessing_details1.php", 
        type: "POST",
        data: { requestId: requestId, type: type },
        success: function(response) {
            detailsContainer.innerHTML = response;
        },
        error: function() {
            detailsContainer.innerHTML = "<p style='color: red;'>Failed to load details.</p>";
        }
    });
}

</script>