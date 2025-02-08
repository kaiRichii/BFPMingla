

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Minglanilla - User Form</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        #signup-form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            width: 100%; 
            padding: 0; 
            margin: 0;
        }

        .form-group {
            flex: 1 1 calc(50% - 20px);
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .form-group input[type="text"], .form-group select, 
        .form-group input[type="number"], .form-group input[type="date"], .form-group textarea, 
        .form-group input[type="password"], .form-group input[type="email"] {
            padding: 10px;
            font-size: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-dark);
            background-color: #f9f9f9;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus, .form-group select:focus,
        .form-group input[type="number"]:focus, .form-group input[type="date"]:focus, .form-group textarea:focus,
        .form-group input[type="password"]:focus, .form-group input[type="email"]:focus {
            border-color: var(--primary-color);
        }

        /* Dynamic Fields Styling */
        #dynamicFields {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            width: 100%;
        }


        /* Button Styling */
        .button-container {
            width: 100%;
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .button-container button {
            background-color: var(--primary-red);
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 0.9rem;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button-container button:hover {
            background-color: #b71c1c;
        }

.password-group {
    position: relative;
    width: 100%;
}

.input-group {
    display: flex;
    align-items: center;
    position: relative;
    width: 100%;
}

.input-group input {
    width: 100%;
    padding: 10px 40px 10px 10px; 
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.input-group input:focus {
    border-color: #d32f2f; 
    background-color: #ffffff; 
}

.input-group input:focus + .eye-icon {
    color: #d32f2f; 
}

.eye-icon {
    position: absolute;
    right: 10px; 
    cursor: pointer;
    font-size: 18px;
    color: #aaa;
    z-index: 2; 
}

#username{
    width: 49.1%;
}

input:invalid {
    border-color: red; 
}

input:valid {
    border-color: green; 
}

.error-message {
    color: red;
    font-size: 0.875em;
    margin-top: 5px;
}

input {
    transition: border-color 0.3s ease-in-out;
}
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <nav class="menu" role="navigation">
            <a href="admin.php" class="menu-item">
                <i class="fas fa-house-chimney"></i> 
                <span>Home</span>
            </a>
            <div class="menu-item clients">
                <a href="clients.php" class="list">
                    <i class="fas fa-user-friends"></i>
                    <span>FSIC/FSEC</span>
                </a>
                <div class="dropdown-menu">
                    <a href="clients.php" class="dropdown-item">
                        <i class="fas fa-address-book"></i>
                        <span>Client List</span>
                    </a>
                    <a href="adminreport.php" class="dropdown-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports</span>
                    </a>
                </div>
            </div>
            <div class="menu-item clients active">
                <a href="user_accounts.php" class="list">
                    <i class="fas fa-user-cog"></i> 
                    <span>User Accounts</span>
                </a>
                <a href="user_form.php" class="add-link" style="margin-left: 3px">
                    <i class="fas fa-circle-plus"></i> 
                    <span>Add</span>
                </a>
            </div>
            <div class="menu-item clients">
                <a href="personnels.php" class="list">
                    <i class="fa-solid fa-helmet-safety"></i> 
                    <span>Personnel</span>
                </a>
                <a href="personnel-form.php" class="add-link">
                    <i class="fas fa-circle-plus"></i> 
                    <span>Add</span>
                </a>
            </div>
        </nav>
        <footer class="sidebar-footer">
            <!-- <a href="profile.php" class="footer-item">
                <i class="fas fa-user-circle"></i> 
                <span>Profile</span>
            </a> -->
            <a href="changePassword.php" class="footer-item">
                <i class="fa-solid fa-key"></i> 
                <span>Change Password</span>
            </a>
        </footer>
    </aside>

    <header class="header" id="header">
        <button class="toggle-btn" id="toggle-btn"><i class="fa-solid fa-bars"></i></button>
        <nav class="header-nav">
            <a href="../logout.php" class="header-nav-item">
                <i class="fas fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </nav>
    </header>

    <div class="content" id="mainContent">
        <h2>User Form</h2>
        <?php if (isset($success_message)): ?>
            <p style="color: green;"><?php echo $success_message; ?></p>
        <?php elseif (isset($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form id="signup-form">
    <div class="form-group">
        <label for="full_name">Full Name:</label>
        <input type="text" id="full_name" name="fullname" required>
    </div>
    <div class="form-group">
        <label for="email" class="adjust-label">Email:</label>
        <input type="email" id="email" name="email" required>
        <span id="email-error" class="error-message" role="alert"></span>
    </div>
    <div class="form-group">
        <label for="contactNumber" class="adjust-label">Contact Number:</label>
        <input type="text" id="contactNumber" name="phone" required>
        <span id="contactNumber-error" class="error-message" role="alert"></span>
    </div>
    <div class="form-group">
        <label for="role" class="adjust-label">Role:</label>
        <select id="role" name="role" required>
            <option value="">Select Role</option>
            <option value="Staff">Staff</option>
            <option value="Inspector">Inspector</option>
            <option value="Admin">Admin</option>
        </select>
    </div>

    <!-- Password Field -->
    <div class="form-group password-group">
        <label for="password">Password:</label>
        <div class="input-group">
            <input type="password" id="password" name="password" required>
            <i class="fas fa-eye eye-icon" onclick="togglePasswordVisibility('password')"></i>
        </div>
        <span id="password-error" class="error-message" role="alert"></span>
    </div>

    <!-- Password Confirmation Field -->
    <div class="form-group password-group">
        <label for="password_confirmation">Password Confirmation:</label>
        <div class="input-group">
            <input type="password" id="password_confirmation" name="cpassword" required>
            <i class="fas fa-eye eye-icon" onclick="togglePasswordVisibility('password_confirmation')"></i>
        </div>
        <span id="confirmPassword-error" class="error-message" role="alert"></span>
    </div>

    <!-- Username Field -->
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <span id="username-error" class="error-message" role="alert"></span>
    </div>

    <div class="button-container">
        <button type="submit">SAVE</button>
        <button type="button" onclick="clearForm()">Clear Form</button>
    </div>
</form>

    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggle-btn');
        const header = document.getElementById('header');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            if (sidebar.classList.contains('collapsed')) {
                header.style.backgroundColor = '#242426';  
            } else {
                header.style.backgroundColor = '#1c1c1e';
            }
        });

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

         // new
         function clearForm() {
            document.getElementById('signup-form').reset();
        }
        
        function debounce(func, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

function showError(inputId, message) {
    const inputField = document.getElementById(inputId);
    const errorField = document.querySelector(`#${inputId}-error`);

    if (message) {
        inputField.classList.add('error'); 
        errorField.textContent = message; 
        errorField.style.display = 'block'; 
        inputField.setAttribute('aria-describedby', `${inputId}-error`); 
    } else {
        inputField.classList.remove('error'); 
        errorField.textContent = ''; 
        errorField.style.display = 'none'; 
        inputField.removeAttribute('aria-describedby'); 
    }
}

function validateUsername(username) {
    if (username.trim() === "") {
        showError('username', 'Username cannot be empty');
        return;
    }

    $.ajax({
        url: '../ajax.php?action=check_username',
        method: 'POST',
        data: { username },
        success: function(resp) {
            if (resp === 'username-taken') {
                showError('username', 'Username is already taken');
            } else {
                showError('username', '');
            }
        }
    });
}

function validateEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email)) {
        showError('email', 'Invalid email address');
        return;
    }

    $.ajax({
        url: '../ajax.php?action=check_email',
        method: 'POST',
        data: { email },
        success: function(resp) {
            if (resp === 'email-taken') {
                showError('email', 'Email is already taken');
            } else {
                showError('email', '');
            }
        }
    });
}


$('#username').on('input', debounce(function() {
    validateUsername($(this).val());
}, 300)); 

$('#email').on('input', debounce(function() {
    validateEmail($(this).val());
}, 300)); 

function validatePhone(phone) {
    const phonePattern = /^(09\d{9}|\+639\d{9})$/;

    if (!phonePattern.test(phone)) {
        showError('contactNumber', 'Invalid phone number (e.g., 09123456789 or +639123456789)');
    } else {
        showError('contactNumber', '');
    }
}

function validatePasswordStrength(password) {
    const strengthPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{12,}$/;

    if (!strengthPattern.test(password)) {
        showError('password', 'Password must be at least 12 characters and include uppercase, lowercase, numbers, and symbols');
    } else {
        showError('password', '');
    }
}

function validatePasswordMatch(password, confirmPassword) {
    if (password !== confirmPassword) {
        showError('confirmation_password', 'Passwords do not match');
    } else {
        showError('confirmation_password', '');
    }
}

$('#username').on('input', function() {
    validateUsername($(this).val());
});

$('#email').on('input', function() {
    validateEmail($(this).val());
});

$('#contactNumber').on('input', function() {
    validatePhone($(this).val());
});

$('#password').on('input', function() {
    validatePasswordStrength($(this).val());
});

$('#confirmation_password').on('input', function() {
    validatePasswordMatch($('#password').val(), $(this).val());
});

$('#signup-form').submit(function(e){
    e.preventDefault();
    $.ajax({
            url:'../ajax.php?action=signup',
            method:'POST',
            data:$(this).serialize(),
    beforeSend: function() {
        Swal.fire({
            icon: "info",
            title: "Please wait...",
            timer: 60000,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    },
    success:function(resp){
        console.log(resp)
        if(resp == 'incomplete-field'){
            Swal.fire({
                icon: 'info',
                title: 'Please fill all fields!',
                heightAuto: false
            });
        }else if(resp == 'password-unmatched'){
            Swal.fire({
                icon: 'info',
                title: 'Password and confirm password not matched!',
                heightAuto: false
            });
        }else if(resp == 'invalid-password'){
            Swal.fire({
                icon: 'info',
                title: 'Invalid password!',
                text: 'Password must be at least 12 characters and include uppercase, lowercase, numbers, and symbols.',
                heightAuto: false
            });
        }else if(resp == 'invalid-phone'){
            Swal.fire({
                icon: 'info',
                title: 'Invalid phone number!',
                heightAuto: false
            });
        }else if(resp == 'invalid-email'){
            Swal.fire({
                icon: 'info',
                title: 'Invalid email address!',
                heightAuto: false
            });
        }else if(resp == 'username-taken'){
            Swal.fire({
                icon: 'info',
                title: 'Username already taken!',
                heightAuto: false
            });
        }else if(resp == 'email-taken'){
            Swal.fire({
                icon: 'info',
                title: 'Email already taken!',
                heightAuto: false
            });
        }else if(resp == 'success'){
            Swal.fire({
                icon: 'success',
                title: 'Registration successful!',
                heightAuto: false
            }).then(function() {
                window.location.href = 'user_accounts.php';
            });
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: 'Something went wrong.',
                heightAuto: false
            });
        }
    }
    })
})
    </script>
</body>
</html>
