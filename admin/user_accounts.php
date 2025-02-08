<?php
    include '../db_connection.php';

    $sql = "SELECT id, full_name, email, role, username, status FROM users";
    $result = $conn->query($sql);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_id'])) {
        $id = $_POST['update_user_id'];
        $username = $_POST['username'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $status = $_POST['status'];

        $update_sql = "UPDATE users SET username=?, full_name=?, email=?, role=?, status=? WHERE id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssssi", $username, $full_name, $email, $role, $status, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: user_accounts.php");
        exit;
    }

    $search = '';
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
        $sql .= " WHERE username LIKE ? OR email LIKE ? OR full_name LIKE ?";
        $stmt = $conn->prepare($sql);
        $likeSearch = "%{$search}%";
        $stmt->bind_param("sss", $likeSearch, $likeSearch, $likeSearch);
    } else {
        $stmt = $conn->prepare($sql);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Minglanilla - User Accounts</title>
    <?php include('../dataTables/dataTable-links.php') ?>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/user-accounts.css">
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <nav class="menu" role="navigation">
            <a href="admin.php" class="menu-item">
                <i class="fas fa-house-chimney"></i> 
                <span>Home</span>
            </a>
            <div class="menu-item clients">
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
            <div class="menu-item clients active">
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
        <h2>User Accounts</h2>
        <div class="action-bar">
            <div class="action-select">
                <label for="actionSelect">Action:</label>
                <select id="actionSelect">
                    <option value="">Select an action</option>
                    <option value="delete">Delete</option>
                </select>
                <button id="goButton">Go</button>
            </div>

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
                        <select id="roleFilter" onchange="filterTable('role', this.value)">
                            <option value="all">All Roles</option>
                            <option value="Admin">Admin</option>
                            <option value="Staff">Staff</option>
                            <option value="Inspector">Inspector</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <select id="statusFilter" onchange="filterTable('status', this.value)">
                            <option value="all">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Approved">Approved</option>
                            <option value="Declined">Declined</option>
                            <option value="Suspended">Suspended</option>
                        </select>
                    </div>

                    <!-- Clear Filters Button -->
                    <button class="clear-filters" onclick="clearFilters()">Clear</button>
                </div>
            </div>

        </div>

        <div class="table-container">
            <table id="userTable" class="table table-striped nowrap">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                            <th>Username</th>
                            <th>Email Address</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userBody">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><input type="checkbox" name="action[]" value="<?= $row['id'] ?>" class="row-checkbox"></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td>
                                        <!-- The action dropdown, initially populated with default options -->
                                        <select onchange="handleAction(this, <?php echo $row['id']; ?>)" style="padding: 5px; border-radius: 4px;" id="action-<?php echo $row['id']; ?>">
                                            <!-- Options for all users -->
                                            <option value="">Select</option>
                                            <option value="view">View</option>
                                            <option value="edit">Update</option>
                                            <option value="suspend">Suspend</option>
                                        </select>
            
                                        <!-- The Approve/Decline dropdown for Pending status users, initially hidden -->
                                        <select onchange="handleAction(this, <?php echo $row['id']; ?>)" style="padding: 5px; border-radius: 4px; display: none;" id="pending-action-<?php echo $row['id']; ?>">
                                            <option value="">Select</option>
                                            <option value="approve">Approve</option>
                                            <option value="decline">Decline</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
        </div>


        <div id="viewUserModal" class="profile-modal" style="display: none;">
    <!-- Modal Header -->
    <div class="profile-header">
        <span class="close-btn" onclick="closeModal('viewUserModal')">&times;</span>
        <div class="header-content">
            <div class="profile-info">
                <h2 id="modalFullName">User Details</h2>
                <p id="modalRole">Detailed Information</p>
            </div>
        </div>
    </div>

    <!-- Scrollable Content -->
    <div class="profile-body">
        <section>
            <h3>Details</h3>
            <ul id="userInfo" class="info-list">
                <li><strong>Username:</strong> <span id="modalUsername"></span></li>
                <li><strong>Email:</strong> <span id="modalEmail"></span></li>
                <li><strong>Full Name:</strong> <span id="modalFullName"></span></li>
                <li><strong>Role:</strong> <span id="modalRole"></span></li>
                <li><strong>Status:</strong> <span id="modalStatus"></span></li>
            </ul>
        </section>
    </div>

    <!-- Modal Footer -->
    <div class="profile-footer">
        <button id="closeUserProfileButton" onclick="closeModal('viewUserModal')">Close</button>
    </div>
</div>


        <div id="updateUserModal" class="profile-modal" style="display: none;">
    <div class="profile-header">
        <span class="close-btn" onclick="closeModal('updateUserModal')">&times;</span>
        <div class="header-content">
            <h2>Update User</h2>
            <p>Edit user details and save changes</p>
        </div>
    </div>
    <div class="profile-body">
        <form id="updateForm" method="POST">
            <input type="hidden" name="update_user_id" id="update_user_id">
            <ul id="userInfo" class="info-list">
               
                    <div class="form-row">
                        <div class="form-group">
                            <label for="modalUpdateUsername"><strong>Username:</strong></label>
                            <input type="text" name="username" id="modalUpdateUsername" required>
                        </div>
                        <div class="form-group">
                            <label for="modalUpdateEmail"><strong>Email:</strong></label>
                            <input type="email" name="email" id="modalUpdateEmail" required>
                        </div>
                    </div>
               
                    <div class="form-row">
                        <div class="form-group">
                            <label for="modalUpdateFullName"><strong>Full Name:</strong></label>
                            <input type="text" name="full_name" id="modalUpdateFullName" required>
                        </div>
                        <div class="form-group">
                            <label for="modalUpdateRole"><strong>Role:</strong></label>
                            <select name="role" id="modalUpdateRole">
                                <option value="Admin">Admin</option>
                                <option value="Inspector">Inspector</option>
                                <option value="Staff">Staff</option>
                            </select>
                        </div>
                    </div>
               
                    <div class="form-row">
                        <div class="form-group">
                            <label for="modalUpdateStatus"><strong>Status:</strong></label>
                            <select name="status" id="modalUpdateStatus">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                <div class="profile-footer">
                    <button type="submit" class="btn">Update</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('updateUserModal')">Close</button>
                </div>
            </form>
        </div>
    </div>

        <!-- Suspend User Modal -->
<div id="suspendUserModal" class="action-modal" style="display: none;">
    <div class="action-modal-header">
        <span class="close-btn" onclick="closeModal('suspendUserModal')">&times;</span>
        <div class="header-content">
            <h2>Suspend User</h2>
            <p>Specify suspension dates for the selected user</p>
        </div>
    </div>
    <div class="action-modal-body">
        <form id="suspendForm" method="POST">
            <input type="hidden" id="suspendUserId" name="user_id">
            <ul class="info-list">
                <li><strong>Username:</strong> <span name="suspendUsername" id="suspendUsername"></span></li>
                <li><strong>Full Name:</strong> <span name="suspendFullName" id="suspendFullName"></span></li>
                <li>
                    <label for="startDate"><strong>Start Date:</strong></label>
                    <input type="date" id="startDate" name="start_date" required>
                </li>
                <li>
                    <label for="endDate"><strong>End Date:</strong></label>
                    <input type="date" id="endDate" name="end_date" required>
                </li>
            </ul>
            <div class="action-modal-footer">
                <button type="submit" class="btn-primary" onclick="confirmSuspend(event)">Suspend</button>
                <button type="button" onclick="closeModal('suspendUserModal')" class="btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Approve User Modal -->
<div id="approveUserModal" class="action-modal" style="display: none;">
    <div class="action-modal-header">
        <span class="close-btn" onclick="closeModal('approveUserModal')">&times;</span>
        <div class="header-content">
            <h2>Approve User</h2>
            <p>Review and approve user details</p>
        </div>
    </div>
    <div class="action-modal-body">
        <form id="approveForm" method="POST">
            <input type="hidden" id="approve_user_id" name="approve_user_id">
            <ul class="info-list">
                <li><strong>Username:</strong> <span id="modalUsernameApprove"></span></li>
                <li><strong>Email:</strong> <span id="modalEmailApprove"></span></li>
                <li><strong>Full Name:</strong> <span id="modalFullNameApprove"></span></li>
                <li><strong>Role:</strong> <span id="modalRoleApprove"></span></li>
                <li><strong>Status:</strong> <span id="modalStatusApprove"></span></li>
            </ul>
            <div class="action-modal-footer">
                <button type="submit" class="btn-primary" onclick="confirmApprove(event)">Approve</button>
                <button type="button" onclick="closeModal('approveUserModal')" class="btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Decline User Modal -->
<div id="declineUserModal" class="action-modal" style="display: none;">
    <div class="action-modal-header">
        <span class="close-btn" onclick="closeModal('declineUserModal')">&times;</span>
        <div class="header-content">
            <h2>Decline User</h2>
            <p>Review and decline user details</p>
        </div>
    </div>
    <div class="action-modal-body">
        <form id="declineForm" method="POST">
            <input type="hidden" id="decline_user_id" name="decline_user_id">
            <ul class="info-list">
                <li><strong>Username:</strong> <span id="modalUsernameDecline"></span></li>
                <li><strong>Email:</strong> <span id="modalEmailDecline"></span></li>
                <li><strong>Full Name:</strong> <span id="modalFullNameDecline"></span></li>
                <li><strong>Role:</strong> <span id="modalRoleDecline"></span></li>
                <li><strong>Status:</strong> <span id="modalStatusDecline"></span></li>
            </ul>
            <div class="action-modal-footer">
                <button type="submit" class="btn-primary" onclick="confirmDecline(event)">Decline</button>
                <button type="button" onclick="closeModal('declineUserModal')" class="btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

    </div>
                            
    <script src="../sidebar/sidebar-toggle.js"></script>r
    <script>
        const currentFilters = {
            role: 'all',
            status: 'all',
        };

        function toggleFilterPanel() {
            const panel = document.getElementById("filterPanel");
            panel.style.display = (panel.style.display === "none" || panel.style.display === "") ? "block" : "none";
        }

        function filterTable(filterType, filterValue) {

            currentFilters[filterType] = filterValue;

            const rows = document.querySelectorAll('#userBody tr');

            rows.forEach(row => {
                const role = row.cells[4].textContent.trim();
                const status = row.cells[5].textContent.trim();

                const matchesRole = currentFilters.role === 'all' || role === currentFilters.role;
                const matchesStatus = currentFilters.status === 'all' || status === currentFilters.status;

                if (matchesRole && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function clearFilters() {
            currentFilters.role = 'all';
            currentFilters.status = 'all';
            document.getElementById('roleFilter').value = 'all';
            document.getElementById('statusFilter').value = 'all';
            filterTable(); 
        }

         $(document).ready(function() {
            const table = $('#userTable').DataTable({
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
            const table = document.getElementById("userBody");
            const rows = table.getElementsByTagName("tr");

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName("td");
                let found = false;

                for (let j = 1; j < cells.length; j++) { 
                    if (cells[j].innerText.toLowerCase().includes(input)) {
                        found = true;
                        break;
                    }
                }

                rows[i].style.display = found ? "" : "none"; 
            }
        }

            document.addEventListener("DOMContentLoaded", function() {
                var rows = document.querySelectorAll("#userBody tr");
                
                rows.forEach(function(row) {
                    var status = row.querySelector("td:nth-child(6)").textContent.trim();
                    var actionSelect = row.querySelector("select[id^='action-']");
                    var pendingActionSelect = row.querySelector("select[id^='pending-action-']");
                    
                    // Show Approve/Decline dropdown if status is 'Pending'
                    if (status === 'Pending' || status === 'Decline') {
                        actionSelect.style.display = 'none'; // Hide default options
                        pendingActionSelect.style.display = 'inline'; // Show Approve/Decline options
                    } else {
                        actionSelect.style.display = 'inline'; // Show default options
                        pendingActionSelect.style.display = 'none'; // Hide Approve/Decline options
                    }
                });
            });

            function handleAction(selectElement, userId) {
                const action = selectElement.value;
                if (action === 'view') {
                    loadUserData(userId);
                    document.getElementById('viewUserModal').style.display = 'block';
                }else if(action === 'edit'){
                    loadUpdateUserData(userId);
                    document.getElementById('updateUserModal').style.display = 'block';
                }else if(action === 'suspend'){
                    loadSuspendData(userId);
                    document.getElementById('suspendUserModal').style.display = 'block';
                }else if(action === 'approve') {
                    loadApproveData(userId);
                    document.getElementById('approveUserModal').style.display = 'block';
                }else if(action === 'decline') {
                    loadDeclineData(userId);
                    document.getElementById('declineUserModal').style.display = 'block';

                }
                selectElement.value = '';
            }

            function loadDeclineData(userId) {
                fetch(`get_user.php?id=${userId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            console.error('Error:', data.error);
                            alert('User data not found.');
                            return;
                        }
                        document.getElementById('modalUsernameDecline').innerText = data.username;
                        document.getElementById('modalEmailDecline').innerText = data.email;
                        document.getElementById('modalFullNameDecline').innerText = data.full_name;
                        document.getElementById('modalRoleDecline').innerText = data.role;
                        document.getElementById('modalStatusDecline').innerText = data.status ?? 'Inactive';
                        document.getElementById('decline_user_id').value = data.id;
                    })
            }

            function loadApproveData(userId) {
                fetch(`get_user.php?id=${userId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            console.error('Error:', data.error);
                            alert('User data not found.');
                            return;
                        }
                        document.getElementById('modalUsernameApprove').innerText = data.username;
                        document.getElementById('modalEmailApprove').innerText = data.email;
                        document.getElementById('modalFullNameApprove').innerText = data.full_name;
                        document.getElementById('modalRoleApprove').innerText = data.role;
                        document.getElementById('modalStatusApprove').innerText = data.status ?? 'Inactive';
                        document.getElementById('approve_user_id').value = data.id;
                    })
            }

            function loadSuspendData(userId) {
                fetch(`get_user.php?id=${userId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            console.error('Error:', data.error);
                            alert('User data not found.');
                            return;
                        }
                        document.getElementById('suspendUserId').value = data.id;
                        document.getElementById('suspendUsername').innerText = data.username;
                        document.getElementById('suspendFullName').innerText = data.full_name;
                    })
                    .catch(error => console.error('Error fetching user data:', error));
            }

            function loadUserData(userId) {
                // Fetch user data from the server via AJAX
                fetch(`get_user.php?id=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('modalUsername').innerText = data.username;
                        document.getElementById('modalEmail').innerText = data.email;
                        document.getElementById('modalFullName').innerText = data.full_name;
                        document.getElementById('modalRole').innerText = data.role;
                        document.getElementById('modalStatus').innerText = data.status ?? 'Inactive';
                        // document.getElementById('update_user_id').innerText = data.id; // Store the user ID for the update
                    })
                    .catch(error => console.error('Error fetching user data:', error));
            }

            function loadUpdateUserData(userId) {
                // Fetch user data from the server via AJAX
                fetch(`get_user.php?id=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('modalUpdateUsername').value = data.username;
                        document.getElementById('modalUpdateEmail').value = data.email;
                        document.getElementById('modalUpdateFullName').value = data.full_name;
                        document.getElementById('modalUpdateRole').value = data.role;
                        document.getElementById('modalUpdateStatus').value = data.status;
                        document.getElementById('update_user_id').value = data.id; // Store the user ID for the update
                    })
                    .catch(error => console.error('Error fetching user data:', error));
            }

            function closeModal(modalId) {
                document.getElementById(modalId).style.display = 'none';
            }

            function confirmDecline(event) {
                event.preventDefault();

                const formData = $('#declineForm').serialize(); // Assuming form contains user_id and other necessary data

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to decline this user account.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, decline!',
                    cancelButtonText: 'No, cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'decline_user.php', // Endpoint for approving the user
                            type: 'POST',
                            data: formData, // Sending form data, including user_id
                            success: function(response) {
                                console.log('Server response:', response);
                                const res = JSON.parse(response);
                                if (res.status === 'success') {
                                    Swal.fire('Declined!', res.message, 'success').then(() => {
                                        location.reload(); // Reload the page to reflect the updated status
                                    });
                                } else {
                                    Swal.fire('Error!', res.message, 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX Error:', error);
                                Swal.fire('Error!', 'There was an issue declining the user.', 'error');
                            }
                        });
                    }
                });
            }

            function confirmApprove(event) {
                event.preventDefault();

                const formData = $('#approveForm').serialize(); // Assuming form contains user_id and other necessary data

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to approve this user account.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, approve!',
                    cancelButtonText: 'No, cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'approve_user.php', // Endpoint for approving the user
                            type: 'POST',
                            data: formData, // Sending form data, including user_id
                            success: function(response) {
                                console.log('Server response:', response);
                                const res = JSON.parse(response);
                                if (res.status === 'success') {
                                    Swal.fire('Approved!', res.message, 'success').then(() => {
                                        location.reload(); // Reload the page to reflect the updated status
                                    });
                                } else {
                                    Swal.fire('Error!', res.message, 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX Error:', error);
                                Swal.fire('Error!', 'There was an issue approving the user.', 'error');
                            }
                        });
                    }
                });
            }

            function confirmSuspend(event) {
                event.preventDefault();

                 // Get the values of the date fields
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;

                // Validation: Check if start date is empty
                if (!startDate) {
                    Swal.fire('Error!', 'Start date is required.', 'error');
                    return;  // Stop further execution if validation fails
                }

                // Validation: Check if end date is empty
                if (!endDate) {
                    Swal.fire('Error!', 'End date is required.', 'error');
                    return;
                }

                // Validation: Check if the start date is greater than the end date
                const start = new Date(startDate);
                const end = new Date(endDate);

                if (start > end) {
                    Swal.fire('Error!', 'End date cannot be earlier than start date.', 'error');
                    return;
                }

                const formData = $('#suspendForm').serialize();

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to suspend this user.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, suspend!',
                    cancelButtonText: 'No, cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('Serialized form data:', formData);
                        $.ajax({
                            url: 'suspend_user.php',
                            type: 'POST',
                            data: formData,
                            success: function(response) {
                                console.log('Server response:', response);
                                const res = JSON.parse(response);
                                if (res.status === 'success') {
                                    Swal.fire('Suspended!', res.message, 'success').then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', res.message, 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX Error:', error);
                                Swal.fire('Error!', 'There was an issue suspending the user.', 'error');
                            }
                        });
                    }
                });
            }

            document.getElementById('goButton').addEventListener('click', function() {
                const action = document.getElementById('actionSelect').value;

                if (action === 'delete'){
                    const checkedCheckboxes = document.querySelectorAll('input[name="action[]"]:checked');

                    const userIds = [];
                    checkedCheckboxes.forEach(function(checkbox) {
                        userIds.push(checkbox.value);
                    });

                    if (userIds.length > 0) {
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
                                    url: '../ajax.php?action=delete_user',
                                    method: 'POST',
                                    data: {
                                        userIds: userIds
                                    },
                                    success: function(data) {
                                        console.log('Response:', data);
                                        if (data === 'success') {
                                            Swal.fire(
                                                'Deleted!',
                                                'The selected users have been deleted.',
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
                            text: "Please select at least one user to delete.",
                        });
                    }
                }
            })
    </script>
</body>
</html>
