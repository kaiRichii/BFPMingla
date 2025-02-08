function togglePasswordVisibility(inputId) {
    const inputField = document.getElementById(inputId);
    const eyeIcon = inputField.parentNode.querySelector('.eye-icon');

    if (inputField.type === 'password') {
        inputField.type = 'text'; 
        eyeIcon.classList.replace('fa-eye', 'fa-eye-slash'); 
    } else {
        inputField.type = 'password'; 
        eyeIcon.classList.replace('fa-eye-slash', 'fa-eye'); 
    }
}

function showError(inputId, message) {
    const errorField = document.querySelector(`#${inputId}-error`);

    if (message) {
        errorField.textContent = message; 
        errorField.style.display = 'block'; 
    } else {
        errorField.textContent = ''; 
        errorField.style.display = 'none'; 
    }
}

function validateEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email)) {
        showError('email', 'Invalid email address');
        return;
    }

    $.ajax({
        url: '../ajax.php?action=check_email_login', 
        method: 'POST',
        data: { email },
        success: function (resp) {
            if (resp.trim() === 'email-does-not-exist') {
                showError('email', 'Email does not exist');
            } else if (resp.trim() === 'email-exists') {
                showError('email', ''); 
            } else {
                console.error('Unexpected response from server:', resp);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}

document.getElementById('email').addEventListener('input', function () {
    validateEmail(this.value);
});

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

 // Clear the browser's history stack to prevent navigation back
 history.replaceState(null, null, location.href);
 window.addEventListener('popstate', function () {
     history.pushState(null, null, location.href);
 });