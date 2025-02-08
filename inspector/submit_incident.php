<?php

include '../db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $incident_date = $_POST['incident_date'];
    $time = $_POST['time'];
    $location = $_POST['location'];
    $owner_occupant = $_POST['owner_occupant'];
    $occupancy_type = $_POST['occupancy_type'];
    $cause_of_fire = $_POST['cause_of_fire'];
    $estimated_damages = $_POST['estimated_damages'] ?? 0;
    $casualties_injuries = $_POST['casualties_injuries'];
    $fire_control_time = $_POST['fire_control_time'];
    $inspector_in_charge = $_POST['inspector_in_charge'];
    $investigation_report_date = $_POST['investigation_report_date'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO incident_reports (incident_date, time, location, owner_occupant, occupancy_type, cause_of_fire, estimated_damages, casualties_injuries, fire_control_time, inspector_in_charge, investigation_report_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssss", $incident_date, $time, $location, $owner_occupant, $occupancy_type, $cause_of_fire, $estimated_damages, $casualties_injuries, $fire_control_time, $inspector_in_charge, $investigation_report_date, $status);

    if ($stmt->execute()) {
        session_start();
        $_SESSION['flash-msg'] = 'success-incident';
        header("Location: incidents.php");        
        // echo "<script>alert('Incident report submitted successfully.'); window.location.href = 'incidents.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.location.href = 'residential_fire-form.php';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Invalid request method.'); window.location.href = 'residential_fire-form.php';</script>";
}
