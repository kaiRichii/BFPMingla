<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Minglanilla - Sign Up</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="signup.css">
    <style>
        .error-message {
    display: none;
    color: red;
    font-size: 0.85em;
    margin-top: 5px;
}
    </style>
</head>
<body>
    <div class="main-container">
        <div class="signup-container">
            <div class="form-header">
                <img src="../img/bfp2.jpg" alt="Bureau of Fire Protection Logo">
                <h1>Bureau of Fire Protection</h1>
                <p>Create your account with BFP Minglanilla</p>
            </div>
            <form id="signup-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="fullname" placeholder="Full name" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Username" required>
                        <span id="username-error" class="error-message" role="alert"></span>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Email" required>
                        <span id="email-error" class="error-message" role="alert"></span>
                    </div>
                    <div class="form-group">
                        <label for="contactNumber">Contact Number</label>
                        <input type="text" id="contactNumber" name="phone" placeholder="Contact number" required>
                        <span id="contactNumber-error" class="error-message" role="alert"></span>
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
                        <label for="confirmPassword">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" id="confirmPassword" name="cpassword" placeholder="Confirm password" required>
                            <i class="fas fa-eye eye-icon" onclick="togglePasswordVisibility('confirmPassword')"></i>
                        </div>
                        <span id="confirmPassword-error" class="error-message" role="alert"></span>
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="Staff">Staff</option>
                            <option value="Inspector">Inspector</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="signupButton">Sign Up</button>
                    </div>
                </div>
            </form>
            <div class="signup-footer">
                <p>Already have an account? <a href="index.php">Login here</a></p>
            </div>
        </div>

        <!-- Fire Image Section -->
        <div class="image-section"></div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="signup.js"></script>
</body>
</html>
