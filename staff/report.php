<?php
    session_start();
    include '../db_connection.php';

    $user_id = $_SESSION['user_id'];

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index/index.php");
        exit();
    }

    $query = "SELECT application_type, COUNT(*) as count 
            FROM applications 
            GROUP BY application_type";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $type_data = [
        'building' => 0,
        'occupancy' => 0,
        'new_business_permit' => 0,
        'renewal_business_permit' => 0
    ];
    $applicant_count = 0;

    while ($row = $result->fetch_assoc()) {
        $application_type = $row['application_type'];
        if (isset($type_data[$application_type])) {
            $type_data[$application_type] = (int)$row['count'];
            $applicant_count += (int)$row['count'];
        }
    }

    $type_json = json_encode(array_values($type_data)); 

    $query = "SELECT issuance_status, COUNT(*) as count 
        FROM applications 
        GROUP BY issuance_status";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $status_data = [
        'Pending' => 0,
        'Completed' => 0
    ];

    while ($row = $result->fetch_assoc()) {
        $issuance_status = $row['issuance_status'];
        if (isset($status_data[$issuance_status])) {
            $status_data[$issuance_status] = (int)$row['count'];
        }
    }

    $status_json = json_encode(array_values($status_data)); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Sidebar - Enhanced</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <?php include('../dataTables/dataTable-links.php')?>
    <!-- <link rel="stylesheet" href="../css/style.css"> -->
     <link rel="stylesheet" href="../css/style.css">
    <style>
        .table-con{
            margin-top: 50px;
        }
        .table-container{
            box-shadow: none;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <!-- Logo Section -->
        <nav class="menu" role="navigation">
            <a href="staff.php" class="menu-item">
                <i class="fas fa-house-chimney"></i> 
                <span>Home</span>
            </a>
            <div class="menu-item clients">
                <a href="application-list.php" class="list">
                    <i class="fas fa-user-friends"></i> 
                    <span>Clients</span>
                </a>
                <a href="application-form.php" class="add-link">
                    <i class="fas fa-circle-plus"></i> 
                    <span>Add</span>
                </a>
            </div>
            <a href="report.php" class="menu-item clients active">
                <i class="fas fa-chart-pie"></i> 
                <span>Reports</span>
            </a>
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

        <h2>FSIC & FSEC Reports</h2>

        <div class="action-bar">
            <div class="action-select">
                <label for="actionSelect">Action:</label>
                <select id="actionSelect">
                    <option value="">Select an action</option>
                    <option value="export">Export</option>
                </select>
                <button id="goButton">Go</button>
            </div>
        </div>

        <div class="table-con">
            <table id="applicationTable" class="table table-striped nowrap">
                <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>Report Title</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="checkbox"></td>
                        <td>Building/Occupancy Report</td>
                        <td>
                            <a href="../export_building.php?action=view" target="_blank"><button onclick="viewReport('Bulding Report')" class="view">View</button></a>
                            <div class="dropdown-export">
                                <button>Export</button>
                                <div class="dropdown-export-content">
                                    <a href="../export_building.php?action=pdf"><button onclick="exportReport('pdf', 'Bulding Report')">PDF</button></a>
                                    <a href="../export_building.php?action=excel"><button onclick="exportReport('excel', 'Bulding Report')">Excel</button></a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><input type="checkbox"></td>
                        <td>New/Renewal Business Report</td>
                        <td>
                            <a href="../export_business.php?action=view" target="_blank"><button onclick="viewReport('Business Report')"class="view">View</button></a>
                            <div class="dropdown-export">
                                <button>Export</button>
                                <div class="dropdown-export-content">
                                    <a href="../export_business.php?action=pdf"><button onclick="exportReport('pdf', 'Business Report')">PDF</button></a>
                                    <a href="../export_business.php?action=excel"><button onclick="exportReport('excel', 'Business Report')">Excel</button></a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="exportModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5>Select Export Format</h5>
            <span class="close" id="closeModal">&times;</span>
        </div>
        <div class="modal-body">
            <p class="modal-text">Please choose the format for exporting your selected reports:</p>
            <div class="export-buttons">
                <button class="btn export-btn" data-format="pdf">
                    <i class="fas fa-file-pdf"></i> Export as PDF
                </button>
                <button class="btn export-btn" data-format="excel">
                    <i class="fas fa-file-excel"></i> Export as Excel
                </button>
            </div>
        </div>
    </div>
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

        function viewReport(reportTitle) {
            alert(`Viewing report: ${reportTitle}`);
        }

        function exportReport(type, reportTitle) {
            alert(`Exporting ${reportTitle} as ${type.toUpperCase()}`);
        }

        $(document).ready(function() {
        const table = $('#applicationTable').DataTable({
            responsive: true,
            autoWidth: false,
            searching: true,
            paging: true,
            info: true
        });
    });
        // Select/Deselect All Checkbox Logic
document.querySelector('thead input[type="checkbox"]').addEventListener('change', function () {
    const isChecked = this.checked;
    document.querySelectorAll('tbody input[type="checkbox"]').forEach(checkbox => {
        checkbox.checked = isChecked;
    });
});

        // Open the Export Modal
document.getElementById('goButton').addEventListener('click', () => {
    const actionSelect = document.getElementById('actionSelect');
    const action = actionSelect.value;

    if (action === 'export') {
        const selectedRows = Array.from(document.querySelectorAll('tbody tr'))
            .filter(row => row.querySelector('input[type="checkbox"]').checked);

        if (selectedRows.length === 0) {
            alert('Please select at least one report to export.');
            return;
        }

        // Open the modal
        document.getElementById('exportModal').style.display = 'flex';

        // Store selected rows in a global variable
        window.selectedRows = selectedRows;
    } else {
        alert('Please select a valid action.');
    }
});

// Close the Export Modal
document.getElementById('closeModal').addEventListener('click', () => {
    document.getElementById('exportModal').style.display = 'none';
});

// Handle Export Buttons
document.querySelectorAll('.export-btn').forEach(button => {
    button.addEventListener('click', (event) => {
        const format = event.target.getAttribute('data-format'); // Get export format

        // Export logic for each selected row
        window.selectedRows.forEach(row => {
            const reportTitle = row.cells[1].innerText; // Get the report title
            const reportType = reportTitle.includes('Building') ? 'building' : 'business';
            const exportUrl = `../export_${reportType}.php?action=${format}`;
            window.open(exportUrl, '_blank');
        });

        // Close the modal
        document.getElementById('exportModal').style.display = 'none';
    });
});


    </script>
</body>
</html>
        