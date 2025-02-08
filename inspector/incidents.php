<?php
session_start();
include '../db_connection.php';

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index/index.php");
    exit();
}
    
if (isset($_SESSION['flash-msg']) && $_SESSION['flash-msg'] === 'success-incident') {
    echo "
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Success!',
                text: 'Residential fire incident added successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });
    </script>
    ";
    unset($_SESSION['flash-msg']); 
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
    <title>BFP - Residential Fire Incidents</title>
    <?php include('../dataTables/dataTable-links.php')?>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/incidents.css">
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

    <!-- main content: -->
    <div class="content" id="mainContent">
        <h2>Residential Fire Incidents</h2>

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
                <button class="filter-toggle" onclick="toggleFilterPanel()">
                    <i class="fas fa-filter"></i> Filter
                </button>

                <div class="filter-panel">
                    <div class="filter-row">
                        <button class="filter-toggle" onclick="toggleFilterPanel()">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <input type="date" id="dateFilter" onchange="filterTable('date', this.value)">
                        <input type="time" id="timeFilter" onchange="filterTable('time', this.value)">
                    </div>

                    <div class="filter-row">
                        <select id="locationFilter" onchange="filterTable('location', this.value)">
                            <option value="" disabled selected>Location</option>
                            <option value="all">All</option>
                        </select>
                        <select id="statusFilter" onchange="filterTable('status', this.value)">
                            <option value="" disabled selected>Status</option>
                            <option value="all">All</option>
                            <option value="investigation">For Investigation</option>
                            <option value="under_investigation">Under Investigation</option>
                            <option value="investigation_completed">Investigation Completed</option>
                            <option value="closed">Closed</option>
                        </select>
                        <button class="clear-filters" onclick="clearFilters()">Clear Filters</button>
                    </div>
                </div>
            </div>
        </div>

    <?php
    include '../db_connection.php';

    $query = "SELECT * FROM incident_reports";
    $result = $conn->query($query);
    ?>

    <div class="table-container">
        <table class="table table-striped nowrap" id="incidentTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes(this)"></th>
                    <th>Incident Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Owner/Occupant</th>
                    <th>Occupancy Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="incidentsTableBody">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><input type="checkbox" name="action[]" value="<?= htmlspecialchars($row['id']) ?>"></td>
                            <td><?php echo htmlspecialchars($row['incident_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['time']); ?></td>
                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                            <td><?php echo htmlspecialchars($row['owner_occupant']); ?></td>
                            <td><?php echo htmlspecialchars($row['occupancy_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td>
                                <select onchange="handleAction(this, <?php echo $row['id']; ?>)" style="padding: 5px; border-radius: 4px;">
                                    <option value="">Select</option>
                                    <option value="view">View</option>
                                    <option value="update">Update</option>
                                </select>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No incident reports found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

            <div id="viewIncidentModal" class="profile-modal" style="display: none;">
                <div class="profile-header">
                    <h6>Incident Details</h6>
                    <span class="close-btn" onclick="closeViewModal()">&times;</span>
                </div>

                <div class="profile-body">
                    <ul id="viewIncidentInfo" class="info-list">
                        <li><strong>Incident ID:</strong> <span id="modalIncidentID"></span></li>
                        <li><strong>Reported By:</strong> <span id="modalReporter"></span></li>
                        <li><strong>Date Reported:</strong> <span id="modalDateReported"></span></li>
                        <li><strong>Status:</strong> <span id="modalIncidentStatus"></span></li>
                        <li><strong>Description:</strong> <span id="modalIncidentDescription"></span></li>
                    </ul>
                </div>

                <div class="profile-footer">
                    <button class="btn-secondary" onclick="closeViewModal()">Close</button>
                </div>
            </div>


            <div id="updateIncidentModal" class="profile-modal" style="display: none;">
                <div class="profile-header">
                    <span class="close-btn" onclick="closeUpdateModal()">&times;</span>
                    <div class="header-content">
                        <h2>Update Incident</h2>
                    </div>
                </div>

                <div class="profile-body">
                    <form id="updateIncidentForm" onsubmit="updateIncident(event)">
                        <input type="hidden" id="incidentId" required>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="updateIncidentDate">Incident Date:</label>
                                <input type="date" id="updateIncidentDate" required>
                            </div>
                            <div class="form-group">
                                <label for="updateTime">Time:</label>
                                <input type="time" id="updateTime" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="updateLocation">Location:</label>
                                <input type="text" id="updateLocation" required>
                            </div>
                            <div class="form-group">
                                <label for="updateOwner">Owner/Occupant:</label>
                                <input type="text" id="updateOwner" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="updateOccupancyType">Occupancy Type:</label>
                                <select id="updateOccupancyType" name="updateOccupancyType" required>
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
                                <label for="updateCauseOfFire">Cause of Fire:</label>
                                <input type="text" id="updateCauseOfFire">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="updateEstimatedDamages">Estimated Damages:</label>
                                <input type="number" id="updateEstimatedDamages">
                            </div>
                            <div class="form-group">
                                <label for="updateCasualtiesInjuries">Casualties/Injuries:</label>
                                <input type="text" id="updateCasualtiesInjuries">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="updateFireControlTime">Fire Control Time:</label>
                                <input type="text" id="updateFireControlTime">
                            </div>
                            <div class="form-group">
                                <label for="updateInspector">Inspector In Charge:</label>
                                <input type="text" id="updateInspector">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="updateReportDate">Investigation Report Date:</label>
                                <input type="date" id="updateReportDate">
                            </div>
                            <div class="form-group">
                                <label for="updateStatus">Status:</label>
                                <select id="updateStatus" name="updateStatus" required>
                                    <option value="For Investigation">For Investigation</option>
                                    <option value="Under Investigation">Under Investigation</option>
                                    <option value="Investigation Completed">Investigation Completed</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn">Update</button>
                    </form>
                </div>
            </div>
        <?php
        $conn->close();
        ?>
    </div>

<script src="../sidebar/sidebar-toggle.js"></script>
<script src="incidents.js"></script>
</body>
</html>