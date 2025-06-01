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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/main.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/main.min.css">
    <script src="scriptd.js"></script>
    <script src="calendar.js"></script>
</head>
<body id="bodyTag">
    <header class="header" id="header">
        <div class="header_toggle">
            <i class='bx bx-menu' id="header-toggle"></i>
        </div>
    </header>
    <?php include 'sidebar.php'; ?><br>
    <div class="admin-greeting">Good Day, Admin!</div>
    <div id="datetime" class="datetime"></div> 
    <section class="about-us">
    <h2>Priest Schedule</h2>
    <p class="justified">
        This section allows you to manage the <b>priest’s availability</b> for parish events and services.  
        By default, the priest is available every day, but you can mark specific dates as unavailable when necessary.  
        Click on a date in the calendar to toggle availability, ensuring an organized and efficient scheduling process  
        for both the parish and its requestors.
    </p>
</section>


<section class="about-us">
    <table border="1" id="priest-table">
        <thead>
            <tr>
                <th>Priest Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <br>
    <div class="priest-input-container">
        <input type="text" id="new-priest" placeholder="Enter Priest Name">
        <button onclick="addPriest()">Add Priest</button>
    </div>
    <button id="prev-month">◀</button>
    <span id="month-year"></span>
    <button id="next-month">▶</button>
    <table>
        <thead>
            <tr>
                <th>Sun</th>
                <th>Mon</th>
                <th>Tue</th>
                <th>Wed</th>
                <th>Thu</th>
                <th>Fri</th>
                <th>Sat</th>
            </tr>
        </thead>
        <tbody id="calendar"></tbody>
    </table>
</section>
</div>



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

<script>
    function loadPriests() {
        fetch("schedule.php?action=getPriests")
            .then(response => response.json())
            .then(data => {
                let tableBody = document.querySelector("#priest-table tbody");
                tableBody.innerHTML = "";

                data.priests.forEach(priest => {
                    let row = `<tr>
                        <td>${priest}</td>
                        <td><button class="delete-btn" onclick="deletePriest('${priest}')">Delete</button></td>
                    </tr>`;
                    tableBody.innerHTML += row;
                });
            });
    }

    function addPriest() {
        let priestName = document.getElementById("new-priest").value;
        if (priestName.trim() === "") {
            Swal.fire("Error", "Please enter a priest's name.", "error");
            return;
        }

        fetch("schedule.php?action=addPriest", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ priest_name: priestName })
        }).then(() => {
            document.getElementById("new-priest").value = "";
            Swal.fire("Success", "Priest added successfully!", "success");
            loadPriests();
        });
    }

    function deletePriest(priestName) {
        Swal.fire({
            title: "Are you sure?",
            text: `You are about to delete ${priestName}. This action cannot be undone.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("schedule.php?action=deletePriest", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ priest_name: priestName })
                }).then(() => {
                    Swal.fire("Deleted!", "Priest has been removed.", "success");
                    loadPriests();
                });
            }
        });
    }

    loadPriests();

    </script>


<style>
        .calendar-container {
            background: white;
            max-width: 700px;
            margin: 20px auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        #prev-month, #next-month {
            background: #2c3e50;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 5px;
            margin: 10px;
            transition: 0.3s;
        }

        #prev-month:hover, #next-month:hover {
            background: #1a252f;
        }

        #month-year {
            font-size: 24px;
            font-weight: bold;
            margin: 0 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background: #2c3e50;
            color: white;
            font-size: 18px;
        }

        .calendar-day {
            font-size: 16px;
            background: #f4f4f4;
            color: #333;
            cursor: pointer;
            transition: 0.3s ease-in-out;
        }

        .calendar-day[style*="background-color: red"] {
            background: #e74c3c !important;
            color: white;
        }

        .calendar-day[style*="background-color: green"] {
            background:rgb(29, 124, 69) !important;
            color: white;
        }

        .calendar-day:hover {
            filter: brightness(90%);
            transform: scale(1.05);
        }

        button {
            margin: 10px;
            padding: 5px 10px;
            cursor: pointer;
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

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        .calendar-day {
            transition: background 0.3s;
        }

        .calendar-day:hover {
            opacity: 0.8;
        }

        button {
            background: #2c3e50;
            color: white;
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            margin: 5px;
            border-radius: 5px;
        }

        button:hover {
            background: #1a252f;
        }
        
        #priest-table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        #priest-table th {
            background: #2c3e50;
            color: white;
            padding: 12px;
            font-size: 18px;
        }

        #priest-table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        #priest-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        #priest-table tr:hover {
            background: #d6e9f8;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: 0.3s;
        }

        .delete-btn:hover {
            background: #c0392b;
        }

        .priest-input-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        #new-priest {
            width: 250px;
            padding: 10px;
            border: 2px solid #2c3e50;
            border-radius: 8px;
            font-size: 16px;
            outline: none;
            transition: 0.3s;
        }

        #new-priest:focus {
            border-color: #1abc9c;
            box-shadow: 0 0 5px rgba(26, 188, 156, 0.5);
        }

        button {
            background: #2c3e50;
            color: white;
            padding: 10px 15px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #1a252f;
        }

    </style>
</body>
</html>
