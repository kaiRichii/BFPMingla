<?php
include './db_connection.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You need to be logged in to send a message.";
    exit;
}

$appid = $_POST['appid']; // Application ID (client ID)
$message = $_POST['message']; // The message from the client

// Ensure that the message and appid are not empty
if (empty($appid) || empty($message)) {
    echo "Application ID or message cannot be empty.";
    exit;
}

$inspector_id = $_SESSION['user_id']; // Inspector ID from the session (logged-in user)

// Insert the message into the database
$query = "INSERT INTO messages (application_id, inspector_id, message, status) 
          VALUES ('$appid', '$inspector_id', '$message', 'unread')";

if ($conn->query($query) === TRUE) {
    echo "Message sent successfully!";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
