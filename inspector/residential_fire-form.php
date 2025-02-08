<?php
    session_start();
    include '../db_connection.php';
    
    $user_id = $_SESSION['user_id'];
    
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index/index.php");
        exit();
    }

    $sql = "SELECT COUNT(*) AS waiting_count
        FROM applications 
        INNER JOIN inspections ON inspections.application_id = applications.id
        WHERE inspections.status = 0";

    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $waitingCount = $row['waiting_count']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Sidebar - Enhanced</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
  
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <nav class="menu" role="navigation">
            <a href="inspector.php" class="menu-item">
                <i class="fas fa-house-chimney"></i> 
                <span>Home</span>
            </a>
            <div class="menu-item clients">
                <a href="iclients.php">
                    <i class="fas fa-user-tie" style="color: #FFFFFF; margin-right: 15px"></i>
                    <span style="color: #FFFFFF;">Clients</span>
                    <!-- Notification badge using a div -->
                    <?php if ($waitingCount > 0): ?>
                        <div class="notification-badge"><?= $waitingCount ?></div>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu">
                    <a href="fsic-fsec_report.php" class="dropdown-item">
                        <i class="fas fa-clipboard"></i>
                        <span>Reports</span>
                    </a>
                </div>
            </div>
            <div class="menu-item clients">
                <a href="equipments.php" class="list">
                    <i class="fas fa-tools"></i> 
                    <span>Equipment</span>
                </a>
                <a href="equipment-form.php" class="add-link">
                    <i class="fas fa-circle-plus"></i> 
                    <span>Add</span>
                </a>
            </div>
            <div class="menu-item clients active">
                <a href="incidents.php" class="list">
                    <i class="fas fa-fire-alt"></i>
                    <span>Fire Incidents</span>
                </a>
                <a href="residential_fire-form.php" class="add-link" style="margin-left: 17px;">
                    <i class="fas fa-circle-plus"></i> 
                    <span>Add</span>
                </a>
                <div class="dropdown-menu">
                    <a href="incidents.php" class="dropdown-item">
                        <i class="fas fa-list-ul"></i>
                        <span>Incidents List</span>
                    </a>
                    <a href="incident_reports.php" class="dropdown-item">
                        <i class="fas fa-clipboard"></i>
                        <span>Reports</span>
                    </a>
                </div>
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
                Logout
            </a>
        </nav>
    </header>

    <div class="content" id="mainContent">
        <h2>Residential Fire Incident Form</h2>
        <form action="submit_incident.php" method="post" id="incidentForm">
        <div class="form-group">
            <label for="incident_date">Incident Date:</label>
            <input type="date" id="incident_date" name="incident_date" required>
        </div>
        <div class="form-group">
            <label for="time">Time:</label>
            <input type="time" id="time" name="time" required>
        </div>
        <div class="form-group">
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required>
        </div>
        <div class="form-group">
            <label for="owner_occupant">Owner/Occupant:</label>
            <input type="text" id="owner_occupant" name="owner_occupant" required>
        </div>
        <div class="form-group">
            <label for="occupancy_type">Occupancy Type:</label>
            <select id="occupancy_type" name="occupancy_type" required>
                <option value="">------</option>
                <option value="Assembly">Assembly</option>
                <option value="Educational">Educational</option>
                <option value="Daycare">Daycare</option>
                <option value="Healthcare">Healthcare</option>
                <option value="Residential Board & Care">Residential Board & Care</option>
                <option value="Detention & Correctional">Detention & Correctional</option>
                <option value="Hotel">Hotel</option>
                <option value="Dormitories">Dormitories</option>
                <option value="Apartment Building">Apartment Building</option>
                <option value="Lodging & Rooming House">Lodging & Rooming House</option>
                <option value="Single & Two Family Dwelling Unit">Single & Two Family Dwelling Unit</option>
                <option value="Mercantile">Mercantile</option>
                <option value="Business">Business</option>
                <option value="Industrial">Industrial</option>
                <option value="Storage">Storage</option>
                <option value="Special Structures">Special Structures</option>
            </select>
        </div>
        <div class="form-group">
            <label for="cause_of_fire">Cause of Fire:</label>
            <input type="text" id="cause_of_fire" name="cause_of_fire">
        </div>
        <div class="form-group">
            <label for="estimated_damages">Estimated Damages (PHP):</label>
            <input type="number" id="estimated_damages" name="estimated_damages" min="0" step="0.01">
        </div>
        <div class="form-group">
            <label for="casualties_injuries">Casualties/Injuries:</label>
            <textarea id="casualties_injuries" name="casualties_injuries" rows="1"></textarea>
        </div>
        <div class="form-group">
            <label for="fire_control_time">Fire Control Time:</label>
            <input type="time" id="fire_control_time" name="fire_control_time">
        </div>
        <div class="form-group">
            <label for="inspector_in_charge">Inspector In Charge:</label>
            <input type="text" id="inspector_in_charge" name="inspector_in_charge">
        </div>
        <div class="form-group">
            <label for="investigation_report_date">Investigation Report Date:</label>
            <input type="date" id="investigation_report_date" name="investigation_report_date">
        </div>
        <div class="form-group">
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="">------</option>
                <option value="For Investigation">For Investigation</option>
                <option value="Under Investigation">Under Investigation</option>
                <option value="Investigation Completed">Investigation Completed</option>
                <option value="Closed">Closed</option>
            </select>
        </div>
        <div class="button-container">
            <button type="submit">Save</button>
            <button type="button" onclick="clearForm()">Clear Form</button>
        </div>
    </form>
</div>

    <script src="../sidebar/sidebar-toggle.js"></script>
    <script>
        function clearForm() {
            document.getElementById('incidentForm').reset();
        }

        function validateIncidentForm(event) {
            event.preventDefault(); 
            let isValid = true;

            document.querySelectorAll('.error-text').forEach(function (el) {
                el.remove();
            });

            const requiredFields = document.querySelectorAll('[required]');
            requiredFields.forEach(function (field) {
                if (!field.value.trim()) {
                    isValid = false;
                    showError(field, `${field.previousElementSibling.innerText} is required.`);
                }
            });

            const estimatedDamagesField = document.getElementById('estimated_damages');
            if (estimatedDamagesField && estimatedDamagesField.value < 0) {
                isValid = false;
                showError(estimatedDamagesField, 'Estimated Damages must be a positive value.');
            }

            const incidentDate = document.getElementById('incident_date');
            const investigationReportDate = document.getElementById('investigation_report_date');
            if (incidentDate && investigationReportDate && investigationReportDate.value <= incidentDate.value) {
                isValid = false;
                showError(investigationReportDate, 'Investigation Report Date must be after Incident Date.');
            }

            if (isValid) {
                document.getElementById('incidentForm').submit();
            }
        }

        function showError(field, message) {
            field.classList.add('error');  
            const errorText = document.createElement('div');
            errorText.className = 'error-text';
            errorText.innerText = message;  
            field.parentNode.appendChild(errorText);  
        }

        document.getElementById('submit-btn').addEventListener('click', validateIncidentForm);
    </script>
</body>
</html>
