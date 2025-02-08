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
    <title>BFP Minglanilla - FSIC/FSEC Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0-alpha3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .table-con{
            margin-top: 50px;
        }
         /* Dropdown Styles for Export */
         .dropdown-export {
            position: relative;
            display: inline-block;
        }
        

        .dropdown-export button, .view {
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            color: #333;
            padding: 8px 12px;
            cursor: pointer;
        }

        .dropdown-export-content {
            display: none;
            position: absolute;
            background-color: #333;
            min-width: 120px;
            border-radius: 4px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .table-container{
            box-shadow: none;
            border: none;
        }

        .dropdown-export-content button {
            color: #ffffff;
            padding: 8px 16px;
            text-decoration: none;
            display: block;
            width: 100%;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
        }

        .dropdown-export-content button:hover {
            background-color: #555;
        }

        .dropdown-export:hover .dropdown-export-content {
            display: block;
        }
        /* modal for export */
#exportModal {
    font-family: 'Poppins', sans-serif;
    display: none; /* Hidden by default */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Dark overlay */
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

#exportModal .modal-content {
    background: #fff;
    width: 90%;
    max-width: 400px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    animation: fadeIn 0.3s ease-out;
    padding: 20px 30px;
}

#exportModal .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 10px;
    border-bottom: 1px solid #e0e0e0;
}

#exportModal .modal-header h5 {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
}

#exportModal .modal-header .close {
    font-size: 1.5rem;
    color: #888;
    cursor: pointer;
    transition: color 0.3s ease;
}

#exportModal .modal-header .close:hover {
    color: #ff6363;
}

#exportModal .modal-body {
    text-align: center;
    margin: 20px 0;
}

#exportModal .modal-body .modal-text {
    font-size: 1rem;
    margin-bottom: 20px;
    color: #555;
}

#exportModal .modal-body .export-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
}

#exportModal .btn {
    background-color: #ff6363;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background-color 0.3s, transform 0.1s;
}

#exportModal .btn:hover {
    background-color: #b71c1c;
    transform: scale(1.02);
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10%);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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
            <div class="menu-item clients active">
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
            <div class="menu-item clients">
                <a href="residential_fire-form.php" class="list">
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

    <div id="exportModal" class="modal">
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


    <script src="../sidebar/sidebar-toggle.js"></script>
    <script>
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
        const format = event.target.getAttribute('data-format'); 

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
        