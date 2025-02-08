<?php
session_start();
include '../db_connection.php';

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index/index.php");
    exit();
}

$query = "SELECT status, COUNT(*) as count 
          FROM inspections 
          WHERE inspector_id = ? 
          GROUP BY status";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$status_data = array_fill(0, 8, 0);

while ($row = $result->fetch_assoc()) {
    $status = (int) $row['status'];
    $status_data[$status] = (int) $row['count'];
}

$status_json = json_encode($status_data);

$query = "SELECT application_type, COUNT(*) as count 
          FROM applications 
          INNER JOIN inspections ON applications.id = inspections.application_id 
          WHERE inspections.inspector_id = ? 
          GROUP BY application_type";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$type_data = [
    'building' => 0,
    'occupancy' => 0,
    'new_business_permit' => 0,
    'renewal_business_permit' => 0
];

while ($row = $result->fetch_assoc()) {
    $application_type = $row['application_type'];
    if (isset($type_data[$application_type])) {
        $type_data[$application_type] = (int) $row['count'];
    }
}

$type_json = json_encode(array_values($type_data));

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
    <title>BFP Minglanilla - Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="inspector-overview.css">
    <?php include('overview-links.php') ?>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <style>
        #calendar h2{
            border: none;
        }
        
.fc-event {
    font-size: 0.8em;
    padding: 10px;
    border-radius: 5px;
    text-align: justify;
    font-weight: bold;
}

.fc-event:hover {
    cursor: pointer;
}

.fc-event.pending-inspection {
    background-color: #fff3e0 !important; 
    border-left: 5px solid #ff9800 !important; 
    color: #ff9800 !important; 
}
.fc-event.pending-inspection:hover::after {
    content: 'pending inspection';  
    position: absolute;
    top: -25px;  
    left: 50%;
    transform: translateX(-50%);
    background-color: #777;
    color: #fff;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 0.8em;
    white-space: nowrap;
    z-index: 10;
}

/* Inspected - custom class */
.fc-event.inspected {
    background-color: #e8f5e9 !important; 
    border-left: 5px solid #388e3c !important;
    color: #388e3c !important;
}
.fc-event.inspected:hover::after {
    content: 'inspected';  
    position: absolute;
    top: -25px;  
    left: 50%;
    transform: translateX(-50%);
    background-color: #777;
    color: #fff;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 0.8em;
    white-space: nowrap;
    z-index: 10;
}
.fc-event-main-frame{
    border: none;
}
.fc-daygrid-event{
    border: 1px solid rgba(0, 0, 0, 0.1);
}
.fc-event-main{
    border: none;
}
.fc-event-title-container{
    border: none;
}
.fc-daygrid-event-harness {
    border: none;
}
.fc-event-title {
    color: #777;
}
.swal2-popup {
    font-family: 'Poppins', sans-serif !important;
    background-color: #fff !important;
    border-radius: 12px !important;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    color: #333;
}

.swal2-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #d32f2f;
    margin-bottom: 10px;
}

.swal2-content {
    font-size: 0.8em;
    color: #333;
    line-height: 1.6;
}

.swal2-confirm {
    background-color: #d32f2f;
    color: white;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 6px;
    padding: 10px 20px;
    transition: background-color 0.3s ease;
}

.swal2-confirm:hover {
    background-color: #b71c1c;
}

@keyframes bounce {
    0% { transform: translateY(-10px); opacity: 0; }
    50% { transform: translateY(0); opacity: 1; }
    100% { transform: translateY(-10px); }
}

.swal2-popup {
    animation: bounce 0.5s ease-in-out;
}
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <nav class="menu" role="navigation">
            <a href="inspector.php" class="menu-item active">
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
            <a href="../logout.php" class="header-nav-item">
                <i class="fas fa-right-from-bracket"></i>
                Logout
            </a>
        </nav>
    </header>

    <div class="content" id="mainContent">
        <h2>Dashboard</h2>

        <div id="calendar"></div>

        <div class="top-metrics">
            <div class="card">
                <p>Total FSIC/FSEC Clients</p>
                <h2 id="totalClients">0</h2>
            </div>
            <div class="card">
                <p>Total Firefighting Equipment</p>
                <h2 id="totalEquipment">0</h2>
            </div>
            <div class="card">
                <p>Total Residential Fire Incidents</p>
                <h2 id="totalIncidents">0</h2>
            </div>
        </div>

        <div class="grid">
            <div class="chart-card">
                <h3>Number of Inspections by Type</h3>
                <canvas id="typeChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Number of Inspections by Status</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>


    </div>

    <script src="../sidebar/sidebar-toggle.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: function(info, successCallback, failureCallback) {
                    fetch('fetch_inspection_data.php')  
                        .then(response => response.json())
                        .then(events => {
                            const filteredEvents = events.filter(event => 
                                event.status === 0 || event.status === 1 
                            );
                            
                            successCallback(filteredEvents);
                        })
                        .catch(error => {
                            failureCallback(error);
                        });
                },
                eventClick: function(info) {
                    const scheduleText = info.event.extendedProps.inspection_schedule;
                    const isInspected = info.event.extendedProps.status === 1;
                    const inspectionDate = info.event.extendedProps.inspection_date;
                    let inspectionDateText = 'N/A';

                    if (inspectionDate) {
                        const dateObj = new Date(inspectionDate);
                        inspectionDateText = dateObj.toLocaleDateString('en-US', {
                            month: 'short', 
                            day: 'numeric', 
                            year: 'numeric' 
                        });
                    }

                    Swal.fire({
                        title: 'Inspection Details',
                        html: ` 
                            <strong>Owner Name:</strong> ${info.event.title}<br>
                            <strong>Business Name:</strong> ${info.event.extendedProps.business_trade_name}<br>
                            <strong>Address:</strong> ${info.event.extendedProps.address}<br>
                            <strong>Contact Number:</strong> ${info.event.extendedProps.contact_number}<br>
                            <strong>Email:</strong> ${info.event.extendedProps.email_address}<br>
                            <strong>Status:</strong> 
                            <span style="color: ${isInspected ? 'green' : 'orange'};">
                                ${isInspected ? '<i class="fa fa-check-circle"></i> Inspected' : '<i class="fa fa-clock"></i> Pending Inspection'}
                            </span><br>
                            <strong>Schedule:</strong> ${scheduleText}<br>
                            <strong>Inspection Date:</strong> ${inspectionDateText}
                        `,
                        icon: 'info',
                        confirmButtonText: 'Close',
                        customClass: {
                            popup: 'sweetalert-popup',
                            title: 'sweetalert-title',
                            content: 'sweetalert-content',
                            confirmButton: 'sweetalert-btn'
                        }
                    });
                },
                eventClassNames: function(info) {
                    if (info.event.extendedProps.status === 0) { 
                        return ['pending-inspection']; 
                    } else if (info.event.extendedProps.status === 1) { 
                        return ['inspected']; 
                    }
                    return [];  
                }
            });

            calendar.render();
        });

        document.addEventListener("DOMContentLoaded", function () {
            fetch("dashboard-data.php")
                .then(response => response.json())
                .then(data => {
                    console.log("Fetched Data:", data);

                    document.getElementById("totalClients").textContent = data.totalClients;
                    document.getElementById("totalEquipment").textContent = data.totalEquipment;
                    document.getElementById("totalIncidents").textContent = data.totalIncidents;


                })
                .catch(error => console.error("Error fetching dashboard data:", error));
        });
        const typeData = <?php echo $type_json; ?>;

        var ctx = document.getElementById('typeChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Building', 'Occupancy', 'New Business Permit', 'Renewal Business Permit'],
                datasets: [{
                    data: typeData,
                    backgroundColor: [
                        '#3E4A59',  
                        '#006F80',  
                        '#8C9A9E',  
                        '#A9B6B8'   
                    ],
                    hoverOffset: 4, 
                }]
            },
            options: {
                responsive: true,
                cutoutPercentage: 80, 
                plugins: {
                    title: {
                        display: true,
                        font: {
                            size: 20,
                            weight: 'bold',
                            family: "'Helvetica Neue', Arial, sans-serif", 
                        },
                        color: '#333', 
                        padding: {
                            top: 20,
                            bottom: 30
                        }
                    },
                    legend: {
                        position: 'bottom',  
                        labels: {
                            usePointStyle: true,
                            font: {
                                size: 14,
                                family: "'Helvetica Neue', Arial, sans-serif", 
                            },
                            padding: 20, 
                            boxWidth: 10, 
                            color: '#666'  
                        }
                    },
                    tooltip: {
                        backgroundColor: '#444',  
                        titleFont: {
                            size: 14,
                            weight: 'bold',
                            family: "'Helvetica Neue', Arial, sans-serif",
                        },
                        bodyFont: {
                            size: 12,
                            family: "'Helvetica Neue', Arial, sans-serif",
                        },
                        bodyColor: '#fff',  
                        borderColor: '#ddd',  
                        borderWidth: 1,
                        cornerRadius: 8,  
                        padding: 12, 
                    }
                }
            },
        });

        const statusData = <?php echo $status_json; ?>;

        var ctx = document.getElementById('statusChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [
                    'Pending Inspection', 
                    'Inspected', 
                    'Waiting for Compliance', 
                    'Complied', 
                    'Notice to Comply Issued', 
                    'Notice to Correct Violation', 
                    'Issued Abandonment Order', 
                    'Issued Closure Order'
                ],
                datasets: [{
                    data: statusData,
                    backgroundColor: [
                        '#4E6D7A', 
                        '#7A8D9B', 
                        '#A1BCCF', 
                        '#B8D6E6', 
                        '#C9C6D1',
                        '#A4A49B', 
                        '#717D7E', 
                        '#C5D1C3'  
                    ],
                    hoverOffset: 6, 
                }]
            },
            options: {
                responsive: true,
                cutoutPercentage: 80, 
                plugins: {
                    title: {
                        display: true,
                        font: {
                            size: 20,
                            weight: 'bold',
                            family: "'Helvetica Neue', Arial, sans-serif",
                        },
                        color: '#333',
                        padding: {
                            top: 20,
                            bottom: 30
                        }
                    },
                    legend: {
                        position: 'bottom',  
                        labels: {
                            usePointStyle: true,
                            font: {
                                size: 14,
                                family: "'Helvetica Neue', Arial, sans-serif", 
                            },
                            padding: 20, 
                            boxWidth: 10, 
                            color: '#666'  
                        }
                    },
                    tooltip: {
                        backgroundColor: '#444',  
                        titleFont: {
                            size: 14,
                            weight: 'bold',
                            family: "'Helvetica Neue', Arial, sans-serif",
                        },
                        bodyFont: {
                            size: 12,
                            family: "'Helvetica Neue', Arial, sans-serif",
                        },
                        bodyColor: '#fff',  
                        borderColor: '#ddd', 
                        borderWidth: 1,
                        cornerRadius: 8,  
                        padding: 12,  
                    }
                }
            },
        });
    </script>
</body>

</html>