<?php
require_once('db_connection.php');

function GetApplication($column, $value){
    global $conn;

    $query = "SELECT * FROM applications";

    if ($column && $value) {
        $query .= " WHERE $column = '$value'";
    }

    $result = $conn->query($query);
    return $result;
}

// function getInspectionStatus($column, $value){
//     global $conn;

//     $query = "SELECT *, inspections.status AS inspection_status FROM applications 
//               INNER JOIN inspections ON applications.id = inspections.application_id 
//               INNER JOIN users ON inspections.inspector_id = users.id";

//     if ($column && $value) {
//         $query .= " WHERE $column = '$value'";
//     }

//     $result = $conn->query($query);
//     return $result;
// }
function getInspectionStatus($column, $value) {
    global $conn;

    // Select fields including the inspector's details
    $query = "SELECT applications.*, 
                     inspections.status AS inspection_status, 
                     inspections.remarks, 
                     inspections.schedule, 
                     users.id AS inspector_id, 
                     users.full_name AS inspector_name 
              FROM applications 
              INNER JOIN inspections ON applications.id = inspections.application_id 
              INNER JOIN users ON inspections.inspector_id = users.id";

    // Filter by column and value (e.g., application id)
    if ($column && $value) {
        $query .= " WHERE $column = '$value'";
    }

    $result = $conn->query($query);
    return $result;
}

function getInspection($inspection) {
    $inspectionStatuses = [
        1 => 'Inspected',
        2 => 'Waiting for Compliance',
        3 => 'Complied',
        4 => 'Notice to Comply Issued',
        5 => 'Notice to Correct Violation',
        6 => 'Issued Abandonment Order',
        7 => 'Issued Closure Order',
    ];

    return $inspectionStatuses[$inspection] ?? 'Pending Inspection';
}

?>