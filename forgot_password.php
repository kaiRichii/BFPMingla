<?php
session_start();
include 'db_connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require './vendor/autoload.php';

$forgotPasswordError = '';
$forgotPasswordSuccess = '';

if (isset($_POST['forgotPasswordButton'])) {
    $email = trim($_POST['email']);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) == 1) {
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_email'] = $email;
            $_SESSION['otp_expiration'] = time() + 300; // 5 minutes

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
                        .email-header h2 {
                            font-size: 22px;
                            margin: 0;
                            font-weight: 600;
                        }
                        .email-body {
                            padding: 20px 30px;
                        }
                        .email-body p {
                            font-size: 15px;
                            line-height: 1.6;
                            margin: 10px 0;
                            color: #555;
                        }
                        .email-body strong {
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
                            <h2>BFP Minglanilla</h2>
                        </div>

                        <!-- Body -->
                        <div class='email-body'>
                            <p>Dear User,</p>
                            <p>Your OTP for resetting your password is: <strong>$otp</strong>.</p>
                            <p>This OTP will expire in 5 minutes. Please do not share it with anyone.</p>
                            <p>Thank you,<br>BFP Minglanilla</p>
                        </div>

                        <!-- Footer -->
                        <div class='email-footer'>
                            <p>If you didn’t request this, please ignore this email or contact us immediately.</p>
                        </div>
                    </div>
                </body>
                </html>";


                $mail->send();
                $forgotPasswordSuccess = 'An OTP has been sent to your email.';
            } catch (Exception $e) {
                $forgotPasswordError = 'Failed to send OTP. Please try again.';
            }
        } else {
            $forgotPasswordError = 'Email does not exist. Please check and try again.';
        }
    } else {
        $forgotPasswordError = 'Invalid email address. Please enter a valid email.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Minglanilla - Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Styling for the forgot password page */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f0f2f5;
            font-family: 'Poppins', sans-serif;
            color: #333;
            margin: 0;
        }

        .main-container {
            width: 100%;
            max-width: 500px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

        .main-container img {
            width: 50px;
            margin-bottom: 15px;
        }

        .main-container h1 {
            color: #d32f2f;
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .main-container p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .form-group button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            background-color: #d32f2f;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-group button:hover {
            background-color: #b71c1c;
        }

        .error-message, .success-message {
            font-size: 12px;
            margin-top: 10px;
        }

        .error-message {
            color: #d32f2f;
        }

        .success-message {
            color: #28a745;
        }

        .login-footer {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .login-footer a {
            color: #d32f2f;
            text-decoration: none;
            font-weight: 500;
        }

        .login-footer a:hover {
            color: #b71c1c;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <img src="./img/bfp2.jpg" alt="BFP Logo">
        <h1>Bureau of Fire Protection</h1>
        <p>Forgot your password? Enter your email address to receive a one-time password (OTP).</p>
        <form action="forgot_password.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <button type="submit" name="forgotPasswordButton">Send OTP</button>
            </div>
            <p class="error-message"><?php echo htmlspecialchars($forgotPasswordError); ?></p>
            <p class="success-message"><?php echo htmlspecialchars($forgotPasswordSuccess); ?></p>
        </form>
        <div class="login-footer">
            <p>Remembered your password? <a href="./index/index.php">Back to Login</a></p>
        </div>
    </div>
</body>
</html>
