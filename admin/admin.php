<?php
session_start();
include '../db_connection.php';

$user_id = $_SESSION['user_id'];
$current_year = date("Y");

// Function to fetch data based on filter conditions
function fetchData($conn, $year = null, $month = null, $scope = null)
{
    $query = "SELECT MONTH(issuance.created_at) AS month, application_type, COUNT(*) as count 
              FROM applications 
              INNER JOIN issuance ON applications.id = issuance.application_id
              WHERE 1=1";

    $params = [];
    if ($year) {
        $query .= " AND YEAR(issuance.created_at) = ?";
        $params[] = $year;
    }
    if ($month) {
        $query .= " AND MONTH(issuance.created_at) = ?";
        $params[] = $month;
    }
    if ($scope) {
        $query .= " AND application_type = ?";
        $params[] = $scope;
    }

    $query .= " GROUP BY MONTH(issuance.created_at), application_type";

    $stmt = $conn->prepare($query);
    if (count($params) > 0) {
        $stmt->bind_param(str_repeat("s", count($params)), ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $stacked_data = array_fill(1, 12, [
        'building' => 0,
        'occupancy' => 0,
        'new_business_permit' => 0,
        'renewal_business_permit' => 0
    ]);

    while ($row = $result->fetch_assoc()) {
        $month = (int) $row['month'];
        $type = $row['application_type'];
        if (isset($stacked_data[$month][$type])) {
            $stacked_data[$month][$type] = (int) $row['count'];
        }
    }

    return json_encode(array_values($stacked_data));
}

// Get data for the chart using filter parameters
$stacked_json = fetchData($conn);

$type_query = "SELECT additional FROM issuance";
$type_result = $conn->query($type_query);

$types_count = [
    'assembly' => 0,
    'educational' => 0,
    'daycare' => 0,
    'healthcare' => 0,
    'residential' => 0,
    'detention' => 0,
    'mercantile' => 0,
    'business' => 0,
    'industrial' => 0,
    'storage' => 0,
    'special' => 0,
    'hotel' => 0,
    'dormitories' => 0,
    'apartment' => 0,
    'lodging' => 0,
    'single' => 0
];

while ($row = $type_result->fetch_assoc()) {
    $additional = json_decode($row['additional'], true);
    if ($additional && isset($additional['typeOccupancy'])) {
        $typeOccupancy = $additional['typeOccupancy'];
        if (isset($types_count[$typeOccupancy])) {
            $types_count[$typeOccupancy]++;
        }
    }
}

$type_json = json_encode($types_count);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Minglanilla - Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="admin-overview.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <?php include('overview-links.php') ?>
</head>

<body>

    <aside class="sidebar" id="sidebar">
        <nav class="menu" role="navigation">
            <a href="admin.php" class="menu-item active">
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
                <span>Logout</span>
            </a>
        </nav>
    </header>

    <div class="content" id="mainContent">
        <h2>Admin</h2>
        <p>Welcome, Admin.</p>

        <!-- Filters Section -->
        <div class="filters">
            <select id="filterMonth">
                <option value="">Select Month</option>
            </select>
            <select id="filterYear">
                <option value="">Select Year</option>
            </select>
            <select id="filterScope">
                <option value="">Select Scope</option>
                <option value="building">Building</option>
                <option value="occupancy">Occupancy</option>
                <option value="new_business_permit">New Business Permit</option>
                <option value="renewal_business_permit">Renewal Business Permit</option>
            </select>
            <button id="applyFilter">Apply Filter</button>
        </div>

        <div class="top-metrics">
            <div class="card">
                <p>Total Applications</p>
                <h2 id="totalApplications">0</h2>
            </div>
            <div class="card">
                <p>Total FSEC/FSIC Issued</p>
                <h2 id="totalFsecIssued">0</h2>
            </div>
            <div class="card">
                <p>Total Users</p>
                <h2 id="totalUsers">0</h2>
            </div>
            <div class="card">
                <p>Total Personnel</p>
                <h2 id="totalPersonnel">0</h2>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid">
            <div class="chart-card">
                <h3></h3>
                <canvas id="applicationChart" class="chart"></canvas>
            </div>
            <div class="chart-card">
                <h3></h3>
                <canvas id="typeChart" class="chart"></canvas>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../sidebar/sidebar-toggle.js"></script>
    <script>

        function updateCharts(clientsByType, typesCount) {
            // Update Stacked Chart
            stackedChart.data.datasets[0].data = clientsByType.map(m => m.building);
            stackedChart.data.datasets[1].data = clientsByType.map(m => m.occupancy);
            stackedChart.data.datasets[2].data = clientsByType.map(m => m.new_business_permit);
            stackedChart.data.datasets[3].data = clientsByType.map(m => m.renewal_business_permit);
            stackedChart.update();

            // Update Type Chart
            myChart.data.labels = Object.keys(typesCount);
            myChart.data.datasets[0].data = Object.values(typesCount);
            myChart.update();
        }

        document.addEventListener("DOMContentLoaded", function () {
            fetch("admin_data.php")
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.json();
                })
                .then(data => {

                    document.getElementById("totalApplications").textContent = data.totalApplications || 0;
                    document.getElementById("totalFsecIssued").textContent = data.totalFsecIssued || 0;
                    document.getElementById("totalUsers").textContent = data.totalUsers || 0;
                    document.getElementById("totalPersonnel").textContent = data.totalPersonnel || 0;
                })
                .catch(error => {
                    // Handle errors in case the fetch fails
                    console.error("There was an error fetching the data:", error);
                    document.getElementById("totalApplications").textContent = "Error";
                    document.getElementById("totalFsecIssued").textContent = "Error";
                    document.getElementById("totalUsers").textContent = "Error";
                    document.getElementById("totalPersonnel").textContent = "Error";
                });
        });


        const stackedData = <?php echo $stacked_json; ?>;

        var ctx = document.getElementById('applicationChart').getContext('2d');
        var stackedChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                datasets: [
                    {
                        label: 'Building',
                        data: stackedData.map(m => m.building),
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Occupancy',
                        data: stackedData.map(m => m.occupancy),
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'New Business Permit',
                        data: stackedData.map(m => m.new_business_permit),
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Renewal Business Permit',
                        data: stackedData.map(m => m.renewal_business_permit),
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Issued FSIC/FSEC (Stacked)',
                        font: {
                            size: 18,
                            weight: 'bold'
                        },
                        color: '#333',
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'Month',
                            font: {
                                size: 14
                            }
                        }
                    },
                    y: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'Count',
                            font: {
                                size: 14
                            }
                        }
                    }
                }
            }
        });

        const typeData = <?php echo $type_json; ?>;

        var ctx = document.getElementById('typeChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(typeData),
                datasets: [{
                    label: 'Issuance Types',
                    data: Object.values(typeData),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Issued Business Permit by Occupancy Type',
                        font: {
                            size: 18,
                            weight: 'bold'
                        },
                        color: '#333',
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        document.getElementById('applyFilter').addEventListener('click', function () {
            const month = document.getElementById('filterMonth').value;
            const year = document.getElementById('filterYear').value;
            const scope = document.getElementById('filterScope').value;

            fetch(`admin_data.php?month=${month}&year=${year}&scope=${scope}`)
                .then(response => {
                    return response.json()
                })
                .then(data => {
                    updateCharts(data.clientsByType, data.typesCount);
                })
                .catch(error => console.error('Error fetching filtered data:', error));
        });

        // Populate Month, Year, and Scope options on page load
        document.addEventListener("DOMContentLoaded", function () {
            // Populate Month Options
            const monthSelect = document.getElementById("filterMonth");
            const monthNames = [
                "January", "February", "March", "April", "May",
                "June", "July", "August", "September", "October",
                "November", "December"
            ];
            monthNames.forEach((month, index) => {
                const option = document.createElement("option");
                option.value = index + 1; // Month index (1-12)
                option.textContent = month;
                monthSelect.appendChild(option);
            });

            // Populate Year Options
            const yearSelect = document.getElementById("filterYear");
            const currentYear = new Date().getFullYear();
            for (let year = currentYear; year >= 2000; year--) {
                const option = document.createElement("option");
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            }});
    </script>
</body>
</html>