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
                window.location.href = './index.php';
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