<?php
// Include database connection
include '../db_connection.php';
session_start();

// Fetch user data
$sql = "SELECT id, account_number, item_number, rank, first_name, middle_name, last_name, suffix, contact_number, date_of_birth, marital_status, gender, complete_address, religion, tin, pagibig, gsis, philhealth, tertiary_courses, post_graduate_courses, highest_eligibility, highest_training, specialized_training, date_entered_other_gov_service, date_entered_fire_service, mode_of_entry, date_of_last_promotion, appointment_status, unit_code, unit_assignment, designation FROM personnel";
$result = $conn->query($sql);

// Handle update logic if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_id'])) {
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

    // Collecting all input fields
    $id = $_POST['update_user_id'];
    $account_number = $_POST['account_number'];
    $item_number = $_POST['item_number'];
    $rank = $_POST['rank'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $suffix = $_POST['suffix'];
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
    $post_graduate_courses = $_POST['post_graduate_courses'];
    $highest_eligibility = $_POST['highest_eligibility'];
    $highest_training = $_POST['highest_training'];
    $specialized_training = $_POST['specialized_training'];
    $date_entered_other_gov_service = $_POST['date_entered_other_gov_service'];
    $date_entered_fire_service = $_POST['date_entered_fire_service'];
    $mode_of_entry = $_POST['mode_of_entry'];
    $date_of_last_promotion = $_POST['date_of_last_promotion'];
    $appointment_status = $_POST['appointment_status'];
    $unit_code = $_POST['unit_code'];
    $unit_assignment = $_POST['unit_assignment'];
    $designations = isset($_POST['designation']) ? implode(", ", $_POST['designation']) : "";
    $imagePath = $filePath;

    $update_sql = "UPDATE personnel SET account_number=?, item_number=?, rank=?, first_name=?, middle_name=?, last_name=?, suffix=?, contact_number=?, date_of_birth=?, marital_status=?, gender=?, complete_address=?, religion=?, tin=?, pagibig=?, gsis=?, philhealth=?, tertiary_courses=?, post_graduate_courses=?, highest_eligibility=?, highest_training=?, specialized_training=?, date_entered_other_gov_service=?, date_entered_fire_service=?, mode_of_entry=?, date_of_last_promotion=?, appointment_status=?, unit_code=?, unit_assignment=?, designation=?";
    
    if ($imagePath !== null) {
        $update_sql .= ", image_path=?";
    }
    
    $update_sql .= " WHERE id=?";

    $stmt = $conn->prepare($update_sql);
    if ($imagePath !== null) {
        $stmt->bind_param(
            "sssssssssssssssssssssssssssssssi", 
            $account_number, $item_number, $rank, $first_name, $middle_name, 
            $last_name, $suffix, $contact_number, $date_of_birth, $marital_status, 
            $gender, $complete_address, $religion, $tin, $pagibig, $gsis, 
            $philhealth, $tertiary_courses, $post_graduate_courses, $highest_eligibility, 
            $highest_training, $specialized_training, $date_entered_other_gov_service, 
            $date_entered_fire_service, $mode_of_entry, $date_of_last_promotion, 
            $appointment_status, $unit_code, $unit_assignment, $designations, 
            $imagePath, $id
        );
    } else {
        $stmt->bind_param(
            "ssssssssssssssssssssssssssssssi", 
            $account_number, $item_number, $rank, $first_name, $middle_name, 
            $last_name, $suffix, $contact_number, $date_of_birth, $marital_status, 
            $gender, $complete_address, $religion, $tin, $pagibig, $gsis, 
            $philhealth, $tertiary_courses, $post_graduate_courses, $highest_eligibility, 
            $highest_training, $specialized_training, $date_entered_other_gov_service, 
            $date_entered_fire_service, $mode_of_entry, $date_of_last_promotion, 
            $appointment_status, $unit_code, $unit_assignment, $designations, $id
        );
    }

    if ($stmt->execute()) {
        $_SESSION['flash-msg'] = 'update';
        echo "User updated successfully!";
        header("Location: personnels.php");
        exit;
    } else {
        echo "Error updating user: " . $stmt->error;
    }
}

// Handle search functionality
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $sql .= " WHERE first_name LIKE ? OR rank LIKE ? OR unit_assignment LIKE ?";
    $stmt = $conn->prepare($sql);
    $likeSearch = "%{$search}%";
    $stmt->bind_param("sss", $likeSearch, $likeSearch, $likeSearch);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Minglanilla - Personnel</title>
    <?php include('../dataTables/dataTable-links.php') ?> 
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/personnels.css">
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
        <h2>BFP Minglanilla Personnel</h2>
        <div class="action-bar">

            <div class="action-select">
                <label for="actionSelect">Action:</label>
                <select id="actionSelect">
                    <option value="">Select an action</option>
                    <option value="export_pdf">Export via PDF</option>
                    <option value="export_excel">Export via Excel</option>
                    <option value="delete">Delete</option>
                </select>
                <button id="goButton">Go</button>
            </div>
            <div class="search-bar">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" placeholder="Search" oninput="debouncedSearch()">
            </div>

            <div class="filter-container">
                <!-- Filter Toggle Button -->
                <button class="filter-toggle" onclick="toggleFilterPanel()">
                    <i class="fas fa-filter"></i> Filters
                </button>
                <!-- Collapsible Filter Panel -->
                <div class="filter-panel" id="filterPanel">
                    <!-- Filter by Rank -->
                    <div class="filter-group">
                        <select id="rankFilter" onchange="filterTable('rank', this.value)">
                            <option value="all">All</option>
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
                    <!-- Filter by Designation -->
                    <div class="filter-group">
                        <select id="designationFilter" onchange="filterTable('designation', this.value)">
                            <option value="all">All</option>
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
                            <option value="EMS Driver">EMS Driver</option>
                            <option value="Duty Investigation Shift - B">Duty Investigation Shift - B</option>
                            <option value="Plan Evaluator">Plan Evaluator</option>
                            <option value="Public Information Officer">Public Information Officer</option>
                            <option value="Crew (Public Information)">Crew (Public Information)</option>
                            <option value="Fire Safety Inspector (Crew)">Fire Safety Inspector (Crew)</option>
                            <option value="Duty Investigation Shift - A">Duty Investigation Shift - A</option>
                        </select>
                    </div>
                    <!-- Clear Filters Button -->
                    <button class="clear-filters" onclick="clearFilters()">Clear</button>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table id="personnelTable" class="table table-striped nowrap">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes(this)"></th>
                        <th>Rank</th>
                        <th>Fullname</th>
                        <th>Contact Number</th>
                        <th>Designation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="personnelTableBody">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><input type="checkbox" name="action[]" value="<?= htmlspecialchars($row['id']) ?>"></td>
                                <td><?php echo htmlspecialchars($row['rank']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                <td>
                                    <select onchange="handleAction(this, <?php echo $row['id']; ?>)" style="padding: 5px; border-radius: 4px;">
                                        <option value="">Select</option>
                                        <option value="view">View</option>
                                        <option value="edit">Update</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

       
        <div id="viewUserModal" class="personnel-modal" style="display: none;">
    <!-- Modal Header -->
    <div class="personnel-header">
        <span class="close-btn" onclick="closeModal('viewUserModal')">&times;</span>
        <div class="header-content">
            <div class="profile-image">
                <img src="../img/default.png" alt="Profile Picture" id="profilePicture">
            </div>
            <div class="profile-info">
                <h2 id="userName">John Doe</h2>
                <p id="userDesignation">Senior Fire Officer</p>
            </div>
        </div>
    </div>

    <!-- Scrollable Content -->
    <div class="profile-body">
        <section>
            <h3>Personal Information</h3>
            <ul id="userInfo" class="info-list"></ul>
        </section>
        <section>
            <h3>Employment Details</h3>
            <ul id="employmentDetails" class="info-list"></ul>
        </section>
        <section>
            <h3>Government Records</h3>
            <ul id="govRecords" class="info-list"></ul>
        </section>
        <section>
            <h3>Trainings and Education</h3>
            <ul id="trainingsAndEducation" class="info-list"></ul>
        </section>
    </div>

    <!-- Modal Footer -->
    <div class="profile-footer">
        <button id="closeUserProfileButton" onclick="closeModal('viewUserModal')">Close</button>
    </div>
</div>

    <div id="updateUserModal" class="profile-modal" style="display: none;">
            <div class="profile-header">
                <h6>Update Personnel</h6>
                <span class="close-btn" onclick="closeModal('updateUserModal')">&times;</span>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="profile-body">
                    <input type="hidden" name="update_user_id" id="update_user_id">

                    <div class="image-upload-container">
                        <img id="image_preview" alt="upload profile picture">
                        <input type="file" name="profile_image" accept="image/*" onchange="previewImage(event)" style="font-size: 0.7em">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Account Number:</label>
                            <input type="text" name="account_number" id="account_number">
                        </div>
                        <div class="form-group">
                            <label>Item Number:</label>
                            <input type="text" name="item_number" id="item_number">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Rank:</label>
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
                            <label>First Name:</label>
                            <input type="text" name="first_name" id="first_name">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Middle Name:</label>
                            <input type="text" name="middle_name" id="middle_name">
                        </div>
                        <div class="form-group">
                            <label>Last Name:</label>
                            <input type="text" name="last_name" id="last_name">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Suffix:</label>
                            <input type="text" name="suffix" id="suffix">
                        </div>
                        <div class="form-group">
                            <label>Contact Number:</label>
                            <input type="text" name="contact_number" id="contact_number">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Date of Birth:</label>
                            <input type="date" name="date_of_birth" id="date_of_birth">
                        </div>
                        <div class="form-group">
                            <label>Marital Status:</label>
                            <select id="marital_status" name="marital_status" required>
                                <option value="">Select Marital Status</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="widowed">Widowed</option>
                                <option value="divorced">Divorced</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Gender:</label>
                            <select id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Complete Address:</label>
                            <input type="text" name="complete_address" id="complete_address">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Religion:</label>
                            <input type="text" name="religion" id="religion">
                        </div>
                        <div class="form-group">
                            <label>TIN:</label>
                            <input type="text" name="tin" id="tin">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>PAGIBIG:</label>
                            <input type="text" name="pagibig" id="pagibig">
                        </div>
                        <div class="form-group">
                            <label>GSIS:</label>
                            <input type="text" name="gsis" id="gsis">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>PhilHealth:</label>
                            <input type="text" name="philhealth" id="philhealth">
                        </div>
                        <div class="form-group">
                            <label>Tertiary Courses:</label>
                            <input type="text" name="tertiary_courses" id="tertiary_courses">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Post Graduate Courses:</label>
                            <input type="text" name="post_graduate_courses" id="post_graduate_courses">
                        </div>
                        <div class="form-group">
                            <label>Highest Eligibility:</label>
                            <input type="text" name="highest_eligibility" id="highest_eligibility">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Highest Training:</label>
                            <input type="text" name="highest_training" id="highest_training">
                        </div>
                        <div class="form-group">
                            <label>Specialized Training:</label>
                            <input type="text" name="specialized_training" id="specialized_training">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date Entered Other Gov Service:</label>
                            <input type="date" name="date_entered_other_gov_service" id="date_entered_other_gov_service">
                        </div>
                        <div class="form-group">
                            <label>Date Entered Fire Service:</label>
                            <input type="date" name="date_entered_fire_service" id="date_entered_fire_service">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Mode of Entry:</label>
                            <input type="text" name="mode_of_entry" id="mode_of_entry">
                        </div>
                        <div class="form-group">
                            <label>Date of Last Promotion:</label>
                            <input type="date" name="date_of_last_promotion" id="date_of_last_promotion">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Appointment Status:</label>
                            <select id="appointment_status" name="appointment_status" required>
                                <option value="">Select Appointment Status</option>
                                <option value="permanent">Permanent</option>
                                <option value="temporary">Temporary</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Unit Code:</label>
                            <input type="text" name="unit_code" id="unit_code">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Unit Assignment:</label>
                            <input type="text" name="unit_assignment" id="unit_assignment">
                        </div>
                        <div class="form-group">
                            <label>Designation:</label>
                            <select id="designation" name="designation[]" multiple required>
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
                    </div>
                    <div class="profile-footer">
                        <button type="submit" class="btn">Update</button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal('updateUserModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    
    <?php
    if(isset($_SESSION['flash-msg'])){
        if($_SESSION['flash-msg'] == 'update'){
            echo "
            <script>
                Swal.fire({
                    title: 'Success!',
                    text: 'User updated successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = 'personnels.php';
                    }
                });
            </script>
            ";
        }else{
            echo "
            <script>
                Swal.fire({
                    title: 'Success!',
                    text: 'User added successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = 'personnels.php';
                    }
                });
            </script>
            ";
        }
        unset($_SESSION['flash-msg']);
    }
    ?>


    <script>
        function toggleAllCheckboxes(master) {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => checkbox.checked = master.checked);
        }

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
        function previewImage(event) {
            const image = document.getElementById("image_preview");
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

       // Toggle the visibility of the filter panel
        function toggleFilterPanel() {
            const filterPanel = document.getElementById('filterPanel');
            filterPanel.style.display = (filterPanel.style.display === 'none' || filterPanel.style.display === '') ? 'flex' : 'none';
        }

        // Current filter states
        let currentFilters = {
            rank: 'all',
            designation: 'all'
        };

        // Clear all filters
        function clearFilters() {
            document.getElementById('rankFilter').value = 'all';
            document.getElementById('designationFilter').value = 'all';

            currentFilters.rank = 'all';
            currentFilters.designation = 'all';

            filterTable(); // Reset table display
        }

        // Filter table rows based on selected filters
        function filterTable(filterType, filterValue) {
            if (filterType) {
                currentFilters[filterType] = filterValue;
            }

            const rows = document.querySelectorAll('#personnelTableBody tr');

            rows.forEach(row => {
                const rank = row.cells[1].textContent.trim(); // Extract rank from the 2nd column
                const designation = row.cells[4].textContent.trim(); // Extract designation from the 5th column

                const matchesRank = currentFilters.rank === 'all' || rank === currentFilters.rank;
                const matchesDesignation = currentFilters.designation === 'all' || designation === currentFilters.designation;

                row.style.display = matchesRank && matchesDesignation ? '' : 'none';
            });
        }


         // Initialize DataTable with responsive and custom configurations (new)
         $(document).ready(function() {
            const table = $('#personnelTable').DataTable({
                responsive: true,
                autoWidth: false,
                searching: false,
                paging: true,
                info: true
            });
        });

        // new
        function debouncedSearch() {
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(searchApplications, 300);
        }
    
        function searchApplications() {
            const input = document.getElementById("searchInput").value.toLowerCase();
            const table = document.getElementById("personnelTableBody");
            const rows = table.getElementsByTagName("tr");

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName("td");
                let found = false;

                for (let j = 1; j < cells.length; j++) { // Start from 1 to skip the checkbox
                    if (cells[j].innerText.toLowerCase().includes(input)) {
                        found = true;
                        break;
                    }
                }

                rows[i].style.display = found ? "" : "none"; // Show or hide row
            }
        }

        function handleAction(select, userId) {
    const action = select.value;

    if (action === 'view') {
        fetch(`fetch_user_details.php?id=${userId}`)
            .then((response) => response.json())
            .then((data) => {
                // Update profile picture
                const profilePicture = document.getElementById('profilePicture');
                profilePicture.src = data.image_path || '../img/default.png';

                // Update name and designation
                document.getElementById('userName').textContent = `${data.first_name} ${data.middle_name} ${data.last_name} ${data.suffix}`;
                document.getElementById('userDesignation').textContent = data.designation || 'No designation available';

                // Populate personal information
                document.getElementById('userInfo').innerHTML = `
                    <li><strong>Account Number:</strong> ${data.account_number}</li>
                    <li><strong>Date of Birth:</strong> ${data.date_of_birth}</li>
                    <li><strong>Marital Status:</strong> ${data.marital_status}</li>
                    <li><strong>Gender:</strong> ${data.gender}</li>
                    <li><strong>Address:</strong> ${data.complete_address}</li>
                    <li><strong>Religion:</strong> ${data.religion}</li>
                `;

                // Populate employment details
                document.getElementById('employmentDetails').innerHTML = `
                    <li><strong>Unit Assignment:</strong> ${data.unit_assignment}</li>
                    <li><strong>Date of Last Promotion:</strong> ${data.date_of_last_promotion}</li>
                    <li><strong>Mode of Entry:</strong> ${data.mode_of_entry}</li>
                    <li><strong>Appointment Status:</strong> ${data.appointment_status}</li>
                `;

                // Populate government records
                document.getElementById('govRecords').innerHTML = `
                    <li><strong>TIN:</strong> ${data.tin}</li>
                    <li><strong>PAGIBIG:</strong> ${data.pagibig}</li>
                    <li><strong>GSIS:</strong> ${data.gsis}</li>
                    <li><strong>PhilHealth:</strong> ${data.philhealth}</li>
                `;

                // Populate trainings and education
                document.getElementById('trainingsAndEducation').innerHTML = `
                    <li><strong>Tertiary Courses:</strong> ${data.tertiary_courses}</li>
                    <li><strong>Post Graduate Courses:</strong> ${data.post_graduate_courses}</li>
                    <li><strong>Specialized Training:</strong> ${data.specialized_training}</li>
                `;

                // Show the modal
                document.getElementById('viewUserModal').style.display = 'flex';
            });
            } else if (action === 'edit') {
                // Fetch data for updating
                fetch(`fetch_user_details.php?id=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        const imagePreview = document.getElementById('image_preview');
                        imagePreview.src = data.image_path ? data.image_path : 'img/default.png';
                        document.getElementById('update_user_id').value = data.id;
                        document.getElementById('account_number').value = data.account_number;
                        document.getElementById('item_number').value = data.item_number;
                        const rankSelect = document.getElementById('rank');
                        rankSelect.value = data.rank;
                        Array.from(rankSelect.options).forEach(option => {
                            option.selected = option.value === data.rank;
                        });
                        document.getElementById('first_name').value = data.first_name;
                        document.getElementById('middle_name').value = data.middle_name;
                        document.getElementById('last_name').value = data.last_name;
                        document.getElementById('suffix').value = data.suffix;
                        document.getElementById('contact_number').value = data.contact_number;
                        document.getElementById('date_of_birth').value = data.date_of_birth;
                        const maritalStatusSelect = document.getElementById('marital_status');
                        maritalStatusSelect.value = data.marital_status; 
                        Array.from(maritalStatusSelect.options).forEach(option => {
                            option.selected = option.value === data.marital_status;
                        });
                        const genderSelect = document.getElementById('gender');
                        genderSelect.value = data.gender; 
                        Array.from(genderSelect.options).forEach(option => {
                            option.selected = option.value === data.gender;
                        });
                        document.getElementById('complete_address').value = data.complete_address;
                        document.getElementById('religion').value = data.religion;
                        document.getElementById('tin').value = data.tin;
                        document.getElementById('pagibig').value = data.pagibig;
                        document.getElementById('gsis').value = data.gsis;
                        document.getElementById('philhealth').value = data.philhealth;
                        document.getElementById('tertiary_courses').value = data.tertiary_courses;
                        document.getElementById('post_graduate_courses').value = data.post_graduate_courses;
                        document.getElementById('highest_eligibility').value = data.highest_eligibility;
                        document.getElementById('highest_training').value = data.highest_training;
                        document.getElementById('specialized_training').value = data.specialized_training;
                        document.getElementById('date_entered_other_gov_service').value = data.date_entered_other_gov_service;
                        document.getElementById('date_entered_fire_service').value = data.date_entered_fire_service;
                        document.getElementById('mode_of_entry').value = data.mode_of_entry;
                        document.getElementById('date_of_last_promotion').value = data.date_of_last_promotion;
                        document.getElementById('appointment_status').value = data.appointment_status;
                        document.getElementById('unit_code').value = data.unit_code;
                        document.getElementById('unit_assignment').value = data.unit_assignment;
                        const designationSelect = document.getElementById('designation');
                        const selectedDesignations = data.designation.split(', ').map(designation => designation.trim());
                        Array.from(designationSelect.options).forEach(option => {
                            option.selected = selectedDesignations.includes(option.value);
                        });
                        document.getElementById('updateUserModal').style.display = 'block';
                    });
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function toggleSelectAll(selectAllCheckbox) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        function navigateTo(url, title) {
            window.location.href = url;
            document.title = title;
        }

        document.getElementById('goButton').addEventListener('click', function() {
            const action = document.getElementById('actionSelect').value;
            
            if (action === 'export_pdf') {
                const checkedCheckboxes = document.querySelectorAll('input[name="action[]"]:checked');

                const personnelIds = [];
                checkedCheckboxes.forEach(function(checkbox) {
                    personnelIds.push(checkbox.value);
                });

                if (personnelIds.length > 0) {
                    window.location.href = 'export_personnel.php?action=pdf&personnelIds=' + encodeURIComponent(personnelIds.join(','));
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "No Selection",
                        text: "Please select at least one personnel to update.",
                    });
                }
            } else if (action === 'export_excel'){
                const checkedCheckboxes = document.querySelectorAll('input[name="action[]"]:checked');

                const personnelIds = [];
                checkedCheckboxes.forEach(function(checkbox) {
                    personnelIds.push(checkbox.value);
                });

                if (personnelIds.length > 0) {
                    window.location.href = 'export_personnel.php?action=excel&personnelIds=' + encodeURIComponent(personnelIds.join(','));
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "No Selection",
                        text: "Please select at least one personnel to update.",
                    });
                }
            } else if (action === 'delete'){
                const checkedCheckboxes = document.querySelectorAll('input[name="action[]"]:checked');

                const personnelIds = [];
                checkedCheckboxes.forEach(function(checkbox) {
                    personnelIds.push(checkbox.value);
                });

                if (personnelIds.length > 0) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '../ajax.php?action=delete_personnel',
                                method: 'POST',
                                data: {
                                    personnelIds: personnelIds
                                },
                                success: function(data) {
                                    console.log('Response:', data);
                                    if (data === 'success') {
                                        Swal.fire(
                                            'Deleted!',
                                            'The selected personnel have been deleted.',
                                            'success'
                                        ).then(() => {
                                            window.location.reload(); 
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: "error",
                                            title: "Oops...",
                                            text: "Something went wrong with the deletion!",
                                        });
                                    }
                                }
                            });
                        }
                    })
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "No Selection",
                        text: "Please select at least one personnel to delete.",
                    });
                }
                
            } else {
                Swal.fire({
                    icon: "warning",
                    title: "No Action Selected",
                    text: "Please select an action from the dropdown.",
                });
            }
        });
        
    </script>
</body>
</html>
