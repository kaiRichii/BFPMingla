<?php
include '../db_connection.php'; 

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); 
    $query = "SELECT id, username, email, full_name, role, status FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'User not found']);
    }

    $stmt->close();
}
$conn->close();
?>
