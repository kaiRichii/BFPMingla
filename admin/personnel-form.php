<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Minglanilla - Enhanced</title>
    <?php include('admin-overviewlinks.php')?>
    <link rel="stylesheet" href="../css/style.css">
    <style>
    #userForm {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        width: 100%; 
        padding: 0; 
        margin: 0; 
    }

    @media (max-width: 768px) {
        .content {
            margin-left: 20px;
            margin-right: 20px;
            padding: 15px;
        }
        #userForm .form-group {
            flex: 1 1 100%;
        }
    }
    .profile-pic-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .image-upload-container {
        position: relative;
        width: 150px;  
        height: 150px; 
        border: 3px dashed #2d3436;
        border-radius: 12px;  
        padding: 10px;
        overflow: hidden;
        background-color: #f9f9f9;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: border-color 0.3s ease;
    }

    .image-upload-container:hover {
        border-color: var(--primary-color);  
    }

    #profilePic {
        width: 100%; 
        height: 100%;
        object-fit: cover;  
        border-radius: 8px;  
    }

    #profile_image {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 100%;
        opacity: 0; 
        cursor: pointer;
    }

    #profile_image:focus + .image-upload-container {
        border-color: var(--primary-color); 
    }

    .image-upload-container:hover #profilePic {
        opacity: 0.8;
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
            <div class="menu-item clients">
                <a href="user_accounts.php" class="list">
                    <i class="fas fa-user-cog"></i> 
                    <span>User Accounts</span>
                </a>
                <a href="user_form.php" class="add-link" style="margin-left: 3px">
                    <i class="fas fa-circle-plus"></i> 
                    <span>Add</span>
                </a>
            </div>
            <div class="menu-item clients active">
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
        <h2>Personnel Form</h2>
       
        <?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../db_connection.php';

    $uploadDirectory = "../uploads/";
    $filePath = NULL;

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = $_FILES['profile_image']['name'];
        $fileSize = $_FILES['profile_image']['size'];
        $fileType = $_FILES['profile_image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $filePath = $uploadDirectory . $newFileName;

            if (!move_uploaded_file($fileTmpPath, $filePath)) {
                die("File upload failed.");
                return;
            }
        } else {
            die("Invalid file type. Allowed types: " . implode(", ", $allowedExtensions));
        }
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO personnel (
        account_number, item_number, rank, first_name, middle_name, last_name, 
        suffix, contact_number, date_of_birth, marital_status, gender, 
        complete_address, religion, tin, pagibig, gsis, philhealth, 
        tertiary_courses, post_graduate_courses, highest_eligibility, 
        highest_training, specialized_training, date_entered_other_gov_service, 
        date_entered_fire_service, mode_of_entry, date_of_last_promotion, 
        appointment_status, unit_code, unit_assignment, designation, image_path
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Check if prepare was successful
    if (!$stmt) {
        die("Preparation failed: (" . $conn->errno . ") " . $conn->error);
    }

    // Set parameters
    $account_number = $_POST['account_number'];
    $item_number = $_POST['item_number'];
    $rank = $_POST['rank'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'] ?? NULL;
    $last_name = $_POST['last_name'];
    $suffix = $_POST['suffix'] ?? NULL;
    $contact_number = $_POST['contact_number'];
    $date_of_birth = $_POST['date_of_birth'];
    $marital_status = $_POST['marital_status'];
    $gender = $_POST['gender'];
    $complete_address = $_POST['complete_address'];
    $religion = $_POST['religion'];
    $tin = $_POST['tin'];
    $pagibig = $_POST['pagibig'];
    $gsis = $_POST['gsis'];
    $philhealth = $_POST['philhealth'];
    $tertiary_courses = $_POST['tertiary_courses'];
    $post_graduate_courses = $_POST['post_graduate_courses'] ?? NULL;
    $highest_eligibility = $_POST['highest_eligibility'];
    $highest_training = $_POST['highest_training'];
    $specialized_training = $_POST['specialized_training'];
    $date_entered_other_gov_service = $_POST['date_entered_other_gov_service'] ?? NULL;
    $date_entered_fire_service = $_POST['date_entered_fire_service'] ?? NULL;
    $mode_of_entry = $_POST['mode_of_entry'];
    $date_of_last_promotion = $_POST['date_of_last_promotion'] ?? NULL;
    $appointment_status = $_POST['appointment_status'];
    $unit_code = $_POST['unit_code'];
    $unit_assignment = $_POST['unit_assignment'];
    $designations = isset($_POST['designation']) ? implode(", ", $_POST['designation']) : "";
    $imagePath = $filePath;

    // Bind parameters
    $stmt->bind_param("sssssssssssssssssssssssssssssss", 
        $account_number, $item_number, $rank, $first_name, $middle_name, 
        $last_name, $suffix, $contact_number, $date_of_birth, $marital_status, 
        $gender, $complete_address, $religion, $tin, $pagibig, 
        $gsis, $philhealth, $tertiary_courses, $post_graduate_courses, 
        $highest_eligibility, $highest_training, $specialized_training, 
        $date_entered_other_gov_service, $date_entered_fire_service, 
        $mode_of_entry, $date_of_last_promotion, 
        $appointment_status, $unit_code, $unit_assignment, $designations, $imagePath
    );

    // Execute and check for success
    if ($stmt->execute()) {
        // Success message with SweetAlert2 and redirection
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                title: 'Success!',
                text: 'Personnel added successfully!',
                icon: 'success',
                confirmButtonText: 'Ok'
            }).then(function() {
                window.location.href = 'personnels.php?success=1';
            });
        </script>";
        exit;
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
}
?>
        <form action="personnel-form.php" method="post" id="userForm" enctype="multipart/form-data">
        <div class="profile-pic-container form-group">
                <label for="profile_image">
                    <div class="image-upload-container">
                        <img id="profilePic" src="default-profile.png" alt="upload profile picture">
                        <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="previewImage(event)">
                    </div>
                </label>
            </div>
            <div class="form-group">
                <label for="account_number">Account Number:</label>
                <input type="text" id="account_number" name="account_number" required>
            </div>
            <div class="form-group">
                <label for="item_number">Item Number:</label>
                <input type="text" id="item_number" name="item_number" required>
            </div>
            <div class="form-group">
                <label for="rank">Rank:</label>
                <select id="rank" name="rank" required>
                    <option value="">Select Rank</option>
                    <option value="Senior Fire Officer IV (SFO4)">Senior Fire Officer IV (SFO4)</option>
                    <option value="Senior Fire Officer III (SFO3)">Senior Fire Officer III (SFO3)</option>
                    <option value="Senior Fire Officer II (SFO2)">Senior Fire Officer II (SFO2)</option>
                    <option value="Senior Fire Officer I (SFO1)">Senior Fire Officer I (SFO1)</option>
                    <option value="Fire Officer III (FO3)">Fire Officer III (FO3)</option>
                    <option value="Fire Officer II (FO2)">Fire Officer II (FO2)</option>
                    <option value="Fire Officer I (FO1)">Fire Officer I (FO1)</option>
                    <option value="Fire Officer (FO)">Fire Officer (FO)</option>
                    <option value="Non-Uniformed Personnel (NUP)">Non-Uniformed Personnel (NUP)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="middle_name">Middle Name:</label>
                <input type="text" id="middle_name" name="middle_name">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="suffix">Suffix:</label>
                <input type="text" id="suffix" name="suffix">
            </div>
            <div class="form-group">
                <label for="contact_number">Contact Number:</label>
                <input type="text" id="contact_number" name="contact_number" required>
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" required>
            </div>
            <div class="form-group">
                <label for="marital_status">Marital Status:</label>
                <select id="marital_status" name="marital_status" required>
                    <option value="">Select Marital Status</option>
                    <option value="single">Single</option>
                    <option value="married">Married</option>
                    <option value="widowed">Widowed</option>
                    <option value="divorced">Divorced</option>
                </select>
            </div>
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="complete_address">Complete Address:</label>
                <textarea id="complete_address" name="complete_address" required></textarea>
            </div>
            <div class="form-group">
                <label for="religion">Religion:</label>
                <input type="text" id="religion" name="religion" required>
            </div>
            <div class="form-group">
                <label for="tin">TIN:</label>
                <input type="text" id="tin" name="tin" required>
            </div>
            <div class="form-group">
                <label for="pagibig">PAGIBIG:</label>
                <input type="text" id="pagibig" name="pagibig" required>
            </div>
            <div class="form-group">
                <label for="gsis">GSIS:</label>
                <input type="text" id="gsis" name="gsis" required>
            </div>
            <div class="form-group">
                <label for="philhealth">PhilHealth:</label>
                <input type="text" id="philhealth" name="philhealth" required>
            </div>
            <div class="form-group">
                <label for="tertiary_courses">Tertiary Course/s:</label>
                <input type="text" id="tertiary_courses" name="tertiary_courses" required>
            </div>
            <div class="form-group">
                <label for="post_graduate_courses">Post Graduate Course/s:</label>
                <input type="text" id="post_graduate_courses" name="post_graduate_courses">
            </div>
            <div class="form-group">
                <label for="highest_eligibility">Highest Eligibility:</label>
                <input type="text" id="highest_eligibility" name="highest_eligibility" required>
            </div>
            <div class="form-group">
                <label for="highest_training">Highest Training:</label>
                <input type="text" id="highest_training" name="highest_training" required>
            </div>
            <div class="form-group">
                <label for="specialized_training">Specialized Training:</label>
                <input type="text" id="specialized_training" name="specialized_training" required>
            </div>
            <div class="form-group">
                <label for="date_entered_other_gov_service">Date Entered Other Government Service:</label>
                <input type="date" id="date_entered_other_gov_service" name="date_entered_other_gov_service">
            </div>
            <div class="form-group">
                <label for="date_entered_fire_service">Date Entered Fire Service:</label>
                <input type="date" id="date_entered_fire_service" name="date_entered_fire_service">
            </div>
            <div class="form-group">
                <label for="mode_of_entry">Mode of Entry:</label>
                <input type="text" id="mode_of_entry" name="mode_of_entry" required>
            </div>
            <div class="form-group">
                <label for="date_of_last_promotion">Date of Last Promotion:</label>
                <input type="date" id="date_of_last_promotion" name="date_of_last_promotion">
            </div>
            <div class="form-group">
                <label for="appointment_status">Appointment Status:</label>
                <select id="appointment_status" name="appointment_status" required>
                    <option value="">Select Appointment Status</option>
                    <option value="permanent">Permanent</option>
                    <option value="temporary">Temporary</option>
                </select>
            </div>
            <div class="form-group">
                <label for="unit_code">Unit Code:</label>
                <input type="text" id="unit_code" name="unit_code" required>
            </div>
            <div class="form-group">
                <label for="unit_assignment">Unit Assignment:</label>
                <input type="text" id="unit_assignment" name="unit_assignment" required>
            </div>
            <div class="form-group">
                <label for="designation">Designation:</label>
                <select id="designation" name="designation[]" multiple required>
                    <option value="">Select Designation</option>
                    <option value="Municipal Fire Marshal">Municipal Fire Marshal</option>
                    <option value="Shift In-Charge Alpha">Shift In-Charge Alpha</option>
                    <option value="C, Intelligence Section">C, Intelligence Section</option>
                    <option value="C, Investigation Section">C, Investigation Section</option>
                    <option value="Fire Safety Inspector">Fire Safety Inspector</option>
                    <option value="Driver">Driver</option>
                    <option value="Fire Safety Lecturer">Fire Safety Lecturer</option>
                    <option value="OLP Coordinator">OLP Coordinator</option>
                    <option value="Fire Safety Inspector (OLP Crew)">Fire Safety Inspector (OLP Crew)</option>
                    <option value="FSES Clerk">FSES Clerk</option>
                    <option value="Fire Truck Operator">Fire Truck Operator</option>
                    <option value="Driver (Crew)">Driver (Crew)</option>
                    <option value="Operations Clerk">Operations Clerk</option>
                    <option value="C, Fire Safety Enforcement Section">C, Fire Safety Enforcement Section</option>
                    <option value="PCF Custodian">PCF Custodian</option>
                    <option value="Liaison Officer">Liaison Officer</option>
                    <option value="C, Administration Section">C, Administration Section</option>
                    <option value="Collecting Agent">Collecting Agent</option>
                    <option value="Customer Relation Officer">Customer Relation Officer</option>
                    <option value="Shift In-Charge Bravo">Shift In-Charge Bravo</option>
                    <option value="C, Plans Section">C, Plans Section</option>
                    <option value="C, Training Section">C, Training Section</option>
                    <option value="EMS">EMS</option>
                    <option value="Fire Safety Lecturer">Fire Safety Lecturer</option>
                    <option value="EMS Driver">EMS Driver</option>
                    <option value="Duty Investigation Shift - B">Duty Investigation Shift - B</option>
                    <option value="Plan Evaluator">Plan Evaluator</option>
                    <option value="Fire Truck Operator">Fire Truck Operator</option>
                    <option value="Public Information Officer">Public Information Officer</option>
                    <option value="Crew (Public Information)">Crew (Public Information)</option>
                    <option value="Fire Safety Inspector (Crew)">Fire Safety Inspector (Crew)</option>
                    <option value="Duty Investigation Shift - A">Duty Investigation Shift - A</option>
                </select>
            </div>
            <div class="button-container">
                <button type="submit">SAVE</button>
            </div>
        </form>
    </div>

    <script src="../sidebar/sidebar-toggle.js"></script>
    <script>
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
            document.getElementById('userForm').reset();
        }

        function previewImage(event) {
            const image = document.getElementById("profilePic");
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    image.src = e.target.result; 
                };
                
                reader.readAsDataURL(file); 
            }
        }
        $(document).ready(function() {
            $('#designation').select2({
                placeholder: 'Select Designations',  
                allowClear: true                    
            });
        });

        function validateForm(event) {
            event.preventDefault(); // Prevent form submission initially
            console.log("validating...")

            let isValid = true;
            const requiredFields = document.querySelectorAll('[required]');

            // Clear previous error messages
            document.querySelectorAll('.error-text').forEach(function (el) {
                el.remove();
            });

            // Validate all required fields
            requiredFields.forEach(function (field) {
                if (!field.value.trim()) {
                    isValid = false;
                    showError(field, `${field.previousElementSibling.innerText} is required.`);
                } else {
                    field.classList.remove('error');
                }
            });

            // Custom Validation: Contact Number
            const phoneNumber = document.getElementById('contact_number');
            if (!/^\d{10,12}$/.test(phoneNumber.value.trim())) {
                isValid = false;
                showError(phoneNumber, 'Contact Number must be 10 to 12 digits.');
            }

            // Custom Validation: TIN
            const tinField = document.getElementById('tin');
            if (!/^\d{9}$/.test(tinField.value.trim())) {
                isValid = false;
                showError(tinField, 'TIN must be exactly 9 digits.');
            }

            // Custom Validation: PAGIBIG
            const pagibigField = document.getElementById('pagibig');
            if (!/^\d{12}$/.test(pagibigField.value.trim())) {
                isValid = false;
                showError(pagibigField, 'PAGIBIG must be exactly 12 digits.');
            }

            // Submit the form if all validations pass
            if (isValid) {
                document.getElementById('userForm').submit();
            }
        }

        // Function to show error message below the input field
        function showError(field, message) {
            console.log(field);
            field.classList.add('error');
            const errorText = document.createElement('div');
            errorText.className = 'error-text';
            errorText.innerText = message;
            field.parentNode.appendChild(errorText);
        }

        // Attach validateForm function to form submission
        document.getElementById('submit-btn').addEventListener('click', validateForm);
    </script>
</body>
</html>
