<?php
include './db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "You need to be logged in to mark messages as read.";
    exit;
}

$message_id = $_POST['message_id'];

if (empty($message_id)) {
    echo "Message ID cannot be empty.";
    exit;
}

// Mark the message as read
$query = "UPDATE messages SET status = 'read' WHERE id = '$message_id' AND inspector_id = '" . $_SESSION['user_id'] . "'";

if ($conn->query($query) === TRUE) {
    echo "Message marked as read!";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
