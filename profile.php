<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Profile Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
         body, h1, p, a {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #ebedf0;
            color: #333;
        }

        .profile-container {
            max-width: 960px;
            width: 100%;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
        }

        .profile-sidebar {
            width: 40%;
            background-image: url('https://via.placeholder.com/960x720');
            background-size: cover;
            background-position: center;
        }

        .profile-main {
            flex: 1;
            padding: 30px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .profile-header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-header h1 {
            font-size: 1.8rem;
            color: #d32f2f;
        }

        .profile-header span {
            font-size: 0.95rem;
            color: #666;
            display: block;
        }

        .profile-rankings {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 10px 0;
        }

        .profile-rankings span {
            font-size: 1rem;
            font-weight: 600;
        }

        .profile-contact-info, .profile-work, .profile-skills {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .profile-contact-info p, .profile-work p, .profile-skills p {
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: #555;
        }

        .profile-contact-info p span, .profile-work p span {
            font-weight: 500;
            color: #000;
        }

        .profile-footer {
            text-align: center;
            font-size: 0.85rem;
            color: #666;
        }

        .profile-footer a {
            color: #d32f2f;
            text-decoration: none;
        }

        .profile-footer a:hover {
            color: #b71c1c;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <!-- Sidebar -->
        <div class="profile-sidebar"></div>

        <!-- Main Content -->
        <div class="profile-main">
            <div class="profile-header">
                <img id="profile-picture" src="https://via.placeholder.com/80" alt="Profile Picture">
                <div>
                    <h1 id="full-name"></h1>
                    <span id="role"></span>
                </div>
            </div>

            <div class="profile-contact-info">
                <div class="section-title">Contact Information</div>
                <p><span>Phone:</span> <span id="phone"></span></p>
                <p><span>Email:</span> <span id="email"></span></p>
                <p><span>Username:</span> <span id="username"></span></p>
                <p><span>Status:</span> <span id="status"></span></p>
            </div>

            <div class="profile-work">
                <div class="section-title">Account Info</div>
                <p><span>Created At:</span> <span id="created-at"></span></p>
                <p><span>Start Suspend:</span> <span id="start-suspend"></span></p>
                <p><span>End Suspend:</span> <span id="end-suspend"></span></p>
            </div>

            <div class="profile-footer">
                <p>Visit <a href="#">www.example.com</a> for more information.</p>
            </div>
        </div>
    </div>

    <script>
        // Fetch user ID from the query string
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('user_id');

        if (!userId) {
            alert('No user ID provided!');
            return;
        }

        // Fetch user data and populate the page
        fetch(`profile.php?user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const user = data.data;

                    // Populate the fields
                    document.getElementById('full-name').textContent = user.full_name;
                    document.getElementById('role').textContent = user.role;
                    document.getElementById('phone').textContent = user.phone;
                    document.getElementById('email').textContent = user.email;
                    document.getElementById('username').textContent = user.username;
                    document.getElementById('status').textContent = user.status;
                    document.getElementById('created-at').textContent = user.created_at;
                    document.getElementById('start-suspend').textContent = user.start_suspend || 'N/A';
                    document.getElementById('end-suspend').textContent = user.end_suspend || 'N/A';
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching user data:', error);
                alert('Failed to load user data.');
            });
    </script>
</body>
</html>
