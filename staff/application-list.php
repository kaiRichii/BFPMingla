<?php
include '../db_connection.php'; 

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index/index.php");
    exit();
}

$sql = "SELECT applications.id, application_type, owner_name, business_trade_name, address, contact_number, email_address, checklist, type, issuance_status, applications.created_at, schedule, full_name AS inspector, 
inspections.status AS inspection_status FROM applications 
LEFT JOIN inspections ON inspections.application_id = applications.id 
LEFT JOIN users ON inspections.inspector_id = users.id 
ORDER BY applications.created_at DESC";
$result = $conn->query($sql);

$applications = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['checklist'] = json_decode($row['checklist'], true); 
        $applications[] = $row;
    }
} else {
    echo "Error: " . $conn->error; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Minglanilla - FSIC/FSEC Clients</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <?php include('../dataTables/dataTable-links.php')?>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="staff-overview.css">
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
            <div class="menu-item clients active">
                <a href="application-list.php" class="list">
                    <i class="fas fa-user-friends"></i> 
                    <span>Clients</span>
                </a>
                <a href="application-form.php" class="add-link">
                    <i class="fas fa-circle-plus"></i> 
                    <span>Add</span>
                </a>
            </div>
            <a href="report.php" class="menu-item clients">
                <i class="fas fa-chart-pie"></i> 
                <span>Reports</span>
            </a>
        </nav>
        <footer class="sidebar-footer">
            <a href="profile.php" class="footer-item">
                <!-- <i class="fas fa-user-circle"></i> 
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
        <h2>Client List</h2>
        
        <div class="action-bar">
            <!-- Action Section for Delete -->
            <div class="action-select">
                <label for="actionSelect">Action:</label>
                <select id="actionSelect">
                    <option value="">Select an action</option>
                    <option value="delete">Delete</option>
                </select>
                <button id="goButton">Go</button>
            </div>

            <!-- Search Bar -->
            <div class="search-bar">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" placeholder="Search" oninput="debouncedSearch()">
            </div>
            
            <div class="filter-container">
                <!-- Filter Toggle Button -->
                <button class="filter-toggle" onclick="toggleFilterPanel()">
                    <i class="fas fa-filter"></i> Filter
                </button>

                <!-- Toggleable Filter Panel -->
                <div class="filter-panel" id="filterPanel">
                    <!-- Application Type Dropdown -->
                    <select id="applicationType" onchange="filterTable('application_type', this.value)">
                        <option value="" disabled selected>Application Type</option>
                        <option value="all">All</option>
                        <option value="building">Building</option>
                        <option value="occupancy">Occupancy</option>
                        <option value="new_business_permit">New Business Permit</option>
                        <option value="renewal_business_permit">Renewal Business Permit</option>
                    </select>

                    <!-- Status Dropdown -->
                    <select id="status" onchange="filterTable('status', this.value)">
                        <option value="" disabled selected>Status</option>
                        <option value="all">All</option>
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                    </select>

                    <input type="date" id="dateFilter" onchange="filterTable('date', this.value)" />

                    <!-- Clear Filters Button -->
                    <button class="clear-filters" onclick="clearFilters()">Clear</button>
                </div>
            </div>
            <!-- Add Client Button -->
            <a href="application-form.php" class="add-button">
                <i class="fas fa-plus"></i> Add Client
            </a>
        </div>

        <div class="table-container">
            <table id="applicationTable" class="table table-striped nowrap">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes(this)"></th>
                    <th>Application Type</th>
                    <th>Facility Name / Business or Trade Name</th>
                    <th>Owner Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                    <th>Address</th>
                    <th>Email</th>
                    <th>Inspector</th>
                    <th>Inspection Schedule</th>
                    <th style="display:none" class="created_at">Created At</th>
                </tr>
            </thead>
            <tbody id="applicationTableBody">
                <?php if (count($applications) > 0): ?>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><input type="checkbox" name="action[]" value="<?= htmlspecialchars($app['id']) ?>"></td>
                           
                            <td><?= htmlspecialchars($app['application_type']) ?></td>
                            <td><?= htmlspecialchars($app['business_trade_name']) ?></td>
                            <td><?= htmlspecialchars($app['owner_name']) ?></td>
                            <td><?= htmlspecialchars($app['issuance_status']) ?></td>
                            <td>
                                <select onchange="handleAction(this, <?= htmlspecialchars(json_encode($app['checklist'])) ?>, <?= htmlspecialchars($app['type']) ?>)" style="padding: 5px; border-radius: 4px; width:100%;">
                                    <option value="">Select</option>
                                    <option value="view">View</option>
                                    <option value="edit" <?php if ($app['issuance_status'] == 'Completed' || $app['inspection_status'] == 1 || $app['inspection_status'] >= 2) echo 'disabled'; ?>>Edit</option>
                                    <?php if($app['schedule'] == ''): ?>
                                        <option value="generateInspection">Generate Inspection</option>
                                    <?php endif; ?>
                                </select>
                            </td>
                            <td><?= htmlspecialchars($app['address']) ?></td>
                            <td><?= htmlspecialchars($app['email_address']) ?></td>
                            <td><?= $app['inspector'] == '' ? 'N/A' : htmlspecialchars($app['inspector']) ?></td>
                            <td><?= $app['schedule'] == '' ? 'N/A' : htmlspecialchars($app['schedule']) ?></td>
                            <td style="display:none" class="created_at"><?= htmlspecialchars($app['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">No applications found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
                </table>
        </div>

        <!-- <div id="viewClientModal" class="modal">
            <div class="modal-content">
            <div class="qr-code text-center">
                        <img src="" alt="QR Code" id="qrCodeImage" style="display: none;">
                    </div>
                <div class="modal-header">
                    <h5>Client Details</h5>
                    <span class="close" onclick="closeModal('viewClientModal')">&times;</span>
                </div>
                <div class="modal-body">
                    <h6>Client Information</h6>
                    <ul id="viewClientInfo"></ul>
                    <h6>Submitted Requirements</h6>
                    <ul id="submittedList"></ul>
                    <h6>Missing Requirements</h6>
                    <ul id="missingList"></ul>
                    <h6>History</h6>
                    <ul id="historyList"></ul>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeModal('viewClientModal')">Close</button>
                </div>
            </div>
        </div> -->

        <div id="viewClientModal" class="profile-modal" style="display: none;">
        <!-- Modal Header -->
            <div class="profile-header">
                <span class="close-btn" onclick="closeModal('viewClientModal')">&times;</span>
                <div class="header-content">
                    <div class="qr-code">
                        <img src="" alt="QR Code" id="qrCodeImage" style="display: none;">
                    </div>
                </div>
            </div>

            <!-- Scrollable Content -->
            <div class="profile-body">
                <section>
                    <h3>Client Information</h3>
                    <ul id="viewClientInfo" class="info-list"></ul>
                </section>
                <section>
                    <h3>Submitted Requirements</h3>
                    <ul id="submittedList" class="info-list"></ul>
                </section>
                <section>
                    <h3>Missing Requirements</h3>
                    <ul id="missingList" class="info-list"></ul>
                </section>
                <section>
                    <h3>History</h3>
                    <ul id="historyList" class="info-list"></ul>
                </section>
            </div>

            <!-- Footer -->
            <div class="profile-footer">
                <button id="closeClientProfileButton" onclick="closeModal('viewClientModal')">Close</button>
            </div>
        </div>


        <!-- <div id="editClientModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Edit Client Information</h5>
                    <span class="close" onclick="closeModal('editClientModal')">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="updateForm">
                        <input type="hidden" id="clientId" value="">
                        <input type="hidden" id="type" value="">

                        <div class="form-group">
                            <label for="applicationType">Application Type:</label>
                            <input type="text" name="applicationType" id="applicationType" required>
                        </div>
                        <div class="form-group">
                            <label for="facilityName">Facility Name:</label>
                            <input type="text" name="facilityName" id="facilityName" required>
                        </div>
                        <div class="form-group">
                            <label for="ownerName">Owner Name:</label>
                            <input type="text" name="ownerName" id="ownerName" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <input type="text"   id="status" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <input type="text" name="address" id="address" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" id="email" required>
                        </div>
                        <h6>Checklist Items:</h6>
                        <div id="checklistItems"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn" onclick="saveClient()">Save</button>
                    <button class="btn btn-secondary" onclick="closeModal('editClientModal')">Close</button>
                </div>
            </div>
        </div> -->
        <div id="editClientModal" class="profile-modal" style="display: none;">
            <!-- Modal Header -->
            <div class="profile-header">
                <span class="close-btn" onclick="closeModal('editClientModal')">&times;</span>
                <div class="header-content">
                    <h2>Edit Client Information</h2>
                </div>
            </div>

            <!-- Scrollable Content -->
            <div class="profile-body">
                <form id="updateForm">
                    <input type="hidden" id="clientId" value="">
                    <input type="hidden" id="type" value="">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="applicationType">Application Type:</label>
                            <input type="text" name="applicationType" id="applicationType" required>
                        </div>
                        <div class="form-group">
                            <label for="facilityName">Facility Name:</label>
                            <input type="text" name="facilityName" id="facilityName" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="ownerName">Owner Name:</label>
                            <input type="text" name="ownerName" id="ownerName" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <input type="text" id="status" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <input type="text" name="address" id="address" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" id="email" required>
                        </div>
                    </div>

                    <h6>Checklist Items:</h6>
                    <div id="checklistItems"></div>
                </form>
            </div>

            <!-- Footer -->
            <div class="profile-footer">
                <button class="btn" onclick="saveClient()">Save</button>
                <button class="btn btn-secondary" onclick="closeModal('editClientModal')">Close</button>
            </div>
        </div>

    <div id="generateInspectionModal" class="profile-modal" style="display: none;">
            <!-- Modal Header -->
            <div class="profile-header">
                <span class="close-btn" onclick="closeModal('generateInspectionModal')">&times;</span>
                <div class="header-content">
                    <h2>Generate Inspection</h2>
                </div>
            </div>

        <!-- Modal Body (Scrollable Content) -->
        <div class="profile-body">
            <form id="inspectionForm">
                <input type="hidden" id="clientId" value="">

                <div class="form-row">
                    <div class="form-group">
                        <label for="orderNumber">Inspection Order Number:</label>
                        <input type="text" name="orderNumber" id="orderNumber" required>
                    </div>
                    <div class="form-group">
                        <label for="assignedInspector">Assigned Inspector:</label>
                        <select name="assignedInspector" id="assignedInspector">
                            <option value="" selected disabled>Select Inspector</option>
                            <?php
                            $query = "SELECT id, full_name FROM users WHERE role = 'inspector'";
                            $result = $conn->query($query);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['full_name']) . '</option>';
                                }
                            } else {
                                echo '<option value="">No inspectors available</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                <div class="form-group">
                    <p style="font-size: 0.875rem; color: #888; text-align: center; margin-top: 10px;">
                        <em>If you wish to recheck the requirements, please click "Close".</em>
                    </p>
                </div>
            </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="profile-footer">
            <button class="btn" onclick="saveInspection()">Save</button>
            <button class="btn btn-secondary" onclick="closeModal('generateInspectionModal')">Close</button>
        </div>
    </div>


    <?php
    if (isset($_SESSION['complete']) && $_SESSION['complete'] === true && isset($_SESSION['appid'])) {
        $appId = $_SESSION['appid'];
        unset($_SESSION['complete'], $_SESSION['appid']);
    }
    ?>
    <script src="application-list.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
        <?php if (!empty($appId)) : ?>
            generateInspection(<?php echo json_encode($appId); ?>);
        <?php endif; ?>
        });

        function viewClient(applicationType, facilityName, ownerName, status, address, email, appId, checklist, type) {
        const viewInfoList = document.getElementById("viewClientInfo");
        viewInfoList.innerHTML = ""; // Clear previous details\
        let appLabel = 'Business/Trade Name'

        if(applicationType == 'building' || applicationType == 'occupancy'){
            appLabel = 'Structure/Facility Name'
        }

        const details = [
            { label: "Application Type", value: applicationType },
            { label: appLabel, value: facilityName },
            { label: "Owner Name", value: ownerName },
            { label: "Status", value: status },
            { label: "Address", value: address },
            { label: "Email", value: email },
            { label: "Application ID", value: appId },
        ];
        //qr
        const currentUrl = window.location.origin; 
        const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(currentUrl + "/bfpMingla/scanned_application.php?appid=" + appId)}&size=150x150`;
            document.getElementById('qrCodeImage').src = qrCodeUrl;
            document.getElementById('qrCodeImage').style.display = 'block';

        //details
        details.forEach(detail => {
            const listItem = document.createElement("li");
            listItem.innerHTML = `<strong>${detail.label}:</strong> ${detail.value}`;
            viewInfoList.appendChild(listItem);
        });

        //requirements
        const submittedList = document.getElementById("submittedList");
        const missingList = document.getElementById("missingList");
        submittedList.innerHTML = "";
        missingList.innerHTML = "";

        const expectedChecklist = applicationTypeData[type].checklist.map(item => item );
        
        expectedChecklist.forEach(expectedItem => {

            const actualItem = checklist.find(item => item === expectedItem.toLowerCase().replace(/\s+/g, '_').replace(/\./g, '_'));

            const requirementItem = document.createElement("li");
            requirementItem.textContent = expectedItem

            if (actualItem) {
                submittedList.appendChild(requirementItem);
            } else {
                missingList.appendChild(requirementItem);
            }
        });

        //history
        fetch(`../ajax.php?action=fetch_history&email=${encodeURIComponent(email)}`)
        .then(response => response.json())
        .then(history => {
            const historyList = document.getElementById("historyList");
            historyList.innerHTML = ""; 

            history.forEach(record => {
                const historyItem = document.createElement("li");
                historyItem.innerHTML = `<div style="display: flex; justify-content: space-between"><span> ${record.business_trade_name}</span> ${record.created_at}</span></div>`;
                historyList.appendChild(historyItem);
            });
        })
        .catch(error => console.error('Error fetching application history:', error));

        document.getElementById("viewClientModal").style.display = "block"; 
    }
    </script>
</body>
</html>
