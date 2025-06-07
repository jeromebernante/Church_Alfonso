<?php
session_start();
$conn = new mysqli("localhost", "root", "", "phcsss_web");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, user_id, username, password, email, user_type FROM user_type_church WHERE username = ? AND user_type = 'viewer'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $user = $result->fetch_assoc()) {
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];

            // Determine redirect target
            $specialUsers = [
                "Rev. Fr. Apolinario Roxas, Jr.",
                "Rev. Fr. Roel Aldwin C. Valmadrid"
            ];

            $redirectPage = in_array($user['username'], $specialUsers)
                ? 'dashboard_priest.php'
                : 'dashboard_viewer.php';

            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'Welcome, " . $user['username'] . "!',
                            text: 'Login successful! Your Viewer ID: " . $user['user_id'] . "',
                            icon: 'success',
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '$redirectPage';
                        });
                    });
                  </script>";
        } else {
            showLoginError();
        }
    } else {
        showLoginError();
    }
}

function showLoginError() {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Invalid username or password.',
                    icon: 'error',
                    confirmButtonText: 'Try Again'
                }).then(() => {
                    window.location.href = 'index.php';
                });
            });
          </script>";
}
?>
