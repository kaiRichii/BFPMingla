<?php
session_start();
include '../db_connection.php'; 

$user_id = $_SESSION['user_id'];

$month = $_GET['month'] ?? null;
$year = $_GET['year'] ?? null;
$scope = $_GET['scope'] ?? null;

// Initialize response data
$response = [
    'totalApplications' => 0,
    'totalFsecIssued' => 0,
    'totalUsers' => 0,
    'totalPersonnel' => 0,
    'typesCount' => [],
    'clientsByType' => [],
    'issuedCount' => [],
];

// Total Applications
$query = "SELECT COUNT(*) as total FROM applications";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$response['totalApplications'] = $row['total'] ?? 0;

// Total Users
$query = "SELECT COUNT(*) as total FROM users";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$response['totalUsers'] = $row['total'] ?? 0;

// Total Personnel
$query = "SELECT COUNT(*) as total FROM personnel";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$response['totalPersonnel'] = $row['total'] ?? 0;

// Total FSEC Issued
$query = "SELECT COUNT(*) as total FROM issuance";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$response['totalFsecIssued'] = $row['total'] ?? 0;

// Clients by Type (Stacked Chart)
$query = "SELECT MONTH(issuance.created_at) AS month, application_type, COUNT(*) as count 
          FROM applications 
          INNER JOIN issuance ON applications.id = issuance.application_id
          WHERE YEAR(issuance.created_at) = ?" . 
          ($month ? " AND MONTH(issuance.created_at) = ?" : "") . 
          ($scope ? " AND application_type = ?" : "") . 
          " GROUP BY MONTH(issuance.created_at), application_type";

$stmt = $conn->prepare($query);
if ($month && $scope && $scope !== "all") {
    $stmt->bind_param("iis", $year, $month, $scope);  // Bind year, month, and scope (application_type)
} elseif ($month) {
    $stmt->bind_param("ii", $year, $month);  // Bind only year and month
} elseif ($scope && $scope !== "all") {
    $stmt->bind_param("is", $year, $scope);  // Bind year and scope (application_type)
} else {
    $stmt->bind_param("i", $year);  // Bind only year
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
    $month = (int)$row['month'];
    $type = $row['application_type'];  // Correct column name
    if (isset($stacked_data[$month][$type])) {
        $stacked_data[$month][$type] = (int)$row['count'];
    }
}

$response['clientsByType'] = array_values($stacked_data);

// Types Count (Occupancy Types)
$query = "SELECT issuance.additional 
          FROM issuance
          INNER JOIN applications ON applications.id = issuance.application_id
          WHERE YEAR(issuance.created_at) = ?" .
         ($month ? " AND MONTH(issuance.created_at) = ?" : "") . 
         ($scope && $scope !== "all" ? " AND applications.application_type = ?" : "");


$stmt = $conn->prepare($query);
if ($month && $scope && $scope !== "all") {
    $stmt->bind_param("iis", $year, $month, $scope);  // Bind year, month, and scope (application_type)
} elseif ($month) {
    $stmt->bind_param("ii", $year, $month);  // Bind only year and month
} elseif ($scope && $scope !== "all") {
    $stmt->bind_param("is", $year, $scope);  // Bind year and scope (application_type)
} else {
    $stmt->bind_param("i", $year);  // Bind only year
}

$stmt->execute();
$result = $stmt->get_result();

$types_count = [
    'assembly' => 0, 'educational' => 0, 'daycare' => 0, 'healthcare' => 0,
    'residential' => 0, 'detention' => 0, 'mercantile' => 0, 'business' => 0,
    'industrial' => 0, 'storage' => 0, 'special' => 0, 'hotel' => 0,
    'dormitories' => 0, 'apartment' => 0, 'lodging' => 0, 'single' => 0
];

while ($row = $result->fetch_assoc()) {
    $additional = json_decode($row['additional'], true);
    if ($additional && isset($additional['typeOccupancy'])) {
        $typeOccupancy = $additional['typeOccupancy'];
        if (isset($types_count[$typeOccupancy])) {
            $types_count[$typeOccupancy]++;
        }
    }
}

$response['typesCount'] = $types_count;

file_put_contents('debug_log.txt', print_r($response, true), FILE_APPEND);

// Return valid JSON response
header('Content-Type: application/json');
echo json_encode($response);