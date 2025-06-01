<?php
session_start();
$conn = new mysqli("localhost", "root", "", "phcsss_web");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, user_id, username, password, email, user_type FROM user_type_church WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && $password === $result['password']) { 
        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['username'] = $result['username'];
        $_SESSION['email'] = $result['email'];
        $_SESSION['user_type'] = $result['user_type']; 

        $redirect_page = ($result['user_type'] == 'admin') ? 'dashboard_admin.php' : 'dashboard_viewer.php';

        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Welcome!',
                        text: 'Login successful!',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '$redirect_page';
                    });
                });
              </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Invalid username or password.',
                        icon: 'error',
                        confirmButtonText: 'Try Again'
                    }).then(() => {
                        window.location.href = '../index.php';
                    });
                });
              </script>";
    }
}
?>
