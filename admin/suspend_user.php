<?php
session_start();
require_once '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
    $startDate = isset($_POST['start_date']) ? $_POST['start_date'] : null;
    $endDate = isset($_POST['end_date']) ? $_POST['end_date'] : null;

    if (!$userId || !$startDate || !$endDate) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
        http_response_code(400);
        exit;
    }

    try {
        $query = "UPDATE users SET status = 'Suspended', start_suspend = ?, end_suspend = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to prepare the statement.']);
            http_response_code(500);
            exit;
        }
        $stmt->bind_param('ssi', $startDate, $endDate, $userId);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'User suspended successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to suspend the user.']);
            http_response_code(500);
        }
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        http_response_code(500);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    http_response_code(405);
}
$conn->close();
?>
