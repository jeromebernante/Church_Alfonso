
    <?php
    session_start();
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
    require 'PHPMailer/src/Exception.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    $conn = new mysqli("localhost", "root", "", "phcsss_web");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        
        $otp = rand(100000, 999999);
        $otp_expiration = date("Y-m-d H:i:s", strtotime("+5 minutes"));
    
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, address, password, otp, otp_expiration) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $phone, $address, $password, $otp, $otp_expiration);
    
        if ($stmt->execute()) {
            sendOTP($email, $otp);
            $_SESSION['email'] = $email;
            header("Location: verify_otp.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    }

    
    function sendOTP($email, $otp) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true;
            $mail->Username = 'santospreciousdesiree@gmail.com'; 
            $mail->Password = 'qznt kgon wykf elbm'; 
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
    
            $mail->setFrom('santospreciousdesiree@gmail.com', 'Parish of the Holy Cross');
            $mail->addAddress($email);
            $mail->Subject = 'Your OTP Code';
            
            $mail->isHTML(true);
            $mail->Body = "<div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px; max-width: 400px; margin: auto; background: #f9f9f9;'>
                <h2 style='text-align: center; color: #333;'>🔒 Secure OTP Code</h2>
                <p style='text-align: center; font-size: 18px; color: #555;'>Use the code below to verify your identity:</p>
                <div style='text-align: center; font-size: 24px; font-weight: bold; color: #007BFF; padding: 10px; border: 2px dashed #007BFF; border-radius: 5px; display: inline-block;'>$otp</div>
                <p style='text-align: center; font-size: 14px; color: #777;'>This code will expire in <strong>5 minutes</strong>. Please do not share it with anyone.</p>
                <p style='text-align: center; font-size: 14px; color: #777;'>If you did not request this, please ignore this email.</p>
                <hr style='border: 0; height: 1px; background: #ddd;'>
                <p style='text-align: center; font-size: 12px; color: #aaa;'>Parish of the Holy Cross Team</p>
            </div>";
    
            $mail->send();
        } catch (Exception $e) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        }
    }
    