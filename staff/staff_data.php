<?php
session_start();
include '../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

$year = $data['year'] ?? null;
$month = $data['month'] ?? null;
$scope = $data['scope'] ?? 'all';  // Get the scope filter

$filterCondition = "";
if ($year) {
    $filterCondition .= "YEAR(created_at) = $year";
}
if ($month) {
    $filterCondition .= ($filterCondition ? " AND " : "") . "MONTH(created_at) = $month";
}

// Apply scope filter
if ($scope && $scope !== 'all') {
    if ($scope == 'statusChart') {
        // Only return status data
        $clientsByType = [];
    } elseif ($scope == 'typeChart') {
        // Only return type data
        $applicationStatusChart = [];
    }
}

// Metrics queries
$totalApplications = $conn->query("SELECT COUNT(*) AS total FROM applications" . ($filterCondition ? " WHERE $filterCondition" : ""))->fetch_assoc()['total'];
$totalPendingApplications = $conn->query("SELECT COUNT(*) AS total FROM applications WHERE issuance_status = 'Pending'" . ($filterCondition ? " AND $filterCondition" : ""))->fetch_assoc()['total'];
$totalCompletedApplications = $conn->query("SELECT COUNT(*) AS total FROM applications WHERE issuance_status = 'Completed'" . ($filterCondition ? " AND $filterCondition" : ""))->fetch_assoc()['total'];

// Type-wise data
$clientsByType = [
    'building' => 0,
    'occupancy' => 0,
    'new_business_permit' => 0,
    'renewal_business_permit' => 0
];
if ($scope == 'all' || $scope == 'typeChart') {
    $result = $conn->query("SELECT application_type, COUNT(*) AS count FROM applications" . ($filterCondition ? " WHERE $filterCondition" : "") . " GROUP BY application_type");
    while ($row = $result->fetch_assoc()) {
        $application_type = strtolower($row['application_type']);  // Ensure lowercase for consistent keys
        if (isset($clientsByType[$application_type])) {
            $clientsByType[$application_type] = (int) $row['count'];
        }
    }
}

// Status-wise data
$applicationStatusChart = [
    'Pending' => 0,
    'Completed' => 0
];
if ($scope == 'all' || $scope == 'statusChart') {
    $result = $conn->query("SELECT issuance_status AS status, COUNT(*) AS count FROM applications" . ($filterCondition ? " WHERE $filterCondition" : "") . " GROUP BY issuance_status");
    while ($row = $result->fetch_assoc()) {
        $status = ucfirst(strtolower($row['status']));  // Ensure 'Pending' or 'Completed'
        if (isset($applicationStatusChart[$status])) {
            $applicationStatusChart[$status] = (int) $row['count'];
        }
    }
}

echo json_encode([
    'totalApplications' => $totalApplications,
    'totalPendingApplications' => $totalPendingApplications,
    'totalCompletedApplications' => $totalCompletedApplications,
    'clientsByType' => $clientsByType,
    'applicationStatusChart' => $applicationStatusChart
]);

$conn->close();
?>
