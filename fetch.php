<?php
include 'db_connection.php'; 

$sql = "SELECT * FROM equipment"; 
$result = $conn->query($sql);

$equipmentData = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $equipmentData[] = $row;
    }
}

$conn->close();
echo json_encode($equipmentData);
?>
