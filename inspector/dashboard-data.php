<?php
include_once('../db_connection.php');

// Get query parameters
$year = isset($_GET['year']) ? $_GET['year'] : null;
$month = isset($_GET['month']) ? $_GET['month'] : null;
$scope = isset($_GET['scope']) ? $_GET['scope'] : 'all';

// Get total clients
$totalClients = $conn->query("SELECT COUNT(*) AS total FROM applications")->fetch_assoc()['total'];

// Get total firefighting equipment
$totalEquipment = $conn->query("SELECT SUM(quantity) AS total FROM equipment")->fetch_assoc()['total'];

// Get total fire incidents
$totalIncidents = $conn->query("SELECT COUNT(*) AS total FROM incident_reports")->fetch_assoc()['total'];

// Clients by type
$clientsByType = [];
$query = "SELECT application_type, COUNT(*) AS count FROM applications GROUP BY application_type";
if ($year && $month) {
    $query .= " WHERE YEAR(created_at) = $year AND MONTH(created_at) = $month";
}
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $clientsByType[$row['application_type']] = $row['count'];
}

// Inspection status
$inspectionStatus = [];
$query = "SELECT status, COUNT(*) AS count FROM inspections GROUP BY status";
if ($year && $month) {
    $query .= " WHERE YEAR(inspected_at) = $year AND MONTH(inspected_at) = $month";
}
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $inspectionStatus[$row['status']] = $row['count'];
}

// Equipment status
$equipmentStatus = [];
$query = "SELECT status, SUM(quantity) AS count FROM equipment GROUP BY status";
if ($year && $month) {
    $query .= " WHERE YEAR(purchased_at) = $year AND MONTH(purchased_at) = $month";
}
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $equipmentStatus[$row['status']] = $row['count'];
}

// Incident status
$incidentStatus = [];
$query = "SELECT status, COUNT(*) AS count FROM incident_reports GROUP BY status";
if ($year && $month) {
    $query .= " WHERE YEAR(report_date) = $year AND MONTH(report_date) = $month";
}
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $incidentStatus[$row['status']] = $row['count'];
}

// Return filtered data
echo json_encode([
    'totalClients' => $totalClients,
    'totalEquipment' => $totalEquipment,
    'totalIncidents' => $totalIncidents,
    'clientsByType' => $clientsByType,
    'inspectionStatus' => $inspectionStatus,
    'equipmentStatus' => $equipmentStatus,
    'incidentStatus' => $incidentStatus
]);

$conn->close();
?>
