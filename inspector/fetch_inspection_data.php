<?php
session_start();
include '../db_connection.php';

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index/index.php");
    exit();
}

// Query to fetch inspections along with related application data
$query = "
    SELECT 
        inspections.id AS inspection_id,
        inspections.schedule AS inspection_schedule,
        inspections.status AS inspection_status,
        inspections.remarks AS inspection_remarks,
        inspections.inspection_order,
        inspections.inspection_date,   -- Added inspection_date
        applications.owner_name,
        applications.business_trade_name,
        applications.address,
        applications.contact_number,
        applications.email_address,
        applications.id AS application_id
    FROM inspections
    INNER JOIN applications ON inspections.application_id = applications.id
    WHERE inspections.inspector_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];

while ($row = $result->fetch_assoc()) {
    // Skip unwanted statuses (if any)
    if ($row['inspection_status'] == 2 || $row['inspection_status'] == 3 || $row['inspection_status'] == 4 || 
        $row['inspection_status'] == 5 || $row['inspection_status'] == 6 || $row['inspection_status'] == 7) {
        continue;
    }

    // For Pending Inspection status (0), use the inspection_schedule as the range
    if ($row['inspection_status'] == 0) {
        $schedule = explode(' - ', $row['inspection_schedule']);
        $start_date = date('Y-m-d', strtotime($schedule[0]));  
        $end_date = date('Y-m-d', strtotime($schedule[1])); 

        $events[] = [
            'id' => $row['inspection_id'],
            'title' => $row['owner_name'],
            'start' => $start_date, 
            'end' => date('Y-m-d', strtotime($end_date . ' +1 day')),
            'status' => $row['inspection_status'],
            'inspection_date' => null,  // No inspection date for Pending Inspection
            'inspection_schedule' => $row['inspection_schedule'],
            'business_trade_name' => $row['business_trade_name'],
            'address' => $row['address'],
            'contact_number' => $row['contact_number'],
            'email_address' => $row['email_address'],
        ];
    } 
    // For Inspected status (1), use the inspection_date (single day event)
    elseif ($row['inspection_status'] == 1 && !empty($row['inspection_date'])) {
        $inspection_date = date('Y-m-d', strtotime($row['inspection_date']));  // Use only the inspection_date

        $events[] = [
            'id' => $row['inspection_id'],
            'title' => $row['owner_name'],
            'start' => $inspection_date,  // Use the single inspection date
            'end' => $inspection_date,  // Same start and end date for a single-day event
            'status' => $row['inspection_status'],
            'inspection_date' => $inspection_date,
            'inspection_schedule' => $row['inspection_schedule'],  // Keep the schedule for the Swal popup
            'business_trade_name' => $row['business_trade_name'],
            'address' => $row['address'],
            'contact_number' => $row['contact_number'],
            'email_address' => $row['email_address'],
        ];
    }
}

echo json_encode($events);  // Output events as JSON
$conn->close();
?>
