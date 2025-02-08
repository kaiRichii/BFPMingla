<?php
session_start();
include '../db_connection.php';

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $equipment_name = trim($_POST['equipment_name']);
    $quantity = (int) $_POST['quantity'];
    $last_maintenance_date = $_POST['last_maintenance_date'];
    $next_maintenance_date = $_POST['next_maintenance_date'];
    $status = $_POST['status'];
    $notes = trim($_POST['notes']);

    $stmt = $conn->prepare("INSERT INTO equipment (equipment_name, quantity, last_maintenance_date, next_maintenance_date, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissss", $equipment_name, $quantity, $last_maintenance_date, $next_maintenance_date, $status, $notes);

    if ($stmt->execute()) {
        session_start();
        $_SESSION['flash-msg'] = 'success-equipment';
        header("Location: equipments.php"); 
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$sql = "SELECT COUNT(*) AS waiting_count
FROM applications 
INNER JOIN inspections ON inspections.application_id = applications.id
WHERE inspections.status = 0"; 

$result = $conn->query($sql);
$row = $result->fetch_assoc();
$waitingCount = $row['waiting_count']; 

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Sidebar - Enhanced</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
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
            <div class="menu-item clients active">
                <a href="equipments.php" class="list">
                    <i class="fas fa-tools"></i> 
                    <span>Equipment</span>
                </a>
                <a href="equipment-form.php" class="add-link">
                    <i class="fas fa-circle-plus"></i> 
                    <span>Add</span>
                </a>
            </div>
            <div class="menu-item clients">
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
        <h2>Firefighting Equipment Form</h2>
        <form action="" method="post" id="equipmentForm">
            <div class="form-group">
                <label for="equipment_name">Equipment Name:</label>
                <input type="text" id="equipment_name" name="equipment_name" required>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" min="1" required>
            </div>

            <div class="form-group">
                <label for="last_maintenance_date">Last Maintenance Date:</label>
                <input type="date" id="last_maintenance_date" name="last_maintenance_date" required>
            </div>

            <div class="form-group">
                <label for="next_maintenance_date">Next Maintenance Date:</label>
                <input type="date" id="next_maintenance_date" name="next_maintenance_date" required>
            </div>

            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="">------</option>
                    <option value="Good">Good</option>
                    <option value="Under Repair">Under Repair</option>
                    <option value="Needs Replacement">Needs Replacement</option>
                </select>
            </div>

            <div class="form-group">
                <label for="notes">Notes:</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
            </div>

            <div class="button-container">
                <button type="submit">SAVE</button>
                <button type="button" onclick="clearForm()">Clear Form</button> 
            </div>
        </form>
    </div>

    <script src="../sidebar/sidebar-toggle.js"></script>
    <script>
        function clearForm() {
            document.getElementById('equipmentForm').reset();
        }

        function validateEquipmentForm(event) {
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

            const quantityField = document.getElementById('quantity');
            if (quantityField && quantityField.value < 1) {
                isValid = false;
                showError(quantityField, 'Quantity must be at least 1.');
            }

            const lastMaintenanceDate = document.getElementById('last_maintenance_date');
            const nextMaintenanceDate = document.getElementById('next_maintenance_date');
            if (lastMaintenanceDate && nextMaintenanceDate && nextMaintenanceDate.value <= lastMaintenanceDate.value) {
                isValid = false;
                showError(nextMaintenanceDate, 'Next Maintenance Date must be after Last Maintenance Date.');
            }

            if (isValid) {
                document.getElementById('equipmentForm').submit();
            }
        }

        function showError(field, message) {
            field.classList.add('error');  
            const errorText = document.createElement('div');
            errorText.className = 'error-text';
            errorText.innerText = message;  
            field.parentNode.appendChild(errorText);  
        }

        document.getElementById('submit-btn').addEventListener('click', validateEquipmentForm);
    </script>
</body>
</html>
