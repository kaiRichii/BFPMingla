<?php
include './db_connection.php';
session_start();

if (isset($_GET['inspectorId'])) {
    $inspectorId = (int) $_GET['inspectorId'];  // Get the inspector ID from the session or request

    // Fetch unread messages for the inspector
    $sql = "SELECT messages.message, messages.timestamp, applications.owner_name, applications.id AS appid 
            FROM messages 
            INNER JOIN applications ON messages.application_id = applications.id 
            WHERE messages.inspector_id = '$inspectorId' AND messages.is_read = 0 
            ORDER BY messages.timestamp DESC";

    $result = $conn->query($sql);
    $messages = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }

        // Mark messages as read
        $updateSql = "UPDATE messages SET is_read = 1 WHERE inspector_id = '$inspectorId' AND is_read = 0";
        $conn->query($updateSql);
    }

    // Return the messages as JSON
    echo json_encode($messages);
} else {
    echo json_encode([]);  // Return an empty array if no inspector ID is passed
}
?>
