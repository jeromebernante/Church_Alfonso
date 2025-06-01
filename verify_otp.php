<?php
session_start();
$conn = new mysqli("localhost", "root", "", "phcsss_web");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['email'];
    $otp = $_POST['otp'];

    $stmt = $conn->prepare("SELECT otp, otp_expiration FROM users WHERE email = ? AND otp = ?");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && strtotime($result['otp_expiration']) > time()) {
        $stmt = $conn->prepare("UPDATE users SET otp = NULL, otp_expiration = NULL WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Success!',
                        text: 'OTP verified successfully! You can now log in.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                });
              </script>";
    } else {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Invalid or expired OTP.',
                        icon: 'error',
                        confirmButtonText: 'Try Again'
                    });
                });
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: #121212;
            color: white;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .otp-container {
            background: rgba(185, 250, 65, 0.3);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 4px 15px rgba(185, 250, 65, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .otp-container h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .otp-container label {
            font-size: 18px;
            display: block;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #fff, #b9fa41);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: bold;
            text-transform: uppercase;
        }

        .otp-container input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 18px;
            text-align: center;
        }

        .otp-container button {
            margin-top: 20px;
            background: rgba(185, 250, 65, 0.3);
            color: white;
            font-size: 18px;
            font-weight: bold;
            padding: 15px 35px;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.3s ease-in-out;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            cursor: pointer;
        }

        .otp-container button:hover {
            background: rgba(185, 250, 65, 0.5);
            transform: scale(1.05);
        }
    </style>
</head>
<body>

    <div class="otp-container">
        <h2>Verify Your OTP</h2>
        <form method="POST">
            <label for="otp">Enter OTP</label>
            <input type="text" name="otp" required>
            <button type="submit">Verify OTP</button>
        </form>
    </div>

</body>
</html>
