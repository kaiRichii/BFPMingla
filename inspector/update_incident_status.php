<?php
include_once('../db_connection.php');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['status'])) {
    $incidentId = $data['id'];
    $status = $data['status'];

    $stmt = $conn->prepare("UPDATE incident_reports SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $incidentId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input data.']);
}

$conn->close();
?>
