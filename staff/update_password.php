<?php
session_start();
include '../db_connection.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$current_password = $data['currentPassword'] ?? '';
$new_password = $data['newPassword'] ?? '';

if (empty($current_password) || empty($new_password)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

$query = "SELECT password FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$user = $result->fetch_assoc();
$stored_password_hash = $user['password'];

if (!password_verify($current_password, $stored_password_hash)) {
    echo json_encode(['status' => 'error', 'message' => 'Incorrect current password']);
    exit;
}

$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

$update_query = "UPDATE users SET password = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param('si', $new_password_hash, $user_id);

if ($update_stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
}

$stmt->close();
$update_stmt->close();
$conn->close();
?>
