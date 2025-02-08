<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body, h1, p, a {
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
        }

        .profile-container {
            width: 100%;
            max-width: 960px;
            /* background-color: #fff; */
            border-radius: 12px;
            /* box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); */
            overflow: hidden;
            display: flex;
            flex-direction: column;
            margin-top: 40px;
        }

        .cover-photo {
            height: 250px;
            background-image: url('../img/cover.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .profile-photo {
            position: absolute;
            bottom: -60px;
            left: 20px;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            transition: transform 0.3s ease;
        }

        .profile-photo:hover {
            transform: scale(1.05);
        }

        .profile-header {
            text-align: center;
            padding-top: 90px;
            padding-bottom: 20px;
            background-color: #fff;
            border-bottom: 1px solid #ddd;
        }

        .profile-header h1 {
            font-size: 2.2rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .profile-header span {
            font-size: 1.1rem;
            color: #7f8c8d;
            display: block;
        }

        .profile-main {
            padding: 30px;
            background-color: #fff;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #34495e;
            margin-bottom: 15px;
        }

        .profile-contact-info p, .profile-work p {
            font-size: 1rem;
            margin-bottom: 10px;
            color: #7f8c8d;
        }

        .profile-contact-info p span, .profile-work p span {
            font-weight: 600;
            color: #2c3e50;
        }

        .edit-button {
            display: inline-block;
            padding: 8px 20px;
            background-color: #2980b9;
            color: #fff;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            margin-top: 15px;
            font-weight: 600;
            text-transform: uppercase;
            transition: background-color 0.3s ease;
        }

        .edit-button:hover {
            background-color: #1d6fa5;
        }

        .profile-footer {
            text-align: center;
            font-size: 0.85rem;
            color: #7f8c8d;
            padding: 20px;
            background-color: #f8f8f8;
        }

        .profile-footer a {
            color: #2980b9;
            text-decoration: none;
        }

        .profile-footer a:hover {
            color: #1d6fa5;
        }
    </style>
</head>
<body>

    <div class="profile-container">
        <!-- Cover Photo and Profile Picture -->
        <div class="cover-photo">
            <img id="profile-picture" class="profile-photo" src="https://via.placeholder.com/140" alt="Profile Picture">
        </div>

        <div class="profile-header">
            <h1 id="full-name">John Doe</h1>
            <span id="role">Software Developer</span>
        </div>

        <div class="profile-main">
            <!-- Contact Info -->
            <div class="profile-contact-info">
                <div class="section-title">Contact Information</div>
                <p><span>Phone:</span> <span id="phone">123-456-7890</span></p>
                <p><span>Email:</span> <span id="email">johndoe@example.com</span></p>
                <p><span>Username:</span> <span id="username">johndoe</span></p>
            </div>

            <!-- Account Info -->
            <div class="profile-work">
                <div class="section-title">Account Info</div>
                <p><span>Created At:</span> <span id="created-at">2023-01-01</span></p>
                <p><span>Start Suspend:</span> <span id="start-suspend">N/A</span></p>
                <p><span>End Suspend:</span> <span id="end-suspend">N/A</span></p>
            </div>

            <!-- Edit Profile Button -->
            <a href="#" class="edit-button" id="edit-contact">Edit Profile</a>
        </div>

        <!-- Footer -->
        <div class="profile-footer">
            <p><a href="inspector.php">Go back</a></p>
        </div>
    </div>

    <script>
        // Fetch user data and populate the page
        fetch(`profile-end.php`)
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
                    document.getElementById('created-at').textContent = user.created_at;
                    document.getElementById('start-suspend').textContent = user.start_suspend || 'N/A';
                    document.getElementById('end-suspend').textContent = user.end_suspend || 'N/A';
                    document.getElementById('profile-picture').src = user.profile_picture || 'https://via.placeholder.com/140';
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching user data:', error);
                alert('Failed to load user data.');
            });

        // Handle profile picture edit
        document.getElementById('profile-picture').addEventListener('click', function() {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*';

            fileInput.onchange = (e) => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        document.getElementById('profile-picture').src = event.target.result;
                        // You can send the image data to the server to update the profile picture
                        const formData = new FormData();
                        formData.append('profile_picture', file);
                        fetch('upload-profile-picture.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                alert('Profile picture updated!');
                            } else {
                                alert('Failed to update profile picture');
                            }
                        })
                        .catch(err => {
                            alert('Error uploading profile picture.')
                            console.log(err)
                        });
                    };
                    reader.readAsDataURL(file);
                }
            };

            fileInput.click();
        });

        // Handle the "Edit Profile" button
        document.getElementById('edit-contact').addEventListener('click', function() {
            const phone = document.getElementById('phone');
            const email = document.getElementById('email');
            const username = document.getElementById('username');

            if (this.textContent === 'Edit Profile') {
                phone.setAttribute('contenteditable', 'true');
                email.setAttribute('contenteditable', 'true');
                username.setAttribute('contenteditable', 'true');
                this.textContent = 'Save Changes';
            } else {
                phone.removeAttribute('contenteditable');
                email.removeAttribute('contenteditable');
                username.removeAttribute('contenteditable');
                this.textContent = 'Edit Profile';

                // Send updated data to the server
                const updatedData = {
                    phone: phone.textContent,
                    email: email.textContent,
                    username: username.textContent
                };

                fetch('update_profile.php', {
                    method: 'POST',
                    body: JSON.stringify(updatedData)
                })
                .then(response => {
                    response.json()
                })
                .then(data => {
                    console.log(data)
                    // if (data.status === 'success') {
                    //     alert('Profile updated successfully!');
                    // } else {
                    //     alert('Failed to update profile.');
                    // }
                })
                .catch(err => {
                    alert('Error updating profile.')
                    console.log(err)
                });
            }
        });
    </script>
</body>
</html>
