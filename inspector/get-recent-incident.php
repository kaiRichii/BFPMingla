<?php
include_once('../db_connection.php');

$query = "SELECT id, incident_date, time, location, owner_occupant, occupancy_type, cause_of_fire, estimated_damages,
          casualties_injuries, fire_control_time, inspector_in_charge, investigation_report_date, status
          FROM incident_reports
          ORDER BY incident_date DESC, time DESC LIMIT 1"; 

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $incident = $result->fetch_assoc();
    $incident['formatted_date'] = date("F j, Y", strtotime($incident['incident_date']));
    
    echo json_encode($incident);
} else {
    echo json_encode([
        "status" => "N/A",
        "location" => "N/A",
        "cause_of_fire" => "N/A",
        "formatted_date" => "N/A"
    ]);
}

// Close the connection
$conn->close();
?>
