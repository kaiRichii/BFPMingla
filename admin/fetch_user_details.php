<?php
include '../db_connection.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT id, account_number, item_number, rank, first_name, middle_name, last_name, suffix, contact_number, date_of_birth, marital_status, gender, complete_address, religion, tin, pagibig, gsis, philhealth, tertiary_courses, post_graduate_courses, highest_eligibility, highest_training, specialized_training, date_entered_other_gov_service, date_entered_fire_service, mode_of_entry, date_of_last_promotion, appointment_status, unit_code, unit_assignment, designation, image_path FROM personnel WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>
