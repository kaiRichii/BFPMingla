<?php
session_start();
include '../db_connection.php';

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index/index.php");
    exit();
}

$query = "SELECT application_type, COUNT(*) as count FROM applications GROUP BY application_type";
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
        $type_data[$application_type] = (int) $row['count'];
        $applicant_count += (int) $row['count'];
    }
}

$type_json = json_encode(array_values($type_data));

$query = "SELECT issuance_status, COUNT(*) as count FROM applications GROUP BY issuance_status";
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
        $status_data[$issuance_status] = (int) $row['count'];
    }
}

$status_json = json_encode(array_values($status_data));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP - Staff</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="staff-overview.css">
    <?php include('overview-links.php') ?>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <!-- Logo Section -->
        <nav class="menu" role="navigation">
            <a href="staff.php" class="menu-item active">
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
            <a href="report.php" class="menu-item clients">
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
        <h2>Dashboard Overview</h2>
        <!-- Filters Section -->
        <div class="filters">
            <select id="filterMonth">
                <option value="">All Months</option>
                <option value="1">January</option>
                <option value="2">February</option>
                <option value="3">March</option>
                <option value="4">April</option>
                <option value="5">May</option>
                <option value="6">June</option>
                <option value="7">July</option>
                <option value="8">August</option>
                <option value="9">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>
            <select id="filterYear">
                <option value="">All Years</option>
            </select>
            <select id="filterScope">
                <option value="all">Apply to All</option>
                <option value="statusChart">Applications by Application Status</option>
                <option value="typeChart">Applications by Application Type</option>
                <option value="metrics">Metrics Only</option>
            </select>
            <button id="applyFilter">Apply Filter</button>
        </div>

        <!-- Metrics Section -->
        <div class="top-metrics">
            <div class="card">
                <p>Total Applications</p>
                <h2 id="totalApplications">Loading...</h2>
            </div>

            <div class="card">
                <p>Pending Applications</p>
                <h2 id="totalPendingApplications">Loading...</h2>
            </div>

            <div class="card">
                <p>Completed Applications</p>
                <h2 id="totalCompletedApplications">Loading...</h2>
            </div>

            <!-- <div class="card">
                <p>Applications with Schedule</p>
                <h2 id="totalApplicationsWithSomeSchedule">Loading...</h2>
            </div> -->
        </div>


        <!-- Charts Section -->
        <div class="grid">
            <div class="chart-card">
                <h3>Number of Applications by Type</h3>
                <canvas id="typeChart" class="chart"></canvas>
            </div>

            <div class="chart-card">
                <h3>Number of Applications by Status</h3>
                <canvas id="statusChart" class="chart" style="width: 10px; height: 10px"></canvas>
            </div>
        </div>

    </div>

    <script src="../sidebar/sidebar-toggle.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Fetch initial data when page loads
            fetchDataAndUpdateCharts();

            // Populate the Year dropdown
            const filterYear = document.getElementById('filterYear');
            for (let year = new Date().getFullYear(); year >= 2000; year--) {
                const optionYear = document.createElement('option');
                optionYear.value = year;
                optionYear.textContent = year;
                filterYear.appendChild(optionYear);
            }
        });

        // Handle filter button click
        document.getElementById('applyFilter').addEventListener('click', function () {
            const selectedYear = document.getElementById('filterYear').value;
            const selectedMonth = document.getElementById('filterMonth').value;
            const selectedScope = document.getElementById('filterScope').value;  // Get the scope filter

            fetch('staff_data.php', {
                method: 'POST',
                body: JSON.stringify({
                    year: selectedYear,
                    month: selectedMonth,
                    scope: selectedScope   // Send scope as part of the request
                }),
                headers: { 'Content-Type': 'application/json' }
            })
                .then(response => response.json())
                .then(data => updateMetricsAndCharts(data));
        });

        function fetchDataAndUpdateCharts() {
            // Fetch data without any filter
            fetch('staff_data.php', {
                method: 'POST',
                body: JSON.stringify({
                    year: '', 
                    month: '', 
                    scope: 'all' 
                }),
                headers: { 'Content-Type': 'application/json' }
            })
                .then(response => response.json())
                .then(data => updateMetricsAndCharts(data));
        }

        function updateMetricsAndCharts(data) {
    // Update metrics on the page
    document.getElementById('totalApplications').textContent = data.totalApplications;
    document.getElementById('totalPendingApplications').textContent = data.totalPendingApplications;
    document.getElementById('totalCompletedApplications').textContent = data.totalCompletedApplications;

    // Clear the previous charts (destroy the existing chart instances)
    if (window.typeChartInstance) {
        window.typeChartInstance.destroy();  // Destroy the type chart instance
    }
    if (window.statusChartInstance) {
        window.statusChartInstance.destroy();  // Destroy the status chart instance
    }

    if (data.clientsByType) {
        window.typeChartInstance = new Chart(document.getElementById('typeChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Building', 'Occupancy', 'New Business Permit', 'Renewal Business Permit'],
                datasets: [{
                    data: [
                        data.clientsByType.building,
                        data.clientsByType.occupancy,
                        data.clientsByType.new_business_permit,
                        data.clientsByType.renewal_business_permit
                    ],
                    backgroundColor: '#007bff',
                    borderColor: '#0056b3',
                    borderWidth: 1
                }]
            }
        });
    }

    // Create new Status Chart (Pie chart for application statuses)
    if (data.applicationStatusChart) {
        window.statusChartInstance = new Chart(document.getElementById('statusChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: ['Pending', 'Completed'],
                datasets: [{
                    data: [
                        data.applicationStatusChart.Pending || 0,
                        data.applicationStatusChart.Completed || 0
                    ],
                    backgroundColor: ['#FF6384', '#36A2EB']
                }]
            }
        });
    }
}


    </script>
</body>

</html>