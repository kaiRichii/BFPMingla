<?php
session_start();
include '../db_connection.php';

header('Content-Type: application/json'); 

$data = json_decode(file_get_contents('php://input'), true);

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$phone = $data['phone'] ?? '';
$email = $data['email'] ?? '';
$username = $data['username'] ?? '';
$status = $data['status'] ?? ''; 

if (empty($phone) || empty($email) || empty($username)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

$query = "UPDATE users SET phone = ?, email = ?, username = ?, status = ? WHERE id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param('ssssi', $phone, $email, $username, $status, $user_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
}

$stmt->close();
$conn->close();
?>
