<?php
include './db_connection.php'; 
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $owner_name = $_POST['owner_name'];
    $application_type = $_POST['application_type'];
    $business_trade_name = $_POST['business_trade_name'];
    $address = $_POST['address'];
    $contact_number = $_POST['contact_number'];
    $email_address = $_POST['email_address'];
    $type = $_POST['type'];
    $issuance_status = 'Pending'; 
    $created_at = date('Y-m-d H:i:s'); 

    $checklist_items = [];

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'checklist_') === 0 && isset($_POST[$key])) {
            $checklist_items[] = str_replace('checklist_', '', $key);
        }
    }

    $checklist_json = json_encode($checklist_items);

    $stmt = $conn->prepare("INSERT INTO applications (application_type, owner_name, business_trade_name, address, contact_number, email_address, checklist, type, issuance_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Preparation failed: " . $conn->error);
    }

    $stmt->bind_param("sssssssiss", $application_type, $owner_name, $business_trade_name, $address, $contact_number, $email_address, $checklist_json, $type, $issuance_status, $created_at);

    if (!$stmt->execute()) {
        die("Execution failed: " . $stmt->error);
    }
    
    if (($type == 0 && count($checklist_items) == 8) ||
        ($type == 1 && count($checklist_items) == 10) ||
        ($type == 2 && count($checklist_items) == 12)) {
        $_SESSION['complete'] = true;
        $_SESSION['appid'] = $conn->insert_id;
    } else {
        $_SESSION['complete'] = false;
    }


    $stmt->close();
    $conn->close();

    header("Location: ./staff/application-list.php?success=1");
    exit();
}
?>
