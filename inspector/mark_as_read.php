<?php
include '../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['equipment_names']) && is_array($data['equipment_names'])) {
    $equipmentNames = $data['equipment_names'];
    $userId = 1;

    // Prepare placeholders for query
    $placeholders = implode(',', array_fill(0, count($equipmentNames), '?'));

    // Prepare the query to mark multiple notifications as read
    $query = "INSERT IGNORE INTO notification_reads (user_id, equipment_name) VALUES " . 
        implode(',', array_map(function () { return '(?, ?)'; }, $equipmentNames));

    $stmt = $conn->prepare($query);

    // Bind user ID and equipment names dynamically
    $types = str_repeat('is', count($equipmentNames));
    $params = [];
    foreach ($equipmentNames as $equipmentName) {
        $params[] = $userId;
        $params[] = $equipmentName;
    }
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}
?>
