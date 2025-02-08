<?php
session_start();
require 'db_connection.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$otpError = '';
$passwordChangeSuccess = '';
$passwordMismatchError = '';

$email = isset($_GET['email']) ? trim($_GET['email']) : (isset($_POST['email']) ? trim($_POST['email']) : '');

function sendOtpEmail($email, $otp)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tessnarval11@gmail.com'; 
        $mail->Password = 'vlkc srgz zdrw llbd'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('tessnarval11@gmail.com', 'BFP Minglanilla');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Password Reset';
        $mail->Body = "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Your OTP Code</title>
            <style>
                body {
                    font-family: 'Poppins', sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f5f7;
                    color: #333;
                }
                .email-container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: #ffffff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                }
                .email-header {
                    background: linear-gradient(90deg, #d32f2f, #e53935);
                    color: #ffffff;
                    padding: 20px;
                    text-align: center;
                }
                .email-header img {
                    width: 60px;
                    margin-bottom: 10px;
                }
                .email-header h1 {
                    font-size: 22px;
                    margin: 0;
                    font-weight: 600;
                }
                .email-body {
                    padding: 20px 30px;
                }
                .email-body h2 {
                    color: #d32f2f;
                    font-size: 18px;
                    margin-bottom: 10px;
                }
                .otp-box {
                    display: inline-block;
                    font-size: 28px;
                    font-weight: bold;
                    color: #ffffff;
                    background: linear-gradient(90deg, #d32f2f, #e53935);
                    padding: 10px 20px;
                    border-radius: 6px;
                    letter-spacing: 2px;
                    margin: 15px 0;
                }
                .email-body p {
                    font-size: 15px;
                    line-height: 1.6;
                    margin: 10px 0;
                    color: #555;
                }
                .email-body ul {
                    list-style: none;
                    padding: 0;
                    margin: 10px 0;
                }
                .email-body ul li {
                    font-size: 14px;
                    color: #555;
                    margin-bottom: 5px;
                    padding-left: 20px;
                    position: relative;
                }
                .email-body ul li::before {
                    content: '•';
                    position: absolute;
                    left: 0;
                    color: #d32f2f;
                    font-weight: bold;
                }
                .email-footer {
                    background: #f4f5f7;
                    text-align: center;
                    padding: 15px 20px;
                    font-size: 13px;
                    color: #888;
                }
                .email-footer a {
                    color: #d32f2f;
                    text-decoration: none;
                    font-weight: 500;
                }
                .email-footer a:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <!-- Header -->
                <div class='email-header'>
                    <img src='./img/bfp2.jpg' alt='BFP Minglanilla Logo'>
                    <h1>Bureau of Fire Protection</h1>
                </div>

                <!-- Body -->
                <div class='email-body'>
                    <h2>Your OTP Code</h2>
                    <div class='otp-box'>$otp</div>
                    <p>Use the above one-time password (OTP) to reset your account password. The OTP is valid for <strong>5 minutes</strong>.</p>
                    <p>To ensure your account’s safety, please:</p>
                    <ul>
                        <li>Keep this OTP private and do not share it with anyone.</li>
                        <li>Complete the password reset within 5 minutes to avoid expiration.</li>
                        <li>Contact us immediately if you didn’t request this code.</li>
                    </ul>
                    <p>If you have any concerns or need help, reach out to our support team using the link below.</p>
                </div>

                <!-- Footer -->
                <div class='email-footer'>
                    <p>Need assistance? <a href='https://example.com/help'>Contact Support</a></p>
                    <p>&copy; " . date('Y') . " Bureau of Fire Protection Minglanilla Fire Station</p>
                </div>
            </div>
        </body>
        </html>";




        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expiration'])) {
    $_SESSION['otp'] = rand(100000, 999999); 
    $_SESSION['otp_expiration'] = time() + 300; 
}

if (isset($_POST['resendOtpButton'])) {
    $_SESSION['otp'] = rand(100000, 999999); 
    $_SESSION['otp_expiration'] = time() + 300; 
    if (!empty($email)) {
        if (sendOtpEmail($email, $_SESSION['otp'])) {
            $otpError = 'A new OTP has been sent to your email.';
        } else {
            $otpError = 'Failed to send OTP. Please try again.';
        }
    } else {
        $otpError = 'Email address is missing. Please refresh the page.';
    }
}

$current_time = time();
$otp_time = isset($_SESSION['otp_expiration']) ? $_SESSION['otp_expiration'] : 0;

// Check OTP expiration
if ($current_time > $otp_time) {
    $otpError = 'OTP expired. Please request a new OTP.';
}

// Handle password change
if (isset($_POST['changePasswordButton'])) {
    $newPassword = trim($_POST['newPassword']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $otp = trim($_POST['otp']);

    if ($newPassword !== $confirmPassword) {
        $passwordMismatchError = 'Passwords do not match. Please try again.';
    } elseif (!isset($_SESSION['otp']) || $current_time > $_SESSION['otp_expiration']) {
        $otpError = 'OTP expired. Please request a new OTP.';
    } else {
        if (preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{12,}$/', $newPassword)) {
            if ($otp == $_SESSION['otp']) {
                $hash_pass = password_hash($newPassword, PASSWORD_DEFAULT);

                $query = "UPDATE users SET password = '$hash_pass' WHERE email = '$email'";
                $result = mysqli_query($conn, $query);

                if ($result) {
                    $passwordChangeSuccess = 'Password changed successfully!';
                    unset($_SESSION['otp']);
                    unset($_SESSION['otp_expiration']);
                    header("Location: ./index/index.php"); // Redirect to index
                    exit;
                } else {
                    $otpError = 'Error updating password. Please try again.';
                }
            } else {
                $otpError = 'Invalid OTP. Please check your email for the correct OTP.';
            }
        } else {
            $passwordMismatchError = 'Passwords should be at least 12 characters, uppercase, lowercase, and a number.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/change_password.css">
    <title>BFP Minglanilla - Change Password</title>
</head>
<body>
    <div class="main-container">
        <div class="login-header">
            <img src="./img/bfp2.jpg" alt="Bureau of Fire Protection Logo">
            <h1>Bureau of Fire Protection</h1>
            <p id="timer">Time remaining: <span id="timeRemaining"></span></p>
        </div>
        <form action="change_password.php" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly required>
                </div>
                <div class="form-group">
                    <label for="otp">OTP</label>
                    <input type="text" id="otp" name="otp" placeholder="Enter OTP">
                </div>
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <div class="input-group">
                        <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password">
                        <i class="fas fa-eye eye-icon" onclick="togglePasswordVisibility('newPassword')"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password">
                        <i class="fas fa-eye eye-icon" onclick="togglePasswordVisibility('confirmPassword')"></i>
                    </div>
                </div>
                <div class="form-group full-width">
                    <button type="submit" class="change-password" name="changePasswordButton">Change Password</button>
                    <button type="submit" name="resendOtpButton" id="resendOtpButton" style="display: none;">
                        <i class="fas fa-redo"></i> Resend OTP
                    </button>
                </div>
            </div>
            <div class="login-footer">
                <p>Remembered your password? <a href="./index/index.php">Back to Login</a></p>
            </div>

            <p class="error-message"><?php echo htmlspecialchars($otpError); ?></p>
            <p class="error-message"><?php echo htmlspecialchars($passwordMismatchError); ?></p>
            <p class="success-message"><?php echo htmlspecialchars($passwordChangeSuccess); ?></p>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script>
    var remainingTime = <?php echo max($_SESSION['otp_expiration'] - time(), 0); ?>;
    const resendOtpButton = document.getElementById('resendOtpButton');

    function updateTimer() {
        const timerElement = document.getElementById("timeRemaining");
        if (remainingTime > 0) {
            const minutes = Math.floor(remainingTime / 60);
            const seconds = remainingTime % 60;
            timerElement.innerText = `${minutes}m ${seconds}s`;
            remainingTime--;
            resendOtpButton.style.display = "none"; 
        } else {
            clearInterval(timerInterval);
            timerElement.innerText = "OTP expired. You can resend OTP.";
            resendOtpButton.style.display = "inline-flex"; 
            resendOtpButton.disabled = false;
        }
    }

    var timerInterval = setInterval(updateTimer, 1000);
    updateTimer();

    function togglePasswordVisibility(inputId) {
        const inputField = document.getElementById(inputId);
        const eyeIcon = inputField.nextElementSibling;

        if (inputField.type === 'password') {
            inputField.type = 'text'; 
            eyeIcon.classList.replace('fa-eye', 'fa-eye-slash'); 
        } else {
            inputField.type = 'password'; 
            eyeIcon.classList.replace('fa-eye-slash', 'fa-eye'); 
        }
    }
    </script>
</body>
</html>
