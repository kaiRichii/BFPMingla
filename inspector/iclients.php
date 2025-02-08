<?php
include '../db_connection.php'; 
include '../function.php';
session_start();

$user_id = $_SESSION['user_id'];

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index/index.php");
        exit();
    }

$sql = "SELECT applications.id AS appid, application_type, owner_name, business_trade_name, address, contact_number, email_address, type, schedule, inspections.status AS inspection_status, 
checklist FROM applications 
INNER JOIN inspections ON inspections.application_id = applications.id 
INNER JOIN users ON inspections.inspector_id = users.id 
WHERE users.id = '".$_SESSION['user_id']."' ORDER BY applications.id DESC";
$result = $conn->query($sql);

$applications = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }
} else {
    echo "Error: " . $conn->error; 
}

// Query to get status counts
$statusSql = "SELECT status, COUNT(*) AS count 
              FROM inspections 
              WHERE inspector_id = '".$_SESSION['user_id']."' 
              GROUP BY status";
$statusResult = $conn->query($statusSql);

$statusCountData = [];
if ($statusResult) {
    while ($row = $statusResult->fetch_assoc()) {
        $statusCountData[$row['status']] = $row['count'];
    }
}

// Map the status values to their human-readable labels
$statusMap = [
    0 => 'Pending Inspection',
    1 => 'Inspected',
    2 => 'Waiting for Compliance',
    3 => 'Complied',
    4 => 'Notice to Comply Issued',
    5 => 'Notice to Correct Violation',
    6 => 'Issued Abandonment Order',
    7 => 'Issued Closure Order'
];

// Add 'All' status to the map
$statusMap['all'] = 'All';

$mappedStatusCountData = [];
// Ensure 'All' status is included in the count data
$mappedStatusCountData['All'] = array_sum($statusCountData); // Sum up all the counts for the "All" option
foreach ($statusCountData as $status => $count) {
    $statusLabel = $statusMap[$status] ?? 'Unknown';
    $mappedStatusCountData[$statusLabel] = $count;
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
    <title>BFP Minglanilla - FSIC/FSEC Clients</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <?php include('../dataTables/dataTable-links.php')?>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        
.profile-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 450px;
    max-height: 90vh;
    background: #ffffff; 
    border-radius: 8px; 
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    color: #333;
    z-index: 1000;
    animation: fadeIn 0.3s ease-in-out;
}
.profile-modal h2{
    border: none;
}


/* Profile Header */
.profile-header {
    background: #f7f7f7; 
    padding: 30px 25px 20px 25px; 
    border-bottom: 1px solid #e0e0e0; 
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    height: 90px; 
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.profile-header .close-btn {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 20px;
    color: #b71c1c; 
    cursor: pointer;
    transition: color 0.3s ease;
}

.profile-header .close-btn:hover {
    color: #a11a1a;
}

.profile-header .header-content {
    text-align: center;
}

.profile-header .profile-info h2 {
     margin: 0;
    font-size: 22px; 
    color: #333;
    font-weight: 600;
}

/* Profile Body (Scrollable) */
.profile-body {
    flex: 1;
    overflow-y: auto;
    padding: 25px;
    background: #fafafa; 
    max-height: calc(90vh - 180px); 
    overflow-x: hidden;
}
.profile-body section {
   margin-bottom: 25px;
}
/* Section Headers */
.profile-body section h3 {
    font-size: 16px;
    color: #333; 
    margin-bottom: 10px;
    border-bottom: 1px solid #e0e0e0; 
    padding-bottom: 8px;
    font-weight: 500;
}

/* Info List Styling */
.info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-list li {
    font-size: 0.9em;
    margin-bottom: 12px;
    color: #555;
    line-height: 1.6;
}

/* QR Code Styling */
.qr-code img {
    width: 120px;
    height: 120px;
    display: block;
    margin: 0 auto;
    margin-top: 50px;
    margin-bottom: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

/* Footer */
.profile-footer {
    background: #ffffff;
    padding: 20px 30px;
    text-align: right;
    border-top: 1px solid #e0e0e0; 
    height: 80px; 
    display: flex;
    justify-content: flex-end;
    align-items: center;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}

/* Footer Button Styling */
#closeClientProfileButton {
    background: #f2f2f2;
    color: #333;
    border: 1px solid #ddd;
    padding: 10px 15px;
    font-size: 0.8em;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s ease;
}

#closeClientProfileButton:hover {
    background: #e0e0e0;
}

/* Scrollbar Styling */
.profile-body::-webkit-scrollbar {
    width: 8px;
}

.profile-body::-webkit-scrollbar-thumb {
     background: #ccc; 
    border-radius: 4px;
}

.profile-body::-webkit-scrollbar-thumb:hover {
    background: #bbb;
}
/* Main Edit Client Modal */
#editClientModal, #updateStatusModal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 450px;
    max-height: 90vh;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); 
    display: flex;
    flex-direction: column;
    overflow: hidden;
    color: #333;
    z-index: 1000;
    animation: fadeIn 0.3s ease-in-out;
}

/* Profile Header */
#editClientModal .profile-header, #updateStatusModal .profile-header {
    background: #f7f7f7;
    padding: 20px 25px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    height: 80px; 
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

#editClientModal .profile-header .close-btn, #updateStatusModal .profile-header .close-btn {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 20px;
    color: #b71c1c; 
    cursor: pointer;
    transition: color 0.3s ease;
}

#editClientModal .profile-header .close-btn:hover, #updateStatusModal .profile-header .close-btn:hover {
      color: #a11a1a; 
}

#editClientModal .profile-header .header-content, #updateStatusModal .profile-header .header-content{
    text-align: center;
}

#editClientModal .profile-header h2, #updateStatusModal .profile-header h2 {
   margin: 0;
    font-size: 18px; 
    color: #333;
    font-weight: 600; 
}

/* Profile Body (Scrollable) */
#editClientModal .profile-body, #updateStatusModal .profile-body {
    flex: 1;
    overflow-y: auto;
    padding: 25px;
    background: #fafafa; 
    max-height: calc(90vh - 160px); 
}

/* Form Group Styling */

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

#editClientModal .form-group, #updateStatusModal .form-group {
    flex: 1;
}

#editClientModal .form-group label, #updateStatusModal .form-group {
    display: block;
    font-size: 14px;
    color: #333;
    margin-bottom: 8px;
    font-weight: 500;
}

#editClientModal .form-group input, #updateStatusModal .form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    color: #333;
    background-color: #f9f9f9;
    transition: border 0.3s ease, background-color 0.3s ease;
}

#editClientModal .form-group input:focus, #updateStatusModal .form-group select:focus {
    border-color: #b71c1c;
    background-color: #fff;
    outline: none;
}

/* Section Header */
#editClientModal h6 {
    font-size: 16px;
    color: #007bff;
    margin-bottom: 10px;
    font-weight: 500;
}

/* Footer */
#editClientModal .profile-footer, #updateStatusModal .profile-footer {
    background: #ffffff;
    padding: 20px 30px;
    text-align: right;
    border-top: 1px solid #e0e0e0;
    height: 80px;
    display: flex;
    gap: 5px;
    justify-content: flex-end;
    align-items: center;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}

#editClientModal .btn, #updateStatusModal .btn {
   background: #b71c1c;
    color: #fff;
    border: none;
    padding: 10px 15px;
    font-size: 0.8em;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
}

#editClientModal .btn:hover, #updateStatusModal .btn:hover {
     background: #9f1a1a;
}

#editClientModal .btn-secondary, #updateStatusModal .btn-secondary {
    background: #f2f2f2;
    color: #333;
    border: 1px solid #ddd;
}

#editClientModal .btn-secondary:hover, #updateStatusModal .btn-secondary:hover {
    background: #e0e0e0;
}

#editClientModal .profile-body::-webkit-scrollbar, #updateStatusModal .profile-body::-webkit-scrollbar {
    width: 8px;
}

#editClientModal .profile-body::-webkit-scrollbar-thumb, #updateStatusModal .profile-body::-webkit-scrollbar-thumb {
    background: #ccc; 
    border-radius: 4px
}

#editClientModal .profile-body::-webkit-scrollbar-thumb:hover, #updateStatusModal .profile-body::-webkit-scrollbar-thumb:hover {
      background: #bbb; 
}

.status-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-start;
    padding: 10px 0;
    margin: 0 auto;
}

.status-button {
    background-color: #f1f1f1; 
    color: #555; 
    border: none;
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    font-family: Arial, sans-serif;
}

.status-button.all {
    background-color: #007bff; 
    color: white;
}

.status-button .badge {
    background-color: #e1e1e1;
    color: #333;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 12px;
    min-width: 20px;
    text-align: center;
}

.status-button:hover {
    background-color: #f0f0f0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); 
}

.status-button.all:hover {
    background-color: #0056b3; 
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.status-button:active {
    transform: scale(0.98); 
    box-shadow: none;
}

.status-button:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(38, 143, 255, 0.5); 
}

@media (max-width: 768px) {
    .status-buttons {
        flex-direction: column;
        gap: 5px;
    }

    .status-button {
        width: 100%;
        justify-content: flex-start;
        padding: 12px;
        text-align: left;
    }
}
.remarks-textarea {
    width: 100%;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 1rem;
    line-height: 1.6;
    font-family: 'Roboto', sans-serif;
    color: #333;
    resize: vertical;
    box-sizing: border-box;
    text-rendering: optimizeLegibility;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: left;
}

.remarks-textarea:focus {
    border-color: #ff9800;
    outline: none;
}

@media (max-width: 768px) {
    .remarks-textarea {
        font-size: 0.9rem;
        padding: 8px;
    }
}

/* Notification container style */
.notifications-container {
    position: absolute;
    top: 40px;
    right: 0;
    width: 300px;
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
    padding: 10px;
    max-height: 300px;
    z-index: 1000;
    overflow-y: auto;
    overflow-x: hidden;
    display: none; /* Hidden by default */
}

.notification {
    background-color: #f9f9f9;
    padding: 10px;
    margin: 5px 0;
    border-radius: 5px;
    border: 1px solid #ddd;
    font-size: 14px;
}

.notification .badge {
    background-color: #007bff;
    color: white;
    border-radius: 12px;
    padding: 5px;
    font-size: 10px;
    position: absolute;
    top: 5px;
    right: 10px;
}

.notification-count {
    position: absolute;
    top: -8px;
    right: -10px;
    background-color: red;
    color: white;
    font-size: 12px;
    font-weight: bold;
    border-radius: 50%;
    padding: 3px 7px;
    display: inline-block;
}
.notification.read {
    background-color: #e0e0e0;
}

.notification strong {
    font-weight: bold;
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
               <!-- Notification Bell -->
            <div id="notification-bell" class="header-nav-item">
                <i id="notificationBell" class="fas fa-bell" title="Notifications"
                style="font-size: 24px; color: #555; cursor: pointer; position: relative;">
                    <!-- Notification Badge -->
                    <span id="notificationCount" class="notification-count" style="display: none;">0</span>
                </i>
            </div>
            <a href="../logout.php" class="header-nav-item">
                <i class="fas fa-right-from-bracket"></i> 
                Logout
            </a>
        </nav>
    </header>

    <div class="content" id="mainContent">
        <!-- Notifications container (initially hidden) -->
<div id="notifications-container" class="notifications-container">
    <!-- Notifications will be dynamically inserted here -->
</div>
        <h2>Client List</h2>

        <div class="action-bar">
          <div class="action-select">
            <label for="actionSelect">Action:</label>
            <select id="actionSelect">
                <option value="">Select an action</option>
                <option value="update">Update Status</option>
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
                        <option value="Pending Inspection">Pending Inspection</option>
                        <option value="Inspected">Inspected</option>
                        <option value="Waiting for Compliance">Waiting for Compliance</option>
                        <option value="Complied">Complied</option>
                        <option value="Notice to Comply Issued">Notice to Comply Issued</option>
                        <option value="Notice to Correct Violation">Notice to Correct Violation</option>
                        <option value="Issued Abandonment Order">Issued Abandonment Order</option>
                        <option value="Issued Closure Order">Issued Closure Order</option>
                    </select>

                    <!-- Clear Filters Button -->
                    <button class="clear-filters" onclick="clearFilters()">Clear</button>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div id="statusButtons" class="status-buttons">
                <!-- "All" Button -->
                <button class="status-button all" onclick="filterTable('status', 'all')">
                    <span>All</span> 
                    <span class="badge" id="badge-All"><?= $mappedStatusCountData['All'] ?? 0 ?></span>
                </button>

                <!-- Status Buttons (Generated from PHP) -->
                <?php foreach ($mappedStatusCountData as $status => $count): ?>
                    <?php if ($status != 'All'): ?>
                        <button class="status-button" onclick="filterTable('status', '<?= htmlspecialchars($status) ?>')">
                            <span><?= htmlspecialchars($status) ?></span>
                            <span class="badge" id="badge-<?= htmlspecialchars(str_replace(' ', '', $status)) ?>"><?= $count ?></span>
                        </button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <table id="applicationTable" class="table table-striped nowrap">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes(this)"></th>
                        <th>Application Type</th>
                        <th>Owner Name</th>
                        <th>Inpsection Schedule</th>
                        <th>Inspection Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="applicationTableBody">
                <?php if (count($applications) > 0): ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><input type="checkbox" name="action[]" value="<?= htmlspecialchars($app['appid']) ?>"></td>
                                <td><?= htmlspecialchars($app['application_type']) ?></td>
                                <td><?= htmlspecialchars($app['owner_name']) ?></td>
                                <td><?= htmlspecialchars($app['schedule']) ?></td>
                                <td><?= htmlspecialchars(getInspection($app['inspection_status'])) ?></td>
                                <td>
                                    <select onchange="handleAction(this)" style="padding: 5px; border-radius: 4px; width: 100%;">
                                        <option value="">Select</option>
                                        <option value="view">View</option>
                                        <option value="edit" <?php if ($app['inspection_status'] == 1 || $app['inspection_status'] >= 2) echo 'disabled'; ?>>Edit</option>
                                    </select>
                                </td>
                                <input type="hidden" class="facilityName" value="<?= htmlspecialchars($app['business_trade_name']) ?>">
                                <input type="hidden" class="address" value="<?= htmlspecialchars($app['address']) ?>">
                                <input type="hidden" class="email" value="<?= htmlspecialchars($app['email_address']) ?>">
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

            <div class="profile-body">
                <section>
                    <h6>Client Information</h6>
                    <ul id="viewClientInfo" class="info-list"></ul>
                </section>
            </div>

            <div class="profile-footer">
                <button id="closeClientProfileButton" onclick="closeModal('viewClientModal')">Close</button>
            </div>
        </div>


        <!-- Edit Client Modal -->
        <!-- <div id="editClientModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Edit Inspection</h5>
                    <span class="close" onclick="closeModal('editClientModal')">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="clientForm">
                        <input type="hidden" id="clientId" value="">
                        <div class="form-group">
                            <label for="schedule">Inspection Schedule:</label>
                            <input type="text" id="schedule" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn" onclick="saveClient()">Save</button>
                    <button class="btn btn-secondary" onclick="closeModal('editClientModal')">Close</button>
                </div>
            </div>
        </div> -->

        <div id="editClientModal" class="profile-modal" style="display: none;">
            <div class="profile-header">
                <span class="close-btn" onclick="closeModal('editClientModal')">&times;</span>
                <div class="header-content">
                    <h2>Edit Inspection</h2>
                </div>
            </div>

            <div class="profile-body">
                <form id="clientForm">
                    <input type="hidden" id="clientId" value="">

                    <div class="form-group">
                        <label for="inspectionDate">Inspection Date:</label>
                        <input type="date" id="inspectionDate" required>
                    </div>
                </form>
            </div>

            <div class="profile-footer">
                    <button class="btn" onclick="saveClient()">Save</button>
                    <button class="btn btn-secondary" onclick="closeModal('editClientModal')">Close</button>
            </div>
        </div>


    <div id="updateStatusModal" class="profile-modal" style="display: none;">
            <div class="profile-header">
                <span class="close-btn" onclick="closeModal('updateStatusModal')">&times;</span>
                <div class="header-content">
                    <h2>Update Status</h2>
                </div>
            </div>

            <div class="profile-body">
                <form id="clientForm">
                    <div class="form-group">
                        <label for="status">Inspection Status:</label>
                        <select id="action_status">
                            <option value="0">Pending Inspection</option>
                            <option value="1">Inspected</option>
                            <option value="2">Waiting for Compliance</option>
                            <option value="3">Complied</option>
                            <option value="4">Notice to Comply Issued</option>
                            <option value="5">Notice to Correct Violation</option>
                            <option value="6">Issued Abandonment Order</option>
                            <option value="7">Issued Closure Order</option>
                        </select>
                    </div>
                    <div class="form-group" id="remarksField" style="display: none;">
    <label for="remarks">Remarks:</label>
    <textarea id="remarks" rows="10" class="remarks-textarea">
Please ensure the following compliance items are addressed:
- Install fire extinguishers at designated locations.
- Ensure proper labeling of fire exits.
- Maintain clear access to fire safety equipment.
    </textarea>
</div>
                </form>
            </div>

            <div class="profile-footer">
                <button class="btn" onclick="saveClient()">Save</button>
                <button class="btn btn-secondary" onclick="closeModal('updateStatusModal')">Close</button>
            </div>
        </div>


    <script src="../sidebar/sidebar-toggle.js"></script>
    <script src="iclient-list.js"></script>
    <script>
        $(document).ready(function () {
    const table = $('#applicationTable').DataTable({
        responsive: true,
        autoWidth: false,
        searching: false, 
        paging: true,
        info: true,
        columnDefs: [
            { orderable: false, targets: [0, 5] } 
        ]
    });

    // Custom search functionality
    $('#searchInput').on('input', function () {
        const searchValue = this.value.toLowerCase();
        $('#applicationTableBody tr').each(function () {
            const row = $(this);
            const cells = row.find('td');
            const matchesSearch = cells.toArray().some(cell =>
                $(cell).text().toLowerCase().includes(searchValue)
            );
            row.toggle(matchesSearch);
        });
    });

    // Select All Checkboxes
    $('#selectAll').on('change', function () {
        const isChecked = $(this).prop('checked');
        $('#applicationTableBody input[type="checkbox"]').prop('checked', isChecked);
    });

    // Handle Action Dropdown
    $('#applicationTableBody').on('change', 'select', function () {
        handleAction(this);
    });
});

    // Pass the status count data from PHP to JavaScript
    let statusCountsFromServer = <?php echo json_encode($mappedStatusCountData); ?>;

    // Function to update badge counts dynamically
    function updateBadgeCounts() {
        for (let status in statusCountsFromServer) {
            const badgeElement = document.getElementById('badge-' + status.replace(/ /g, ''));
            if (badgeElement) {
                badgeElement.textContent = statusCountsFromServer[status];
            }
        }
    }

    // Call to update the badges when the page loads
    updateBadgeCounts();

    // Current filters
    let currentFilters = {
        application_type: 'all',
        status: 'all'
    };

    // Filter function for table based on status or application type
    function filterTable(filterType, filterValue) {
        currentFilters[filterType] = filterValue;

        const rows = document.querySelectorAll('#applicationTableBody tr');

        rows.forEach(row => {
            const applicationType = row.cells[1].textContent.trim();
            const status = row.cells[4].textContent.trim();

            const matchesApplicationType = currentFilters.application_type === 'all' || applicationType === currentFilters.application_type;
            const matchesStatus = currentFilters.status === 'all' || status === currentFilters.status;

            // Show or hide rows based on filter match
            if (matchesApplicationType && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Function to clear filters (reset to 'all')
    function clearFilters() {
        document.getElementById('applicationType').value = '';
        document.getElementById('status').value = '';
        filterTable('application_type', 'all');
        filterTable('status', 'all');
    }

    function toggleRemarksField() {
        const status = document.getElementById('action_status').value;
        const remarksField = document.getElementById('remarksField');

        if (status == '2') {
            remarksField.style.display = 'block'; 
        } else {
            remarksField.style.display = 'none'; 
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleRemarksField();  // Trigger on page load
        document.getElementById('action_status').addEventListener('change', toggleRemarksField);  // Trigger on status change
    });

    document.getElementById('notification-bell').addEventListener('click', function() {
        var notificationsContainer = document.getElementById('notifications-container');
        notificationsContainer.style.display = notificationsContainer.style.display === 'block' ? 'none' : 'block';
    });

    setInterval(function () {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../notifications.php', true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                var notificationsContainer = document.getElementById('notifications-container');
                var notificationCount = document.getElementById('notificationCount');
                
                notificationsContainer.innerHTML = '';

                if (data.length > 0) {
                    data.forEach(function (message) {
                        var notification = document.createElement('div');
                        notification.classList.add('notification');
                        notification.dataset.messageId = message.id;
                        notification.innerHTML = `
                            <strong>${message.owner_name}</strong>: ${message.message} <br>
                            <small>${message.created_at}</small>
                            <span class="badge">New</span>
                        `;
                        notificationsContainer.appendChild(notification);
                    });

                    notificationCount.style.display = 'block';
                    notificationCount.textContent = data.length;
                } else {
                    notificationsContainer.innerHTML = 'No new messages';
                    notificationCount.style.display = 'none'; 
                }
            }
        };
        xhr.send();
    }, 1000); 

    document.getElementById('notifications-container').addEventListener('click', function (event) {
        if (event.target.classList.contains('notification')) {
            var messageId = event.target.dataset.messageId;

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../mark_as_read.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    event.target.classList.add('read'); 
                    setTimeout(function() {
                        event.target.remove();
                    }, 500);
                }
            };
            xhr.send('message_id=' + messageId);
        }
    });


    function handleAction(select) {
        const row = $(select).closest('tr');
        const applicationType = row.find('td:eq(1)').text();
        const ownerName = row.find('td:eq(2)').text();
        const schedule = row.find('td:eq(3)').text();
        const status = row.find('td:eq(4)').text();
        const appId = row.find('td:eq(0) input[type="checkbox"]').val();

        const facilityName = row.find('.facilityName').val();
        const address = row.find('.address').val();
        const email = row.find('.email').val();

        if (select.value === "view") {
            viewClient(applicationType, facilityName, ownerName, status, address, email, appId, schedule);
        } else if (select.value === "edit") {
            editClient(applicationType, facilityName, ownerName, status, address, email, appId, schedule);
        }

        select.value = ""; 
    }
    </script>
</body>
</html>
