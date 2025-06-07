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
</head>
<body id="bodyTag">
    <header class="header" id="header">

    </header>
    <?php include 'sidebar.php'; ?><br>
    <div class="admin-greeting">Good Day, Admin!</div>
    <div id="datetime" class="datetime"></div> 

    <section class="about-us">
    <h2>Admin Account Management</h2>
    <p class="justified">
        This section allows you to manage <b>admin account details</b>, including usernames and email addresses.  
        To update an account, select an admin from the list and confirm your password to proceed.  
        Ensure that all changes are accurate to maintain secure and efficient account management  
        within the parish system.
    </p>
</section>

    <div class="admin-container">
        <h2>Admin Account Details</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT user_id, username, email FROM user_type_church WHERE user_type = 'admin'";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['user_id']}</td>
                                <td id='username_{$row['user_id']}'>{$row['username']}</td>
                                <td id='email_{$row['user_id']}'>{$row['email']}</td>
                                <td>
                                    <button class='edit-btn' onclick=\"openEditModal({$row['user_id']})\">
                                        <i class='bx bx-edit'></i> Edit
                                    </button>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No admin accounts found.</td></tr>";
                }
                ?>
            </tbody>


        </table>
    </div>

<!-- Modal for Editing Admin Account -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Confirm Password</h3>
        
        <div id="passwordSection">
            <input type="password" id="confirmPassword" placeholder="Enter your password">
            <button onclick="verifyPassword()">Submit</button>
        </div>

        <div id="editFields" style="display: none;">
            <h3>Edit Account Details</h3>
            <input type="text" id="newUsername" placeholder="New Username">
            <input type="email" id="newEmail" placeholder="New Email">
            <button onclick="saveChanges()">Save Changes</button>
        </div>
    </div>
</div>


<script>
let selectedUserId;

function openEditModal(userId) {
    selectedUserId = userId;
    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('passwordSection').style.display = 'block';
    document.getElementById('editFields').style.display = 'none';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

function verifyPassword() {
    let password = document.getElementById("confirmPassword").value;

    $.ajax({
        url: 'verify_password.php',
        method: 'POST',
        data: { password: password },
        success: function(response) {
            if (response === 'success') {
                document.getElementById("passwordSection").style.display = 'none';
                document.getElementById("editFields").style.display = 'block';
            } else {
                Swal.fire("Error", "Incorrect password!", "error");
            }
        }
    });
}

function saveChanges() {
    let newUsername = document.getElementById("newUsername").value;
    let newEmail = document.getElementById("newEmail").value;

    if (!newUsername || !newEmail) {
        Swal.fire("Warning", "Please fill in both fields!", "warning");
        return;
    }

    $.ajax({
        url: 'update_user.php',
        method: 'POST',
        data: { user_id: selectedUserId, username: newUsername, email: newEmail },
        success: function(response) {
            console.log("AJAX Response:", response);

            if (response.trim() === 'success') {
                closeModal();
                location.reload(); // Reload the page after closing the modal
            } else if (response.trim() === 'duplicate') {
                Swal.fire("Error", "This email is already in use!", "error");
            } else {
                Swal.fire("Error", "Update failed!", "error");
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", error);
            Swal.fire("Error", "An error occurred. Please try again!", "error");
        }
    });
}


</script>



    <footer>
        <div class="footer-container">
            <div class="footer-about">
                <h3>About Parish of the Holy Cross</h3>
                <p>The Parish of the Holy Cross is a sacred place of worship, where the community comes together to celebrate faith, hope, and love.</p>
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

        .admin-container {
            width: 60%;
            margin: 40px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .admin-container h2 {
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ccc;
        }

        th {
            background: #2ecc71;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: rgb(241, 243, 240);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 20px;
            width: 350px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.3s;
        }

        .modal-content h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .modal-content input {
            width: 90%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .modal-content button {
            width: 100%;
            padding: 10px;
            background: #2ecc71;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 10px;
        }

        .modal-content button:hover {
            background: #27ae60;
        }

        .close {
            float: right;
            font-size: 24px;
            cursor: pointer;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .edit-btn {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background 0.3s ease-in-out;
        }

        .edit-btn:hover {
            background-color: #27ae60; 
        }

        .edit-btn i {
            font-size: 16px;
        }

    </style>
</body>
</html>
