<?php
include './db_connection.php'; 

if (isset($_GET['name'])) {
    $name = $_GET['name'];
    
    $stmt = $conn->prepare("SELECT * FROM applications WHERE owner_name LIKE ?");
    $likeName = "%" . $name . "%"; 
    $stmt->bind_param("s", $likeName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $applications = [];
    
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }

    echo json_encode($applications);
}

$stmt->close();
$conn->close();
?>
