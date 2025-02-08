<?php
include '../db_connection.php'; 

error_reporting(E_ALL);
ini_set('display_errors', 1);

$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "error" => "Invalid JSON"]);
    exit;
}

$stmt = $conn->prepare("UPDATE equipment SET quantity = ?, last_maintenance_date = ?, next_maintenance_date = ?, notes = ?, status = ? WHERE id = ?");
$stmt->bind_param("issssi", $data['quantity'], $data['lastMaintenance'], $data['nextMaintenance'], $data['notes'], $data['status'], $data['id']);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
