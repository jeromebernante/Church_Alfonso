<?php
session_start();
include 'db_connection.php';

function generatePassword($length = 8) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($characters), 0, $length);
}

$alertMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = generatePassword(); 
    $user_type = "viewer"; 
    $user_id = uniqid("viewer_");

    $plainPassword = $password;

    $checkEmail = $conn->prepare("SELECT * FROM user_type_church WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();
    if ($result->num_rows > 0) {
        $alertMessage = "Swal.fire({
                            title: 'Error!',
                            text: 'Email already exists!',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });";
    } else {
        $stmt = $conn->prepare("INSERT INTO user_type_church (user_id, username, password, email, user_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $user_id, $username, $plainPassword, $email, $user_type);
    
        if ($stmt->execute()) {
            $alertMessage = "Swal.fire({
                                title: 'Success!',
                                text: 'Viewer account created successfully! Password: $password',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = window.location.pathname;
                            });";
        } else {
            $alertMessage = "Swal.fire({
                                title: 'Error!',
                                text: 'Failed to create account.',
                                icon: 'error',
                                confirmButtonText: 'Try Again'
                            });";
        }
    }
}    
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
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.js"></script>
    <script src="scriptd.js"></script>
</head>
<body id="bodyTag">
<?php 
    if (isset($_SESSION['alertMessage'])) {
        echo $_SESSION['alertMessage']; 
        unset($_SESSION['alertMessage']); // Clear after displaying
    }
    ?>
    <header class="header" id="header">
        <div class="header_toggle">
            <i class='bx bx-menu' id="header-toggle"></i>
        </div>
    </header><br>       
    <div class="admin-greeting">Good Day, Admin!</div>
    <center><div id="datetime" class="datetime"></div> </center>
    <?php include 'sidebar.php'; ?>
    <section class="about-us">
    <h2>Account Creation</h2>
    <p class="justified">
            This section allows you to create new <b>Viewer</b> accounts for staff members or users who need limited access to parish data.  
            Simply enter a <b>username and email</b>, and a <b>random password</b> will be generated automatically.  
        </p>
</section>
    <div class="form-container">
        <h2>Create Viewer Account</h2>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Enter Username" required>
            <input type="email" name="email" placeholder="Enter Email" required>
            <button type="submit">Create Account</button>
        </form>
    </div>
    <script>
        <?php if (!empty($alertMessage)) echo $alertMessage; ?>
    </script>

<div class="table-container">
    <h2>Viewer Accounts</h2>
    
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Password</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT user_id, username, email, password FROM user_type_church WHERE user_type != 'admin'";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['user_id']}</td>
                            <td>{$row['username']}</td>
                            <td>{$row['email']}</td>
                            <td>
                                <input type='password' class='password-field' value='{$row['password']}' readonly>
                                <button class='toggle-btn' onclick='togglePassword(this)'>Show</button>
                            </td>
                            <td>
                                <button class='delete-btn' onclick=\"deleteAccount('{$row['user_id']}')\">Delete</button>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No viewer accounts found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>


<script>
<?php if (!empty($alertMessage)) echo $alertMessage; ?>

function updateDateTime() {
    let now = new Date();
    let options = { timeZone: 'Asia/Manila', hour12: true, weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    document.getElementById('datetime').innerHTML = new Intl.DateTimeFormat('en-PH', options).format(now);
}

updateDateTime();
setInterval(updateDateTime, 60000); 

    function deleteAccount(userId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `delete_viewer.php?user_id=${userId}`;
            }
        });
    }

    function togglePassword(button) {
        let passwordField = button.previousElementSibling;
        if (passwordField.type === "password") {
            passwordField.type = "text";
            button.textContent = "Hide";
        } else {
            passwordField.type = "password";
            button.textContent = "Show";
        }
    }
</script>

<style>


.header {
    background: #2c3e50;
    color: white;
}

.admin-greeting {
    text-align: center;
    font-size: 35px;
    font-weight: bold;
    color: #2ecc71; /
    margin-top: 70px;
}

.form-container {
    width: 50%;
    background: #34495e;
    color: white;
    padding: 30px;
    margin: 50px auto;
    border-radius: 12px;
    box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.15);
    text-align: center;
}

.form-container h2 {
    color: #2ecc71;
    margin-bottom: 20px;
    font-size: 26px;
}

.form-container input {
    width: 100%;
    padding: 12px;
    margin: 12px 0;
    border: 2px solid #2ecc71;
    border-radius: 8px;
    font-size: 16px;
    background: #2c3e50;
    color: white;
}

.form-container input:focus {
    border-color: #27ae60;
    outline: none;
    box-shadow: 0px 0px 6px rgba(39, 174, 96, 0.5);
}

.form-container button {
    background: #27ae60;
    color: white;
    padding: 14px;
    font-size: 18px;
    border: none;
    width: 100%;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-container button:hover {
    background: #2ecc71;
    box-shadow: 0px 3px 8px rgba(46, 204, 113, 0.4);
}

/* TABLE STYLING */
.table-container {
    width: 90%;
    margin: 40px auto;
    text-align: center;
}

.table-container h2 {
    color: #2ecc71;
    font-size: 24px;
    margin-bottom: 15px;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #34495e;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
    color: white;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #2c3e50;
}

th {
    background: #2ecc71;
    color: white;
}

tr:nth-child(even) {
    background: #2c3e50;
}

/* BUTTONS */
.toggle-btn, .delete-btn {
    padding: 8px 12px;
    border-radius: 5px;
    font-size: 14px;
    transition: 0.3s;
    cursor: pointer;
    border: none;
}

.toggle-btn {
    background: #2ecc71;
    color: white;
}

.toggle-btn:hover {
    background: #27ae60;
}

.delete-btn {
    background: #e74c3c;
    color: white;
}

.delete-btn:hover {
    background: #c0392b;
}


    </style>
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
</body>
</html>
