<?php
include '../db_connection.php'; 

$sql = "SELECT applications.id AS appid, owner_name, application_type, address, contact_number, email_address, issuance.status AS issuance_status, additional FROM applications INNER JOIN issuance ON applications.id = issuance.application_id";
$result = $conn->query($sql);

$applications = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }
} else {
    echo "Error: " . $conn->error; 
}

$conn->close(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Minglanilla - FSIC/FSEC Clients</title>
    <?php include('../dataTables/dataTable-links.php')?>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin-client.css">
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <nav class="menu" role="navigation">
            <a href="admin.php" class="menu-item">
                <i class="fas fa-house-chimney"></i> 
                <span>Home</span>
            </a>
            <div class="menu-item clients active">
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
            <div class="menu-item clients">
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
        <h2>Client List</h2>

        <div class="action-bar">
            <!-- Action Section for Delete -->
            <div class="action-select">
                <label for="actionSelect">Action:</label>
                <select id="actionSelect">
                    <option value="">Select an action</option>
                    <option value="message">Message</option>
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
                <i class="fas fa-filter"></i> Filters
                </button>

                <!-- Toggleable Filter Panel -->
                <div class="filter-panel" id="filterPanel" style="display: none;">
                <div class="filter-group">
                    <!-- <label for="applicationType">By Application Type:</label> -->
                    <select id="applicationType" onchange="filterTable('application_type', this.value)">
                        <option value="all">All</option>
                        <option value="building">Building</option>
                        <option value="occupancy">Occupancy</option>
                        <option value="new_business_permit">New Business Permit</option>
                        <option value="renewal_business_permit">Renewal Business Permit</option>
                    </select>
                </div>

                <div class="filter-group">
                    <!-- <label for="status">By Status:</label> -->
                    <select id="status" onchange="filterTable('status', this.value)">
                        <option value="all">All</option>
                        <option value="Ready for Issuance">Ready for Issuance</option>
                        <option value="Issued">Issued</option>
                    </select>
                </div>

                <!-- Clear Filters Button -->
                <button class="clear-filters" onclick="clearFilters()">Clear</button>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table id="applicationTable" class="table table-striped nowrap">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes(this)"></th>
                        <th>Owner Name</th>
                        <th>Application Type</th>
                        <th>Email Address</th>
                        <th>Issuance Status</th>
                        <th>Actions</th>
                        <th>Address</th>
                        <th>Contact Number</th>
                    </tr>
                </thead>
                <tbody id="applicationTableBody">
                <?php if (count($applications) > 0): ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><input type="checkbox" name="action[]" value="<?= htmlspecialchars($app['appid']) ?>"></td>
                                <td><?= htmlspecialchars($app['owner_name']) ?></td>
                                <td><?= htmlspecialchars($app['application_type']) ?></td>
                                <td><?= htmlspecialchars($app['email_address']) ?></td>
                                <td><?= htmlspecialchars($app['issuance_status'] == 0 ? 'Ready for Issuance' : 'Issued') ?></td>
                                <td>
                                    <select onchange="handleAction(this)" style="padding: 5px; border-radius: 4px; width: 100%;">
                                        <option value="">Select</option>
                                        <?php if($app['issuance_status'] == 1): ?>
                                        <option value="print">Print</option>
                                        <option value="edit">Update</option>
                                        <?php endif; ?>
                                        <option value="view">View</option>
                                        <?php if($app['issuance_status'] == 0): ?>
                                        <option value="issuance">Ready for Issuance</option>
                                        <?php endif; ?>
                                    </select>
                                </td>
                                <td><?= htmlspecialchars($app['address']) ?></td>
                                <td><?= htmlspecialchars($app['contact_number']) ?></td>
                                <input type="hidden" class="additional" value="<?= htmlspecialchars($app['additional']) ?>">
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

    <div id="viewClientModal" class="client-profile-modal" style="display: none;">
        <div class="client-profile-header">
            <span class="close-btn" onclick="closeModal('viewClientModal')">&times;</span>
            <div class="header-content">
                <div class="profile-info">
                    <h6 id="clientModalTitle">Client Details</h6> 
                    <p>Detailed Information</p>
                </div>
            </div>
        </div>

        <div class="client-profile-body">
            <section>
                <h3>Client Information</h3>
                <ul id="viewClientInfo" class="info-list"></ul>
            </section>
            <section>
                <h3>Additional Details</h3>
                <ul id="viewAdditionalInfo" class="info-list"></ul>
            </section>
        </div>

        <div class="client-profile-footer">
            <button class="btn btn-secondary" onclick="closeModal('viewClientModal')">Close</button>
        </div>
    </div>


    <!-- building/occupancy -->
    <div id="issuanceBuildingModal" class="issuance-building-modal" style="display: none;">
        <div class="issuance-building-header">
            <span class="close-btn" onclick="closeModal('issuanceBuildingModal')">&times;</span>
            <div class="header-content">
                <h6 id="issuanceModalTitle">Building/Occupancy Details</h6>
                <p>Fill out the required details below</p>
            </div>
        </div>

        <div class="issuance-building-body">
            <form id="issuanceBuildingForm">
                <input type="hidden" id="issuance_building_id" value="">
                <input type="hidden" id="application_type_building" value="">

                <div class="form-row">
                    <div class="form-group">
                        <label for="area">Total Floor Area (sqm):</label>
                        <input type="text" id="area" required>
                    </div>
                    <div class="form-group">
                        <label for="dateInspection">Date of Inspection<span class="required">*</span>:</label>
                        <input type="date" id="dateInspection" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="engineer">Engineer:</label>
                        <input type="text" id="engineer" required>
                    </div>
                    <div class="form-group">
                        <label for="fsic">FSIC/FSEC No.<span class="required">*</span>:</label>
                        <input type="text" id="fsic" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="or">OR Number<span class="required">*</span>:</label>
                        <input type="text" id="or" required>
                    </div>
                    <div class="form-group">
                        <label for="dateRInspection">Re-inspection Date:</label>
                        <input type="date" id="dateRInspection" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="amount">Amount<span class="required">*</span>:</label>
                        <input type="text" id="amount" required>
                    </div>
                    <div class="form-group">
                        <label for="datePayment">Date of Payment<span class="required">*</span>:</label>
                        <input type="date" id="datePayment" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dateCertificate">Certificate Date<span class="required">*</span>:</label>
                        <input type="date" id="dateCertificate" required>
                    </div>
                    <div class="form-group">
                        <label for="dateClaimed">Date Claimed:</label>
                        <input type="date" id="dateClaimed" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="acknowledge">Acknowledge by:</label>
                        <input type="text" id="acknowledge" required>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="issuance-building-footer">
            <button class="btn" onclick="saveIssuance(false)">Save</button>
            <button class="btn" onclick="saveIssuance(true)">Save and Generate FSIC</button>
            <button class="btn btn-secondary" onclick="closeModal('issuanceBuildingModal')">Close</button>
        </div>
    </div>


    <!-- new/renewal: -->
    <div id="issuanceBusinessModal" class="issuance-building-modal" style="display: none;">
        <div class="issuance-business-header">
            <span class="close-btn" onclick="closeModal('issuanceBusinessModal')">&times;</span>
            <div class="header-content">
                <h6 id="issuanceModalTitle">New/Renewal Business Permit</h6>
                <p>Fill out the required details below</p>
            </div>
        </div>

        <div class="issuance-business-body">
            <form id="issuanceBusinessForm">
                <input type="hidden" id="issuance_business_id" value="">
                <input type="hidden" id="application_type_business" value="">

                <!-- Form Fields -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="area">Total Floor Area (sqm):</label>
                        <input type="text" id="area" class="area" required>
                    </div>
                    <div class="form-group">
                        <label for="tin">TIN:</label>
                        <input type="text" id="tin" class="tin" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="businessId">Business ID:</label>
                        <input type="text" id="businessId" required>
                    </div>
                    <div class="form-group">
                        <label for="typeOccupancy">Type of Occupancy<span class="required">*</span>:</label>
                        <select id="typeOccupancy" required>
                            <option selected disabled>Select Type</option>
                            <option value="assembly">Assembly</option>
                            <option value="educational">Educational</option>
                            <option value="daycare">Daycare</option>
                            <option value="healthcare">Healthcare</option>
                            <option value="residential">Residential Board & Care</option>
                            <option value="detention">Detention & Correctional</option>
                            <option value="mercantile">Mercantile</option>
                            <option value="business">Business</option>
                            <option value="industrial">Industrial</option>
                            <option value="storage">Storage</option>
                            <option value="special">Special Structures</option>
                            <option value="hotel">Hotel</option>
                            <option value="dormitories">Dormitories</option>
                            <option value="apartment">Apartment Buildings</option>
                            <option value="lodging">Lodging & Rooming Houses</option>
                            <option value="single">Single & Two Family Dwellings</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="checklist">Checklist Used:</label>
                        <input type="text" id="checklist" required>
                    </div>
                    <div class="form-group">
                        <label for="fsic">FSIC No.<span class="required">*</span>:</label>
                        <input type="text" id="fsic" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="or">OR No.<span class="required">*</span>:</label>
                        <input type="text" id="or" required>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount<span class="required">*</span>:</label>
                        <input type="text" id="amount" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="datePayment">Date of Payment<span class="required">*</span>:</label>
                        <input type="date" id="datePayment" required>
                    </div>
                    <div class="form-group">
                        <label for="dateCertificate">Certificate Date<span class="required">*</span>:</label>
                        <input type="date" id="dateCertificate" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dateClaimed">Date Claimed:</label>
                        <input type="date" id="dateClaimed" required>
                    </div>
                    <div class="form-group">
                        <label for="received">Received by:</label>
                        <input type="text" id="received" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="issuance-business-footer">
            <button class="btn" onclick="saveIssuance(false)">Save</button>
            <button class="btn" onclick="saveIssuance(true)">Save and Generate FSIC</button>
            <button class="btn btn-secondary" onclick="closeModal('issuanceBusinessModal')">Close</button>
        </div>
    </div>

        <div id="messageModal" class="" style="display: none;">
            <div class="message-content">
                <div class="message-header">
                    <h5>Compose Message</h5>
                    <span class="close" onclick="closeModal('messageModal')">&times;</span>
                </div>
                <div class="message-body">
                    <textarea id="message" style="width:100%; min-height: 200px;"></textarea>
                </div>
                <div class="message-footer">
                    <button class="btn btn-primary">Send</button>
                    <button class="btn btn-secondary" onclick="closeModal('messageModal')">Close</button>
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

        // Toggle the filter panel visibility (new)
        function toggleFilterPanel() {
            const panel = document.getElementById("filterPanel");
            panel.style.display = (panel.style.display === "none" || panel.style.display === "") ? "flex" : "none";
        }

        // for filter section
        let currentFilters = {
            application_type: 'all',
            status: 'all'
        };

        // Clear Filters Function
        function clearFilters() {
            // Reset filter values
            document.getElementById('applicationType').value = 'all';
            document.getElementById('status').value = 'all';
            
            // Reset currentFilters object
            currentFilters.application_type = 'all';
            currentFilters.status = 'all';

            // Trigger re-filtering to show all rows
            filterTable(); // Call filterTable without parameters to reset view
        }

        function filterTable(filterType, filterValue) {
            // Update currentFilters with the selected filter
            currentFilters[filterType] = filterValue;

            const rows = document.querySelectorAll('#applicationTableBody tr');

            rows.forEach(row => {
                const applicationType = row.cells[2].textContent.trim();
                const status = row.cells[6].textContent.trim();
                
                const matchesApplicationType = currentFilters.application_type === 'all' || applicationType === currentFilters.application_type;
                const matchesStatus = currentFilters.status === 'all' || status === currentFilters.status;

                if (matchesApplicationType && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        // Initialize DataTable with responsive and custom configurations (new)
        $(document).ready(function() {
            const table = $('#applicationTable').DataTable({
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
            const table = document.getElementById("applicationTableBody");
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

        function handleAction(select) {
            const row = select.closest('tr');
            const ownerName = row.cells[1].innerText;
            const applicationType = row.cells[2].innerText;
            const email = row.cells[3].innerText;
            const status = row.cells[4].innerText;
            const address = row.cells[6].innerText;
            const phone = row.cells[7].innerText;
            const jsonAdditional = row.querySelector('.additional').value;
            const appId = row.cells[0].querySelector('input[type="checkbox"]').value;

            if (select.value === "view") {
                viewClient(appId, ownerName, applicationType, address, phone, email, status, jsonAdditional);
            } else if (select.value === "edit") {
                editIssuance(applicationType, appId, jsonAdditional);
            } else if (select.value === "issuance") {
                editIssuance(applicationType, appId, jsonAdditional);
            }else if (select.value === "print") {
                switch (applicationType) {
                    case 'building':
                        window.open('generate_building.php?id=' + appId, '_blank', 'width=800,height=600,toolbar=no,menubar=no,scrollbars=yes,resizable=yes');
                        break;
                    case 'occupancy':
                        window.open('generate_occupancy.php?id=' + appId, '_blank', 'width=800,height=600,toolbar=no,menubar=no,scrollbars=yes,resizable=yes');
                        break;
                    default:
                        window.open('generate_business.php?id=' + appId, '_blank', 'width=800,height=600,toolbar=no,menubar=no,scrollbars=yes,resizable=yes');
                }
            }

            select.value = "";  
        }

        function viewClient(appId, ownerName, applicationType, address, phone, email, status, jsonAdditional) {
            const viewInfoList = document.getElementById("viewClientInfo");
            const additionalInfoList = document.getElementById("viewAdditionalInfo");
            const modalTitle = document.getElementById("clientModalTitle"); // Access the title element
            viewInfoList.innerHTML = ""; // Clear previous details
            additionalInfoList.innerHTML = "";

             // Set the modal title to the owner's name
            modalTitle.textContent = `${ownerName}`; // Set title to ownerName

            const details = [
                { label: "Application Type", value: applicationType },
                { label: "Owner Name", value: ownerName },
                { label: "Address", value: address },
                { label: "Email", value: email },
                { label: "Application ID", value: appId },
            ];

            //details
            details.forEach(detail => {
                const listItem = document.createElement("li");
                listItem.innerHTML = `<strong>${detail.label}:</strong> ${detail.value}`;
                viewInfoList.appendChild(listItem);
            });

            const additionalDetails = JSON.parse(jsonAdditional);
            Object.entries(additionalDetails).forEach(([key, value]) => {
                const listItem = document.createElement("li");
                listItem.innerHTML = `<strong>${key}:</strong> ${value}`;
                additionalInfoList.appendChild(listItem);
            });

            document.getElementById("viewClientModal").style.display = "block"; // Show the modal
        }

        function editIssuance(applicationType, appId, jsonAdditional) {
            const data = JSON.parse(jsonAdditional);

            // Function to set today's date in the input fields
            function setTodayDate(selector) {
                const today = new Date();
                const formattedDate = today.toISOString().split('T')[0];  // Format as YYYY-MM-DD
                document.querySelector(selector).value = formattedDate;
            }

            if (applicationType === 'building' || applicationType === 'occupancy') {
                document.getElementById("application_type_building").value = applicationType;
                document.getElementById("issuance_building_id").value = appId;

                document.querySelector("#issuanceBuildingModal #area").value = data.area || "";
                // document.querySelector("#issuanceBuildingModal #dateInspection").value = data.dateInspection || "";
                document.querySelector("#issuanceBuildingModal #engineer").value = data.engineer || "";
                document.querySelector("#issuanceBuildingModal #or").value = data.or || "";
                document.querySelector("#issuanceBuildingModal #dateRInspection").value = data.dateRInspection || "";
                document.querySelector("#issuanceBuildingModal #amount").value = data.amount || "";
                document.querySelector("#issuanceBuildingModal #datePayment").value = data.datePayment || "";
                // document.querySelector("#issuanceBuildingModal #dateCertificate").value = data.dateCertificate || "";
                document.querySelector("#issuanceBuildingModal #dateClaimed").value = data.dateClaimed || "";
                document.querySelector("#issuanceBuildingModal #acknowledge").value = data.acknowledge || "";


                // Set today's date for dateCertificate
                setTodayDate("#issuanceBuildingModal #dateCertificate");

                // Fetch inspection date immediately when the modal opens
                $.ajax({
                    url: '../ajax.php?action=get_inspection_date',  // Ensure the correct path
                    method: 'GET',
                    data: { appId: appId },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);  // Parse response to JSON
                            if (data.inspection_date) {
                                document.querySelector("#issuanceBuildingModal #dateInspection").value = data.inspection_date || "";
                            } else {
                                console.error('No inspection date found.');
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX request failed:', error);
                    }
                });


                document.getElementById("issuanceBuildingModal").style.display = "block";
            } else {
                document.getElementById("application_type_business").value = applicationType;
                document.getElementById("issuance_business_id").value = appId;

                document.querySelector("#issuanceBusinessModal #area").value = data.area || "";
                document.querySelector("#issuanceBusinessModal #tin").value = data.tin || "";
                document.querySelector("#issuanceBusinessModal #businessId").value = data.businessId || "";
                document.querySelector("#issuanceBusinessModal #checklist").value = data.checklist || "";
                document.querySelector("#issuanceBusinessModal #fsic").value = data.fsic || "";
                document.querySelector("#issuanceBusinessModal #or").value = data.or || "";
                document.querySelector("#issuanceBusinessModal #amount").value = data.amount || "";
                document.querySelector("#issuanceBusinessModal #datePayment").value = data.datePayment || "";
                // document.querySelector("#issuanceBusinessModal #dateCertificate").value = data.dateCertificate || "";
                document.querySelector("#issuanceBusinessModal #dateClaimed").value = data.dateClaimed || "";
                document.querySelector("#issuanceBusinessModal #received").value = data.received || "";

                const typeOccupancySelect = document.querySelector("#issuanceBusinessModal #typeOccupancy");
                if (data.typeOccupancy) {
                    typeOccupancySelect.value = data.typeOccupancy; 
                }

                // Set today's date for dateCertificate
                setTodayDate("#issuanceBusinessModal #dateCertificate");

                //  // Fetch inspection date for business modal
                // $.ajax({
                //     url: '../ajax.php?action=get_inspection_date', 
                //     method: 'GET',
                //     data: { appId: appId },
                //     success: function(response) {
                //         try {
                //             const data = JSON.parse(response);  // Parse the response
                //             const inspectionDate = data.inspection_date;
                //             document.querySelector("#issuanceBusinessModal #dateInspection").value = inspectionDate || "";
                //         } catch (e) {
                //             console.error('Error parsing response:', e);
                //         }
                //     },
                //     error: function(xhr, status, error) {
                //         console.error('AJAX request failed:', error);
                //     }
                // });

                document.getElementById("issuanceBusinessModal").style.display = "block";
            }
        }

        function saveIssuance(isGenerate) {
            let appId;
            let applicationType;

            // Get the modal values
            const buildingType = document.getElementById("application_type_building");
            const businessType = document.getElementById("application_type_business");
            const buildingIdElement = document.getElementById("issuance_building_id");
            const businessIdElement = document.getElementById("issuance_business_id");

            // Assign values for appId and applicationType
            if (buildingIdElement && buildingIdElement.value) {
                appId = buildingIdElement.value;
            } else if (businessIdElement && businessIdElement.value) {
                appId = businessIdElement.value;
            }

            if (buildingType && buildingType.value) {
                applicationType = buildingType.value;
            } else if (businessType && businessType.value) {
                applicationType = businessType.value;
            }

            // Find the active modal
            const activeModal = document.querySelector('.issuance-building-modal[style*="display: block"]');
            const form = activeModal ? activeModal.querySelector('form') : null;
            if (!form) {
                console.error("Form not found in active modal.");
                return;
            }

            const inputs = form.querySelectorAll('input, select');
            const additionals = {};
            let allImportantFilled = true;

            // Define important and optional fields for both modals
            const importantFields = applicationType === 'building' ? 
                ['dateInspection', 'fsic', 'or', 'amount', 'datePayment', 'dateCertificate'] : 
                ['typeOccupancy', 'fsic', 'or', 'amount', 'datePayment', 'dateCertificate'];

            const optionalFields = applicationType === 'building' ? 
                ['area', 'engineer', 'dateRInspection', 'dateClaimed', 'acknowledge'] : 
                ['area', 'tin', 'businessId', 'checklist', 'dateClaimed', 'received'];

            // Loop through inputs and validate
            inputs.forEach(input => {
                const value = input.value.trim();
                if (importantFields.includes(input.id)) {
                    if (!value) {
                        allImportantFilled = false;
                    } else {
                        additionals[input.id] = value; // Add to data object if filled
                    }
                } else if (optionalFields.includes(input.id)) {
                    additionals[input.id] = value || "N/A"; // Auto-fill "N/A" for empty optional fields
                }
            });

            if (!allImportantFilled) {
                Swal.fire({
                    icon: "warning",
                    title: "Incomplete Data",
                    text: "Please fill out all important fields.",
                });
                return; // Prevent submission
            }

            // Proceed with AJAX if validation passes
            $.ajax({
                url: '../ajax.php?action=save_issuance',
                method: 'POST',
                data: {
                    appId: appId,
                    additionals: JSON.stringify(additionals),
                },
                beforeSend: function() {
                    Swal.fire({
                        icon: "info",
                        title: "Please wait...",
                        timer: 60000,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(data) {
                    console.log('Response:', data);
                    if (data == 'success') {
                        if (isGenerate) {
                            let newWindow;
                            switch (applicationType) {
                                case 'building':
                                    newWindow = window.open('generate_building.php?id=' + appId, '_blank', 'width=800,height=600,toolbar=no,menubar=no,scrollbars=yes,resizable=yes');
                                    break;
                                case 'occupancy':
                                    newWindow = window.open('generate_occupancy.php?id=' + appId, '_blank', 'width=800,height=600,toolbar=no,menubar=no,scrollbars=yes,resizable=yes');
                                    break;
                                default:
                                    newWindow = window.open('generate_business.php?id=' + appId, '_blank', 'width=800,height=600,toolbar=no,menubar=no,scrollbars=yes,resizable=yes');
                            }

                            newWindow.onbeforeunload = function() {
                                location.reload();
                            };
                        } else {
                            window.location.reload();
                        }
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: "Something went wrong!",
                        });
                    }
                },
            });
        }

        document.getElementById('goButton').addEventListener('click', function() {
            const action = document.getElementById('actionSelect').value;
            
            if (action === 'delete') {
                const checkedCheckboxes = document.querySelectorAll('input[name="action[]"]:checked');
                
                const appIds = [];
                checkedCheckboxes.forEach(function(checkbox) {
                    appIds.push(checkbox.value);
                });

                if (appIds.length > 0) {
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
                                url: '../ajax.php?action=delete_application',
                                method: 'POST',
                                data: {
                                    appIds: appIds
                                },
                                success: function(data) {
                                    console.log('Response:', data);
                                    if (data === 'success') {
                                        Swal.fire(
                                            'Deleted!',
                                            'The selected applications have been deleted.',
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
                        text: "Please select at least one application to delete.",
                    });
                }
            } else if (action === 'message'){
                const checkedCheckboxes = document.querySelectorAll('input[name="action[]"]:checked');
                
                const appIds = [];
                checkedCheckboxes.forEach(function(checkbox) {
                    appIds.push(checkbox.value);
                });

                if (appIds.length > 0) {
                    document.getElementById('messageModal').style.display = 'block';

                    document.querySelector('#messageModal .btn').onclick = function() {
                        const message = document.getElementById('message').value;

                        $.ajax({
                            url: '../ajax.php?action=send_message',
                            method: 'POST',
                            data: {
                                appIds: appIds,
                                message: message,
                            },
                            beforeSend: function() {
                                Swal.fire({
                                    icon: "info",
                                    title: "Please wait...",
                                    timer: 60000,
                                    showConfirmButton: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                            },
                            success: function(data) {
                                document.getElementById('messageModal').style.display = 'none';  
                                console.log('Response:', data);
                                if (data === 'success') {
                                    Swal.fire(
                                        'Sent!',
                                        'Message sent successfully.',
                                        'success'
                                    ).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Oops...",
                                        text: "Something went wrong!",
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: "error",
                                    title: "Oops...",
                                    text: "Failed to update inspections. Please try again.",
                                });
                            }
                        });
                    };

                }
            } else {
                Swal.fire({
                    icon: "warning",
                    title: "No Action Selected",
                    text: "Please select an action from the dropdown.",
                });
            }
        });

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        function toggleAllCheckboxes(master) {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => checkbox.checked = master.checked);
        }

        window.onclick = function(event) {
            const viewModal = document.getElementById("viewClientModal");
            const editModal = document.getElementById("editClientModal");
            if (event.target === viewModal) {
                viewModal.style.display = "none";
            }
            if (event.target === editModal) {
                editModal.style.display = "none";
            }
        };
    </script>
</body>
</html>
