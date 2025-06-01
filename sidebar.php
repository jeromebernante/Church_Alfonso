<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            height: 100vh;
            background: linear-gradient(to right, #4CAF50, #2E7D32);
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: rgba(46, 125, 50, 0.85);
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.2);
            transition: 0.3s;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 15px 20px;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease-in-out;
        }

        .sidebar ul li a i {
            margin-right: 12px;
            font-size: 22px;
        }

        .sidebar ul li:hover {
            background: rgba(27, 94, 32, 0.7);
            border-left: 5px solid #B9FA41;
            transition: all 0.3s ease-in-out;
        }

        /* Header */
        .header {
            background: rgba(51, 51, 51, 0.9);
            color: white;
            padding: 20px;
            text-align: center;
            width: calc(100% - 250px);
            margin-left: 250px;
            position: fixed;
            top: 0;
            z-index: 1000;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* Page Content */
        .page-container {
            margin-left: 250px;
            margin-top: 80px;
            padding: 30px;
            color: white;
        }

        /* Responsive Sidebar */
        @media screen and (max-width: 768px) {
            .sidebar {
                width: 60px;
                align-items: center;
            }

            .sidebar ul li a {
                font-size: 0;
            }

            .sidebar ul li a i {
                margin-right: 0;
                font-size: 24px;
            }

            .header, .page-container {
                margin-left: 60px;
                width: calc(100% - 60px);
            }
        }

    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <ul>
            <li><a href="index.php"><i class="fas fa-home"></i> <span>Home</span></a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> <span>Profile</span></a></li>
            <li><a href="services.php"><i class="fas fa-concierge-bell"></i> <span>Services</span></a></li>
            <li><a href="events.php"><i class="fas fa-calendar-alt"></i> <span>Events</span></a></li>
            <li><a href="contact.php"><i class="fas fa-envelope"></i> <span>Contact</span></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </div>


</body>
</html>
