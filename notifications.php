<?php
include './db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$inspector_id = $_SESSION['user_id']; // Inspector ID from session

// Fetch unread messages for the inspector
$query = "SELECT m.id, m.application_id, m.message, m.created_at, a.owner_name 
          FROM messages m
          INNER JOIN applications a ON m.application_id = a.id
          WHERE m.inspector_id = '$inspector_id' AND m.status = 'unread'
          ORDER BY m.created_at DESC";

$result = $conn->query($query);
$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);

$conn->close();
?>
