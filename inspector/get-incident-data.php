<?php
include_once('../db_connection.php');

$query = "SELECT status, COUNT(*) as count FROM incident_reports GROUP BY status";
$result = $conn->query($query);

$data = [
    'For Investigation' => 0,
    'Under Investigation' => 0,
    'Completed' => 0,
    'Closed' => 0
];

$statusMapping = [
    'For Investigation' => 'For Investigation',
    'Under Investigation' => 'Under Investigation',
    'Investigation Completed' => 'Completed',
    'Closed' => 'Closed'
];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mappedStatus = $statusMapping[$row['status']] ?? null;
        if ($mappedStatus) {
            $data[$mappedStatus] += $row['count'];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($data);
?>
