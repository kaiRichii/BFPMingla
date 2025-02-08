<?php
session_start();
header('Content-Type: application/json');
include '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id']; 

$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$user = $result->fetch_assoc();
echo json_encode(['status' => 'success', 'data' => $user]);
?>