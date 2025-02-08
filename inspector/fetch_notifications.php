<?php
include '../db_connection.php';

// Fetch all notifications
$query = "
    SELECT 
        e.equipment_name, 
        e.next_maintenance_date, 
        e.last_maintenance_date, 
        e.status,
        IF(nr.equipment_name IS NOT NULL, 'read', 'unread') AS read_status
    FROM 
        equipment e
    LEFT JOIN 
        notification_reads nr 
    ON 
        e.equipment_name = nr.equipment_name AND nr.user_id = 1 -- Include user filter
    WHERE 
        e.status = 'needs replacement' 
        OR e.next_maintenance_date <= CURDATE()
    ORDER BY 
        e.next_maintenance_date ASC
";

$result = $conn->query($query);

$alerts = [];

while ($row = $result->fetch_assoc()) {
    $nextMaintenance = $row['next_maintenance_date'];
    $status = $row['status'];

    // Calculate days difference
    $today = date('Y-m-d');
    $daysDifference = (strtotime($nextMaintenance) - strtotime($today)) / (60 * 60 * 24);

    if ($daysDifference < 0) {
        $status = 'overdue';
    } elseif ($daysDifference === 0) {
        $status = 'today';
    } else {
        $status = 'upcoming';
    }

    $alerts[] = [
        'equipment_name' => $row['equipment_name'],
        'next_maintenance_date' => $nextMaintenance,
        'last_maintenance_date' => $row['last_maintenance_date'],
        'status' => $status,
        'read_status' => $row['read_status'] // 'unread' or 'read'
    ];
}

header('Content-Type: application/json');
echo json_encode($alerts);
?>
