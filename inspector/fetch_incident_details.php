<?php
// Include database connection
include '../db_connection.php';

if (isset($_POST['id'])) {
    $incidentId = intval($_POST['id']);  // Securely cast to int
    
    // Prepare and execute the query to fetch incident details
    $stmt = $conn->prepare("SELECT * FROM incident_reports WHERE id = ?");
    $stmt->bind_param("i", $incidentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $incident = $result->fetch_assoc();

        // Return the incident details as JSON
        echo json_encode($incident);
    } else {
        // No incident found
        echo json_encode([]);
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>
