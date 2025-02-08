<?php
session_start();
require_once '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = isset($_POST['approve_user_id']) ? $_POST['approve_user_id'] : null;

    if ($user_id) {
        $sql = "UPDATE users SET status = 'Approved' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'User account has been successfully approved.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to approve the user. Please try again.'
            ]);
        }
        $stmt->close();
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid user ID.'
        ]);
    }
}
?>
