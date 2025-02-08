<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body, h1, p, input, button {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f4f6f9;
            color: #333;
            min-height: 100vh;
            justify-content: center;
        }

        .form-container {
            width: 100%;
            max-width: 400px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        label {
            font-size: 1rem;
            color: #34495e;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            color: #333;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #2980b9;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-transform: uppercase;
        }

        button:hover {
            background-color: #1d6fa5;
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            color: #2980b9;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            color: #1d6fa5;
        }

        .error-message, .success-message {
            text-align: center;
            margin-bottom: 15px;
            font-size: 0.95rem;
            color: red;
        }

        .success-message {
            color: green;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Change Password</h1>

        <form id="change-password-form">
            <label for="current-password">Current Password</label>
            <input type="password" id="current-password" placeholder="Enter your current password" required>

            <label for="new-password">New Password</label>
            <input type="password" id="new-password" placeholder="Enter your new password" required>

            <label for="confirm-password">Re-Enter New Password</label>
            <input type="password" id="confirm-password" placeholder="Re-enter your new password" required>

            <button type="submit">Change Password</button>
        </form>

        <div class="back-link">
            <a href="staff.php">Go back</a>
        </div>
    </div>

    <script>
        document.getElementById('change-password-form').addEventListener('submit', function(event) {
            event.preventDefault();

            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            if (newPassword !== confirmPassword) {
                displayMessage('Passwords do not match', 'error');
                return;
            }

            // Send password change request to the server
            fetch('update_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    currentPassword: currentPassword,
                    newPassword: newPassword
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    displayMessage('Password changed successfully!', 'success');
                    document.getElementById('change-password-form').reset();
                } else {
                    displayMessage(data.message || 'Failed to change password', 'error');
                }
            })
            .catch(error => {
                displayMessage('An error occurred. Please try again.', 'error');
                console.error(error);
            });
        });

        function displayMessage(message, type) {
            const messageElement = document.createElement('div');
            messageElement.className = type === 'error' ? 'error-message' : 'success-message';
            messageElement.textContent = message;

            const formContainer = document.querySelector('.form-container');
            const existingMessage = document.querySelector('.error-message, .success-message');

            if (existingMessage) {
                formContainer.removeChild(existingMessage);
            }

            formContainer.insertBefore(messageElement, document.getElementById('change-password-form'));
        }
    </script>
</body>
</html>
