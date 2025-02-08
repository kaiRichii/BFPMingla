<?php
session_start();
include '../db_connection.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; 

$loginError = ''; 
$attemptCount = isset($_SESSION['attempt_count']) ? $_SESSION['attempt_count'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['email']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $userRole = $user['role'];
        $storedHash = $user['password']; 
        $status = $user['status'];
        $startSuspended = $user['start_suspend'];
        $endSuspended = $user['end_suspend'];
        $currentDate = date('Y-m-d');

        if ($status === 'Suspended' && $currentDate >= $startSuspended && $currentDate <= $endSuspended) {
            echo json_encode(['status' => 'error', 'message' => "Your account is suspended from $startSuspended to $endSuspended."]);
            exit;
        } elseif ($status === 'Suspended' && $currentDate > $endSuspended) {
            $updateQuery = "UPDATE users SET status = 'Active', start_suspend = NULL, end_suspend = NULL WHERE email = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("s", $username);
            $updateStmt->execute();
        }

        if (in_array($status, ['Approved', 'Active', 'Inactive']) || empty($status)) {
            if (password_verify($password, $storedHash)) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $userRole;

                unset($_SESSION['attempt_count']); 

                $redirect = '';
                if ($userRole === 'Admin') {
                    $redirect = '../admin/admin.php';
                } elseif ($userRole === 'Staff') {
                    $redirect = '../staff/staff.php';
                } elseif ($userRole === 'Inspector') {
                    $redirect = '../inspector/inspector.php';
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid role.']);
                    exit;
                }
                echo json_encode(['status' => 'success', 'redirect' => $redirect]);
                exit;
            } else {
                // Handle invalid login attempts
                if (!isset($_SESSION['attempt_count'])) {
                    $_SESSION['attempt_count'] = 0; 
                }

                $_SESSION['attempt_count']++;
                $loginError = 'Invalid credentials. Please try again.';

                // Check if attempts reached 3
                if ($_SESSION['attempt_count'] >= 3) {
                    sendOtp($username); 
                    $loginError = 'Invalid credentials. An OTP has been sent to your email.';
                    unset($_SESSION['attempt_count']); 
                    
                    echo json_encode([
                        'status' => 'redirect',
                        'message' => $loginError,
                        'redirect' => '../change_password.php?email=' . urlencode($username)
                    ]);
                    exit;
                }

                echo json_encode([
                    'status' => 'error',
                    'message' => $loginError
                ]);
                exit;
            }
        } elseif ($status === 'Pending') {
            echo json_encode(['status' => 'error', 'message' => "Your account registration is pending, wait to approve by admin."]);
            exit;
        } elseif ($status === 'Declined') {
            echo json_encode(['status' => 'error', 'message' => "Your account registration is declined by admin."]);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials. Please try again.']);
        exit;
    }
}

function sendOtp($email) {
    $otp = rand(100000, 999999); 

    
    $_SESSION['otp'] = $otp;

   
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'tessnarval11@gmail.com'; 
        $mail->Password = 'vlkc srgz zdrw llbd'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('tessnarval11@gmail.com', 'Bureau of Fire Protection Minglanilla Fire Station');
        $mail->addAddress($email); 

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
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
                    <img src='./img/bfp3.png' alt='BFP Minglanilla Logo'>
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
                    <p>Need assistance? <a href='https://www.facebook.com/minglanillafire?mibextid=ZbWKwL'>Contact Support</a></p>
                    <p>&copy; " . date('Y') . " Bureau of Fire Protection Minglanilla Fire Station</p>
                </div>
            </div>
        </body>
        </html>";



        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Minglanilla - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="main-container">

        <div class="image-section"></div>

        <div class="login-container">
            <div class="login-header">
                <img src="../img/bfp2.jpg" alt="Bureau of Fire Protection Logo"> 
                <h1>Bureau of Fire Protection</h1>
                <p>Welcome! Please log in to your account.</p>
            </div>
            <form action="index.php" method="POST" id="loginForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-group">
                        <input type="text" id="email" name="email" placeholder="Email" required>
                    </div>
                    <span id="email-error" class="error-message" role="alert"></span>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <i class="fas fa-eye eye-icon" onclick="togglePasswordVisibility('password')"></i>
                    </div>
                    <span id="password-error" class="error-message" role="alert"></span>
                </div>
                <div class="form-group">
                    <button type="submit" name="loginButton">Login</button>
                </div>
                <p style="color: red;" class="error-message"><?php echo htmlspecialchars($loginError); ?></p> 
            </form>
            <div class="login-footer">
                <p>Don't have an account? <a href="signup.php">Signup here</a></p>
                <p><a href="../forgot_password.php" id="forgotPasswordLink">Forgot your password?</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="login.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(this);

            fetch('index.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Handle successful login
                    Swal.fire({
                        title: 'Login Successful',
                        text: 'Redirecting...',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.href = data.redirect;  
                    });
                } else if (data.status === 'redirect') {
                    Swal.fire({
                        title: 'Account Locked',
                        text: data.message,
                        icon: 'warning',
                        confirmButtonText: 'Reset Password',
                    }).then(() => {
                        window.location.href = data.redirect; 
                    });
                } else {
                    Swal.fire({
                        title: 'Login Error',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'Try Again',
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'An unexpected error occurred. Please try again later.',
                    icon: 'error',
                    confirmButtonText: 'Okay',
                });
            });
        });
    </script>
</body>
</html>
