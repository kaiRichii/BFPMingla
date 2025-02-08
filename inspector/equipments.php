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
    <title>BFP - Firefighting Equipment</title>
    <?php include('../dataTables/dataTable-links.php')?>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/equipments.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
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
                    
            <div id="notificationWrapper" style="position: relative;">
                <i id="notificationBell" class="fas fa-bell" title="Notifications"
                style="font-size: 24px; color: #555; cursor: pointer; position: relative;">
                <!-- Notification Badge -->
                <span id="notificationCount" class="notification-count" style="display: none;">0</span>
                </i>
                <div id="notificationDropdown" class="notification-dropdown" style="display: none;">
                    <div class="notification-header" style="padding: 10px; background: #f8f9fa; border-bottom: 1px solid #ddd;">
                    Notifications
                        <button id="muteSoundToggle" style="font-size: 12px; color: #007BFF; background: none; border: none; cursor: pointer;">
                    Mute Sound
                        </button>
                        <button id="markAllAsRead" class="mark-all-read-btn" style="font-size: 12px; color: #007BFF; background: none; border: none; cursor: pointer;">
                    Mark All as Read
                        </button>
                    </div>
                    <!-- Body -->
                    <div class="notification-body">
                        <ul id="notificationList" class="notification-list">
                            <!-- Notifications dynamically added here -->
                        </ul>
                    </div>
                    <!-- Footer -->
                    <div id="notificationFooter" class="notification-footer" style="padding: 10px; background: #f8f9fa; border-top: 1px solid #ddd; text-align: center;">
                    View All Notifications
                    </div>
                </div>
            </div>
            <a href="../logout.php" class="header-nav-item">
                <i class="fas fa-right-from-bracket"></i> 
                Logout
            </a>
        </nav>
    </header>

    <div class="content" id="mainContent">
        <h2>Firefighting Equipment</h2>

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

             <!-- Search Bar -->
            <div class="search-bar">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" placeholder="Search" oninput="debouncedSearch()">
            </div>
                    
            <div class="filter-container">
                <button class="filter-toggle" onclick="toggleFilterPanel()">
                <i class="fas fa-filter"></i> Filter
                </button>

                <div class="filter-panel" id="filterPanel">
                    <div class="filter-row">
                        <button class="filter-toggle" onclick="toggleFilterPanel()">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <select id="equipmentStatus" onchange="filterTable()">
                            <option value="all">All</option>
                            <option value="good">Good</option>
                            <option value="under_repair">Under repair</option>
                            <option value="needs_replacement">Needs replacement</option>
                        </select>

                    <select id="quantity" onchange="filterTable('quantity', this.value)">
                        <option value="" disabled selected>Quantity</option>
                        <option value="all">All</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>

                <div class="filter-row">
                    <input type="date" id="lastMaintenance" onchange="filterTable('last_maintenance', this.value)">
                    <input type="date" id="nextMaintenance" onchange="filterTable('next_maintenance', this.value)">
                    <button class="clear-filters" onclick="clearFilters()">Clear Filters</button>
                </div>
            </div>
            </div>
        </div>
        

        <div class="table-container">
            <table class="table table-striped nowrap" id="equipmentTable">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                        <th>Equipment Name</th>
                        <th>Quantity</th>
                        <th>Last Maintenance Date</th>
                        <th>Next Maintenance Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody id="equipmentTableBody">
                    <!-- Dynamic equipment rows will be inserted here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- View Equipment Modal -->
        <div id="viewEquipmentModal" class="profile-modal" style="display: none;">
            <div class="profile-header">
                <h5>Equipment Details</h5>
                <span class="close-btn" onclick="closeModal('viewEquipmentModal')">&times;</span>
            </div>

            <div class="profile-body">
                <ul id="viewEquipmentInfo" class="info-list">

                </ul>
            </div>

            <div class="profile-footer">
                <button class="btn-secondary" onclick="closeModal('viewEquipmentModal')">Close</button>
            </div>
        </div>

    <!-- Update Equipment Modal -->
    <div id="updateEquipmentModal" class="profile-modal" style="display: none;">
        <div class="profile-header">
            <span class="close-btn" onclick="closeModal('updateEquipmentModal')">&times;</span>
            <div class="header-content">
                <h2>Update Equipment</h2>
            </div>
        </div>

        <div class="profile-body">
            <form id="updateEquipmentForm">
                <input type="hidden" id="updateId" required>

                <div class="form-row">
                    <div class="form-group">
                        <label for="updateEquipmentName">Equipment Name:</label>
                        <input type="text" id="updateEquipmentName" required>
                    </div>
                    <div class="form-group">
                        <label for="updateQuantity">Quantity:</label>
                        <input type="number" id="updateQuantity" min="1" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="updateLastMaintenance">Last Maintenance Date:</label>
                        <input type="date" id="updateLastMaintenance" required>
                    </div>
                    <div class="form-group">
                        <label for="updateNextMaintenance">Next Maintenance Date:</label>
                        <input type="date" id="updateNextMaintenance" required>
                    </div>
                </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="updateNotes">Notes:</label>
                            <textarea id="updateNotes" rows="5" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="updateStatus">Status:</label>
                            <select id="updateStatus" required>
                                <option value="Good">Good</option>
                                <option value="Under Repair">Under Repair</option>
                                <option value="Needs Replacement">Needs Replacement</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="profile-footer">
                <button class="btn" onclick="updateEquipment()">Update</button>
                <button class="btn-secondary" onclick="closeModal('updateEquipmentModal')">Cancel</button>
            </div>
        </div>


    <?php
    if(isset($_SESSION['flash-msg'])){
        echo "
        <script>
            Swal.fire(
                'Success!',
                'Equipment added successfully.',
                'success'
            );
        </script>
        ";
        unset($_SESSION['flash-msg']);
    }
    ?>
    <?php
    include '../db_connection.php';

    $query = "
        SELECT equipment_name, next_maintenance_date, status 
        FROM equipment
        WHERE status = 'needs replacement' OR next_maintenance_date <= CURDATE()
        ORDER BY next_maintenance_date DESC
        LIMIT 10";

    $result = $conn->query($query);

    $alerts = [];

    while ($row = $result->fetch_assoc()) {
        $alerts[] = [
            'equipment_name' => $row['equipment_name'],
            'next_maintenance_date' => $row['next_maintenance_date'],
            'status' => $row['status']
        ];
    }

    $alerts_json = json_encode($alerts);

    ?>


    <script src="maintenance-alerts.js"></script>
    <script src="../sidebar/sidebar-toggle.js"></script>
    <script>

        function toggleSelectAll(masterCheckbox) {
                const checkboxes = document.querySelectorAll('.row-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = masterCheckbox.checked;
                });
            }


        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none'; 
            }
        }

        function toggleFilterPanel() {
            const panel = document.querySelector(".filter-panel");
            const toggleButton = document.querySelectorAll(".filter-toggle")[0]; 
            
            if (panel.style.display === "none" || panel.style.display === "") {
                panel.style.display = "flex";
                toggleButton.style.display = "none";
            } else {
                panel.style.display = "none";
                toggleButton.style.display = "flex";
            }
        }

        function filterTable() {
            const statusFilter = document.getElementById('equipmentStatus').value.toLowerCase();
            const quantityFilter = document.getElementById('quantity').value;
            const lastMaintenanceFilter = document.getElementById('lastMaintenance').value;
            const nextMaintenanceFilter = document.getElementById('nextMaintenance').value;

            const rows = document.querySelectorAll('#equipmentTable tbody tr');

            rows.forEach(row => {
                const status = row.cells[5].textContent.toLowerCase().trim();
                const quantity = row.cells[2].textContent.trim();
                const lastMaintenance = row.cells[3].textContent.toLowerCase().trim();
                const nextMaintenance = row.cells[4].textContent.toLowerCase().trim();

                let matchStatus = (statusFilter === "all" || status === statusFilter);
                let matchQuantity = (quantityFilter === "all" || quantity === quantityFilter);
                let matchLastMaintenance = (!lastMaintenanceFilter || lastMaintenance.includes(lastMaintenanceFilter));
                let matchNextMaintenance = (!nextMaintenanceFilter || nextMaintenance.includes(nextMaintenanceFilter));

                row.style.display = (matchStatus && matchQuantity && matchLastMaintenance && matchNextMaintenance) ? '' : 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('equipmentStatus').addEventListener('change', filterTable);
            document.getElementById('quantity').addEventListener('change', filterTable);
            document.getElementById('lastMaintenance').addEventListener('change', filterTable);
            document.getElementById('nextMaintenance').addEventListener('change', filterTable);
        });


        function clearFilters() {
            document.getElementById('equipmentStatus').value = 'all';
            document.getElementById('quantity').value = 'all';
            document.getElementById('lastMaintenance').value = '';
            document.getElementById('nextMaintenance').value = '';

            filterTable();  
        }

        function populateQuantityDropdown(quantities) {
            const quantityDropdown = document.getElementById('quantity');
            quantityDropdown.innerHTML = '<option value="all">All</option>';  
            quantities.forEach(quantity => {
                const option = document.createElement('option');
                option.value = option.textContent = quantity;
                quantityDropdown.appendChild(option);
            });
        }
        
        let table = null; // Declare the table variable globally to retain its instance
        async function loadEquipment() {
            try {
                const response = await fetch('../fetch.php');
                const equipmentData = await response.json();

                const quantitySet = new Set();
                const rowsToAdd = [];

                equipmentData.forEach(equipment => {
                    quantitySet.add(equipment.quantity.toString());

                    const row = [
                        `<input type="checkbox" class="row-checkbox" name="action[]" value="${equipment.id}">`,
                        equipment.equipment_name,
                        equipment.quantity,
                        equipment.last_maintenance_date,
                        equipment.next_maintenance_date,
                        equipment.status,
                        `<select data-id="${equipment.id}">
                            <option value="">Select</option>
                            <option value="view">View</option>
                            <option value="update">Update</option>
                        </select>`,
                        equipment.notes,
                    ];
                    rowsToAdd.push(row);
                });

                populateQuantityDropdown(Array.from(quantitySet));

                if (!table) {
                    // Initialize DataTable only once
                    table = $('#equipmentTable').DataTable({
                        responsive: true,
                        autoWidth: false,
                        searching: true,
                        paging: true,
                        info: true
                    });
                }

                // Clear and add new rows
                table.clear().rows.add(rowsToAdd).draw();
            } catch (error) {
                console.error('Error fetching equipment data:', error);
            }
        }

        $(document).on('change', '#equipmentTable select', function () {
            const select = this;
            const action = select.value;
            const row = $(select).closest('tr');
            const id = select.getAttribute('data-id');
            const name = row.find('td:eq(1)').text();
            const quantity = row.find('td:eq(2)').text();
            const lastMaintenance = row.find('td:eq(3)').text();
            const nextMaintenance = row.find('td:eq(4)').text();
            const status = row.find('td:eq(5)').text();
            const notes = row.find('td:eq(7)').text();

            if (action === 'view') {
                viewEquipment(name, quantity, lastMaintenance, nextMaintenance, status, notes);
                select.selectedIndex = 0;
            } else if (action === 'update') {
                openUpdateModal(id, name, quantity, lastMaintenance, nextMaintenance, status, notes);
                select.selectedIndex = 0;
            }
        });

        function viewEquipment(name, quantity, lastMaintenance, nextMaintenance, status, notes) {
            const viewInfo = document.getElementById('viewEquipmentInfo');
            viewInfo.innerHTML = `
                <li><strong>Equipment Name:</strong> ${name}</li>
                <li><strong>Quantity:</strong> ${quantity}</li>
                <li><strong>Last Maintenance Date:</strong> ${lastMaintenance}</li>
                <li><strong>Next Maintenance Date:</strong> ${nextMaintenance}</li>
                <li><strong>Status:</strong> ${status}</li>
                <li><strong>Notes:</strong> ${notes}</li>
            `;
            document.getElementById('viewEquipmentModal').style.display = 'block';
        }

        function openUpdateModal(id, name, quantity, lastMaintenance, nextMaintenance, status, notes) {

            document.getElementById('updateId').value = id;
            document.getElementById('updateEquipmentName').value = name;
            document.getElementById('updateQuantity').value = quantity;
            document.getElementById('updateLastMaintenance').value = lastMaintenance;
            document.getElementById('updateNextMaintenance').value = nextMaintenance;
            document.getElementById('updateStatus').value = status;
            document.getElementById('updateNotes').value = notes;

            document.getElementById('updateEquipmentModal').style.display = 'block';
        }

        async function updateEquipment() {
            const updatedData = {
                id: document.getElementById('updateId').value,
                name: document.getElementById('updateEquipmentName').value,
                quantity: document.getElementById('updateQuantity').value,
                lastMaintenance: document.getElementById('updateLastMaintenance').value,
                nextMaintenance: document.getElementById('updateNextMaintenance').value,
                notes: document.getElementById('updateNotes').value,
                status: document.getElementById('updateStatus').value
            };

            try {
                const response = await fetch('update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(updatedData),
                });

                const result = await response.json();
                if (result.success) {
                    Swal.fire('Success!', 'Equipment updated successfully.', 'success').then(() => {
                        loadEquipment(); 
                    });
                } else {
                    Swal.fire('Error!', result.error || 'Failed to update equipment.', 'error');
                }
            } catch (error) {
                Swal.fire('Error!', 'An error occurred while updating the equipment. Please try again.', 'error');
                console.error('Error updating equipment:', error);
            }

            closeModal('updateEquipmentModal');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        document.getElementById('goButton').addEventListener('click', async function () {
            const action = document.getElementById('actionSelect').value;

            // Get all selected checkboxes
            const checkedCheckboxes = Array.from(
                document.querySelectorAll('input[name="action[]"]:checked')
            );

            // Extract their values (IDs)
            const equipmentIds = checkedCheckboxes.map(checkbox => checkbox.value);

            if (equipmentIds.length === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "No Selection",
                    text: "Please select at least one equipment.",
                });
                return;
            }

            if (action === 'export_pdf') {
                window.location.href =
                    '../export_equipment.php?action=pdf&equipmentIds=' +
                    encodeURIComponent(equipmentIds.join(','));
            } else if (action === 'export_excel') {
                window.location.href =
                    '../export_equipment.php?action=excel&equipmentIds=' +
                    encodeURIComponent(equipmentIds.join(','));
            } else if (action === 'delete') {
                const confirm = await Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                });

                if (confirm.isConfirmed) {
                    try {
            const response = await fetch('../ajax.php?action=delete_equipment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ equipmentIds }),
            });

            const text = await response.text(); 
            let result;
            try {
                result = JSON.parse(text); 
            } catch (e) {
                console.error('Invalid JSON:', text);
                throw new Error('Server returned an invalid JSON response');
            }

            if (result.success) {
                Swal.fire(
                    'Deleted!',
                    'The selected equipment has been deleted.',
                    'success'
                ).then(() => window.location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.error || 'Something went wrong!',
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: error.message || 'An error occurred while deleting the equipment.',
            });
            console.error('Error:', error);
        }
                }
            } 
        });
    window.onload = loadEquipment;
</script>
</body>
</html>
