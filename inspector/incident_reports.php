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
    .report-container {
        max-width: 600px;
        margin: 50px auto;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
    }

    .report-header {
        padding: 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
        font-size: 18px;
        color: #333333;
        text-align: center;
    }

    .filters-container {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .date-pickers {
        display: flex;
        gap: 20px;
        justify-content: space-between;
    }

    .date-picker-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
        flex: 1;
    }

    .filter-label {
        font-size: 12px;
        font-weight: bold;
        color: #555555;
    }

    .filter-input {
        padding: 10px;
        font-size: 14px;
        border: 1px solid #ccd0d5;
        border-radius: 8px;
        background-color: #f8f9fa;
        transition: border-color 0.3s ease;
    }

    .filter-input:focus {
        border-color: #007BFF;
        outline: none;
    }

    .button-group {
        display: flex;
        justify-content: left;
        gap: 15px;
    }

    .btn {
        font-size: 14px;
        font-weight: 600;
        border-radius: 8px;
        padding: 10px 20px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.1s ease;
    }

    .btn-primary {
        background: #007BFF;
        color: white;
        border: none;
    }

    .btn-primary:hover {
        background: #0056b3;
    }

    .btn-secondary {
        background: #e4e6eb;
        color: #333333;
        border: none;
    }

    .btn-secondary:hover {
        background: #d8dadf;
    }

    .export-menu {
        position: relative;
    }

    .export-button {
        padding-right: 20px;
    }

    .export-options {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: #ffffff;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
        list-style: none;
        margin: 0;
        padding: 0;
        min-width: 150px;
        z-index: 10;
    }

    .export-options li {
        border-bottom: 1px solid #f0f0f0;
    }

    .export-options li:last-child {
        border-bottom: none;
    }

    .export-item {
        display: block;
        width: 100%;
        text-align: left;
        padding: 10px 15px;
        font-size: 14px;
        color: #333333;
        background: none;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .export-item:hover {
        background-color: #f8f9fa;
        color: #007BFF;
    }

    .export-menu:hover .export-options {
        display: block;
    }

    .report-footer {
        padding: 15px;
        background: #f8f9fa;
        text-align: center;
        font-size: 14px;
        font-weight: bold;
        color: #007BFF;
        border-top: 1px solid #ddd;
        cursor: pointer;
        transition: text-decoration 0.3s ease;
    }

    .report-footer:hover {
        text-decoration: underline;
    }
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
                    <a href="" class="dropdown-item">
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
        <h2>Residential Fire Incident Report</h2>

            <div class="report-container">
                <div class="report-header">Generate Incident Report</div>

                <div class="filters-container">
                    <div class="date-pickers">
                        <div class="date-picker-group">
                            <label for="fromDate" class="filter-label">From</label>
                            <input type="date" id="fromDate" class="filter-input">
                        </div>
                        <div class="date-picker-group">
                            <label for="toDate" class="filter-label">To</label>
                            <input type="date" id="toDate" class="filter-input">
                        </div>
                    </div>

                    <div class="button-group">
                        <button class="btn btn-primary" onclick="viewReport()">View</button>
                        <div class="export-menu">
                            <button class="btn btn-secondary export-button">Export</button>
                            <ul class="export-options">
                                <li><button class="export-item" onclick="exportReport('pdf')">PDF</button></li>
                                <li><button class="export-item" onclick="exportReport('excel')">Excel</button></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="report-footer" onclick="redirectToDashboard()"></div>
            </div>
        </div>
    </div>

    <script src="../sidebar/sidebar-toggle.js"></script>
    <script>
        function viewReport(reportTitle) {
            alert(`Viewing report: ${reportTitle}`);
        }

        function exportReport(type, reportTitle) {
            alert(`Exporting ${reportTitle} as ${type.toUpperCase()}`);
        }

        function getDateRangeParams() {
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;
            let params = '';
            if (fromDate) params += `&from=${fromDate}`;
            if (toDate) params += `&to=${toDate}`;
            return params;
        }

        function viewReport() {
            const params = getDateRangeParams();
            window.open(`export_fire.php?action=view${params}`, '_blank');
        }

        function exportReport(format) {
            const params = getDateRangeParams();
            window.location.href = `export_fire.php?action=${format}${params}`;
        }
    </script>
</body>
</html>
