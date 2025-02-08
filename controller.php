<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Controller {
    private $con;
    private $mail;

    public function __construct() {
        require_once('./db_connection.php');
        require_once('./function.php');

        // Database connection
        $this->con = $conn;

        // Initialize PHPMailer
        $this->initializeMailer();
    }

    private function initializeMailer() {
        require_once('./PHPMailer/src/Exception.php');
        require_once('./PHPMailer/src/PHPMailer.php');
        require_once('./PHPMailer/src/SMTP.php');

        $this->mail = new PHPMailer(true);

        try {
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'tessnarval11@gmail.com';
            $this->mail->Password = 'vlkc srgz zdrw llbd'; // Use an app password if 2FA is enabled
            $this->mail->SMTPSecure = 'tls';
            $this->mail->Port = 587;
            $this->mail->isHTML(true);
            $this->mail->setFrom('tessnarval11@gmail.com', 'BFP');
        } catch (Exception $e) {
            die("Mailer Initialization Error: " . $e->getMessage());
        }
    }

    function signup() {
        extract($_POST);
    
        if(empty($fullname) || empty($username) || empty($phone) || empty($email) || empty($password) || empty($cpassword) || empty($role)) {
            echo "incomplete-field";
            return;
        }
    
        if($password != $cpassword) {
            echo "password-unmatched";
            return;
        }
    
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{12,}$/', $password)) {
            echo "invalid-password";
            return;
        }
    
        if (!preg_match('/^(09\d{9}|\+639\d{9})$/', $phone)) {
            echo "invalid-phone";
            return;
        }
    
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "invalid-email";
            return;
        }
    
        $hash_pass = password_hash($password, PASSWORD_DEFAULT);
    
        $created_at = date('Y-m-d H:i:s');
        $status = 'Pending';
    
        $sql = "INSERT INTO users (full_name, email, role, username, password, phone, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("ssssssss", $fullname, $email, $role, $username, $hash_pass, $phone, $created_at, $status);
    
        if($stmt->execute()) {
            echo 'success';
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    

    public function checkUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            return "username-taken";
        } else {
            return "available";
        }
    }

    public function checkEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            return "email-taken";
        } else {
            return "available";
        }
    }

    public function checkEmailLogin($email) {
        if (empty($email)) {
            return "email-does-not-exist"; 
        }
    
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
    
        if ($stmt->num_rows > 0) {
            return "email-exists";
        } else {
            return "email-does-not-exist";
        }
    
        $stmt->close();
    }    


    function fetch_history(){
        extract($_GET);

        $sql = "SELECT business_trade_name, created_at FROM applications WHERE email_address = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        
        echo json_encode($history);
    }

    function update_application(){
        extract($_POST);

        $checklistItems = isset($checklist) ? json_decode($checklist) : [];
        $checklistJson = json_encode($checklistItems);

        // $sql = "UPDATE applications SET application_type = ?, owner_name = ?, business_trade_name = ?, address = ?, email_address = ?, checklist = ? WHERE id = ?";
        $sql = "UPDATE applications SET application_type = ?, owner_name = ?, business_trade_name = ?, address = ?, email_address = ?, checklist = ?, issuance_status = ? WHERE id = ?";

        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("sssssssi", $applicationType, $ownerName, $facilityName, $address, $email, $checklistJson, $status, $appId);
        // $stmt->bind_param("ssssssi", $applicationType, $ownerName, $facilityName, $address, $email, $checklistJson, $appId);
        
        if($stmt->execute()){
            if ($status == 'Pending') {
                $deleteInspectionsSql = "DELETE FROM inspections WHERE application_id = ?";
                $deleteStmt = $this->con->prepare($deleteInspectionsSql);
                $deleteStmt->bind_param("i", $appId);
                $deleteStmt->execute();
            }
            echo 'success';
        } else {
            echo "Error: " . $sql . "<br>" . $stmt->error;
        }
    }

    public function generate_inspection() {
        extract($_POST);
        
        $maxInspectionsPerDay = 10; 
        $currentDate = new DateTime();
    
        // Retrieve the email address from the application ID
        $stmtEmail = $this->con->prepare("SELECT email_address FROM applications WHERE id = ?");
        $stmtEmail->bind_param("i", $appId);
    
        if ($stmtEmail->execute()) {
            $resultEmail = $stmtEmail->get_result();
            if ($resultEmail->num_rows > 0) {
                $application = $resultEmail->fetch_assoc();
                $email = $application['email_address'];
            } else {
                echo "application-not-found";
                return;
            }
        } else {
            echo "Error fetching email: " . $stmtEmail->error;
            return;
        }
        $stmtEmail->close();
    
        while (true) {
            $scheduleStart = $currentDate->format('Y-m-d');
            $scheduleEnd = clone $currentDate;
            $scheduleEnd->modify('+2 days');
            $schedule = $currentDate->format('M. j, Y') . ' - ' . $scheduleEnd->format('M. j, Y');
    
            // Check the number of inspections for the assigned inspector on the given day
            $stmtCheck = $this->con->prepare("SELECT COUNT(*) AS inspection_count FROM inspections WHERE inspector_id = ? AND DATE(schedule) = ?");
            $stmtCheck->bind_param("is", $assignedInspector, $scheduleStart);
    
            if ($stmtCheck->execute()) {
                $result = $stmtCheck->get_result();
                $row = $result->fetch_assoc();
    
                if ($row['inspection_count'] < $maxInspectionsPerDay) {
                    // Schedule the inspection
                    $stmtInsert = $this->con->prepare(
                        "INSERT INTO inspections (application_id, inspector_id, inspection_order, schedule, created_by, created_at) VALUES (?, ?, ?, ?, 1, NOW())"
                    );
                    $stmtInsert->bind_param("iiss", $appId, $assignedInspector, $orderNumber, $schedule);
    
                    if ($stmtInsert->execute()) {
                        $stmtInsert->close();
    
                        // Update the application's issuance status
                        $stmtUpdate = $this->con->prepare("UPDATE applications SET issuance_status = 'Completed' WHERE id = ?");
                        $stmtUpdate->bind_param("i", $appId);
    
                        if ($stmtUpdate->execute()) {
                            $stmtUpdate->close();
    
                        // Generate the QR code URL
                        // $currentUrl = "https://yourdomain.com/bfpMingla/scanned_inspection.php?appid=" . $appId;
                        $currentUrl = "http://localhost/bfpMingla/scanned_inspection.php?appid=" . $appId;
                        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($currentUrl) . "&size=150x150";

                        // Prepare the email message with QR code
                        $message = "
                        <html>
                            <head>
                                <style>
                                    body {
                                        font-family: 'Poppins', sans-serif;
                                        margin: 0;
                                        padding: 0;
                                        background-color: #f4f5f7;
                                        color: #333;
                                    }
                                    .email-container {
                                        max-width: 600px;
                                        margin: 20px auto;
                                        background: #ffffff;
                                        border-radius: 8px;
                                        overflow: hidden;
                                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                                    }
                                    .email-header {
                                        background: linear-gradient(90deg, #d32f2f, #e53935);
                                        color: #ffffff;
                                        padding: 20px;
                                        text-align: center;
                                    }
                                    .email-header img {
                                        width: 60px;
                                        margin-bottom: 10px;
                                    }
                                    .email-header h1 {
                                        font-size: 22px;
                                        margin: 0;
                                        font-weight: 600;
                                    }
                                    .email-body {
                                        padding: 20px 30px;
                                    }
                                    .email-body h2 {
                                        color: #d32f2f;
                                        font-size: 18px;
                                        margin-bottom: 10px;
                                    }
                                    .email-body p {
                                        font-size: 15px;
                                        line-height: 1.6;
                                        margin: 10px 0;
                                        color: #555;
                                    }
                                    .email-body .schedule {
                                        font-size: 16px;
                                        font-weight: bold;
                                        color: #d32f2f;
                                        background-color: #f4f5f7;
                                        padding: 10px;
                                        border-radius: 6px;
                                        margin: 15px 0;
                                    }
                                    .email-body .qr-code {
                                        text-align: center;
                                        margin: 20px 0;
                                    }
                                    .email-footer {
                                        background: #f4f5f7;
                                        text-align: center;
                                        padding: 15px 20px;
                                        font-size: 13px;
                                        color: #888;
                                    }
                                    .email-footer a {
                                        color: #d32f2f;
                                        text-decoration: none;
                                        font-weight: 500;
                                    }
                                    .email-footer a:hover {
                                        text-decoration: underline;
                                    }
                                </style>
                            </head>
                            <body>
                                <div class='email-container'>
                                    <!-- Header -->
                                    <div class='email-header'>
                                        <img src='./img/bfp3.png' alt='BFP Logo'>
                                        <h1>Bureau of Fire Protection</h1>
                                    </div>

                                    <!-- Body -->
                                    <div class='email-body'>
                                        <h2>Inspection Phase: Next Step in Your Application</h2>
                                        <p>Dear Client,</p>

                                        <p>We are pleased to inform you that all required documents for your application have been successfully submitted. Your application is now eligible to proceed to the next phase: <strong>Inspection</strong>.</p>

                                        <div class='schedule'>
                                            <strong>Inspection Schedule: $schedule</strong>
                                        </div>

                                        <p>Please ensure that you are available on the scheduled dates to facilitate a smooth inspection process. If you have any questions or need further assistance, please do not hesitate to contact us.</p>

                                        <p>We appreciate your cooperation and look forward to assisting you in the next steps of the process.</p>

                                        <div class='qr-code'>
                                            <p><strong>Scan the QR Code for inspection details:</strong></p>
                                            <img src='$qrCodeUrl' alt='QR Code'>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class='email-footer'>
                                        <p>For inquiries or support, feel free to <a href='https://www.facebook.com/minglanillafire?mibextid=ZbWKwL'>Contact Us</a>.</p>
                                        <p>&copy; <?php echo date('Y'); ?> Bureau of Fire Protection</p>
                                    </div>
                                </div>
                            </body>
                        </html>
                        ";
           
    
                            try {
                                $this->mail->addAddress($email);
                                $this->mail->Subject = 'Scheduled Inspection';
                                $this->mail->Body = $message;
    
                                if (!$this->mail->send()) {
                                    echo 'Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo;
                                } else {
                                    echo 'success';
                                }
                            } catch (Exception $e) {
                                echo "Error sending email: " . $e->getMessage();
                            }
                        } else {
                            echo "Error updating application: " . $stmtUpdate->error;
                        }
                    } else {
                        echo "Error inserting inspection: " . $stmtInsert->error;
                    }
                    break;
                } else {
                    // Move to the next day if max inspections are reached
                    $currentDate->modify('+1 day');
                }
            } else {
                echo "Error checking inspections: " . $stmtCheck->error;
                return;
            }
        }
    }
    
    function delete_application(){
        extract($_POST);

        if (!empty($appIds) && is_array($appIds)) {
            $placeholders = implode(',', array_fill(0, count($appIds), '?'));
            
            $stmt = $this->con->prepare("DELETE FROM applications WHERE id IN ($placeholders)");
            
            $types = str_repeat('i', count($appIds));
            $stmt->bind_param($types, ...$appIds);
            
            if ($stmt->execute()) {
                echo 'success'; 
            } else {
                echo "Error: " . $sql . "<br>" . $stmt->error;
            }
    
            $stmt->close();
        } else {
            echo 'error'; 
        }
    }

    public function update_inspection() {
        extract($_POST);
        $isScheduleChanged = false;
    
        // Retrieve the email address from the application
        $stmt = $this->con->prepare("SELECT email_address FROM applications WHERE id = ?");
        $stmt->bind_param("i", $appId);
        // $stmt = $this->con->prepare("UPDATE inspections SET schedule = ? WHERE application_id = ?");
        // $stmt->bind_param("si", $schedule, $appId);
    
        if ($stmt->execute()) {
            $stmt->store_result();
    
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($email);
                $stmt->fetch();
            } else {
                echo "application-not-found";
                return;
            }
        } else {
            echo "Error fetching email: " . $stmt->error;
            return;
        }
        $stmt->close();
    
        // Check if the schedule has changed
        $stmt = $this->con->prepare("SELECT schedule FROM inspections WHERE application_id = ?");
        $stmt->bind_param("i", $appId);
    
        if ($stmt->execute()) {
            $stmt->store_result();
    
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($dbschedule);
                $stmt->fetch();
    
                if ($schedule !== $dbschedule) {
                    $isScheduleChanged = true;
                }
            }
        } else {
            echo "Error fetching schedule: " . $stmt->error;
            return;
        }
        $stmt->close();
    
        // Update the inspection's schedule
        // $stmt = $this->con->prepare("UPDATE inspections SET schedule = ? WHERE application_id = ?");
        // $stmt->bind_param("si", $schedule, $appId);
        $stmt = $this->con->prepare("UPDATE inspections SET schedule = ? WHERE application_id = ?");
        $stmt->bind_param("si", $schedule, $appId);
    
        if (!$stmt->execute()) {
            echo "Error updating inspection: " . $stmt->error;
            return;
        }
        $stmt->close();
    
        // Prepare the email message
        if ($isScheduleChanged) {
            $message = "<html>
            <head>
                <style>
                    body {
                        font-family: 'Poppins', sans-serif;
                        margin: 0;
                        padding: 0;
                        background-color: #f4f5f7;
                        color: #333;
                    }
                    .email-container {
                        max-width: 600px;
                        margin: 20px auto;
                        background: #ffffff;
                        border-radius: 8px;
                        overflow: hidden;
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                    }
                    .email-header {
                        background: linear-gradient(90deg, #d32f2f, #e53935);
                        color: #ffffff;
                        padding: 20px;
                        text-align: center;
                    }
                    .email-header img {
                        width: 60px;
                        margin-bottom: 10px;
                    }
                    .email-header h1 {
                        font-size: 22px;
                        margin: 0;
                        font-weight: 600;
                    }
                    .email-body {
                        padding: 20px 30px;
                    }
                    .email-body h2 {
                        color: #d32f2f;
                        font-size: 18px;
                        margin-bottom: 10px;
                    }
                    .email-body p {
                        font-size: 15px;
                        line-height: 1.6;
                        margin: 10px 0;
                        color: #555;
                    }
                    .email-body .schedule {
                        font-size: 16px;
                        font-weight: bold;
                        color: #d32f2f;
                        background-color: #f4f5f7;
                        padding: 10px;
                        border-radius: 6px;
                        margin: 15px 0;
                    }
                    .email-footer {
                        background: #f4f5f7;
                        text-align: center;
                        padding: 15px 20px;
                        font-size: 13px;
                        color: #888;
                    }
                    .email-footer a {
                        color: #d32f2f;
                        text-decoration: none;
                        font-weight: 500;
                    }
                    .email-footer a:hover {
                        text-decoration: underline;
                    }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <!-- Header -->
                    <div class='email-header'>
                        <img src='./img/bfp3.png' alt='BFP Logo'>
                        <h1>Bureau of Fire Protection</h1>
                    </div>

                    <!-- Body -->
                    <div class='email-body'>
                        <h2>Inspection Phase: Next Step in Your Application</h2>
                        <p>Dear Client,</p>

                        <p>We regret to inform you that, due to unforeseen circumstances, your application has been rescheduled. Please be advised that your inspection will now take place on the following new dates:</p>

                        <div class='schedule'>
                            <strong>New Inspection Schedule: $schedule</strong>
                        </div>

                        <p>We kindly ask for your understanding regarding this change and appreciate your cooperation. Should you have any questions or need further assistance, please do not hesitate to contact us.</p>

                        <p>Thank you for your attention to this matter. We look forward to assisting you in the next steps of the process.</p>
                    </div>

                    <!-- Footer -->
                    <div class='email-footer'>
                        <p>For inquiries or support, feel free to <a href='https://www.facebook.com/minglanillafire?mibextid=ZbWKwL'>Contact Us</a>.</p>
                        <p>&copy; <?php echo date('Y'); ?> Bureau of Fire Protection</p>
                    </div>
                </div>
            </body>
        </html>
    ";
        } else {
            $message = "<html>
            <head>
                <style>
                    body {
                        font-family: 'Poppins', sans-serif;
                        margin: 0;
                        padding: 0;
                        background-color: #f4f5f7;
                        color: #333;
                    }
                    .email-container {
                        max-width: 600px;
                        margin: 20px auto;
                        background: #ffffff;
                        border-radius: 8px;
                        overflow: hidden;
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                    }
                    .email-header {
                        background: linear-gradient(90deg, #d32f2f, #e53935);
                        color: #ffffff;
                        padding: 20px;
                        text-align: center;
                    }
                    .email-header img {
                        width: 60px;
                        margin-bottom: 10px;
                    }
                    .email-header h1 {
                        font-size: 22px;
                        margin: 0;
                        font-weight: 600;
                    }
                    .email-body {
                        padding: 20px 30px;
                    }
                    .email-body h2 {
                        color: #d32f2f;
                        font-size: 18px;
                        margin-bottom: 10px;
                    }
                    .email-body p {
                        font-size: 15px;
                        line-height: 1.6;
                        margin: 10px 0;
                        color: #555;
                    }
                    .email-body .schedule {
                        font-size: 16px;
                        font-weight: bold;
                        color: #d32f2f;
                        background-color: #f4f5f7;
                        padding: 10px;
                        border-radius: 6px;
                        margin: 15px 0;
                    }
                    .email-footer {
                        background: #f4f5f7;
                        text-align: center;
                        padding: 15px 20px;
                        font-size: 13px;
                        color: #888;
                    }
                    .email-footer a {
                        color: #d32f2f;
                        text-decoration: none;
                        font-weight: 500;
                    }
                    .email-footer a:hover {
                        text-decoration: underline;
                    }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <!-- Header -->
                    <div class='email-header'>
                        <img src='./img/bfp3.png' alt='BFP Logo'>
                        <h1>Bureau of Fire Protection</h1>
                    </div>

                    <!-- Body -->
                    <div class='email-body'>
                        <h2>Inspection Phase: Next Step in Your Application</h2>
                        <p>Dear Client,</p>

                        <p>We regret to inform you that, due to unforeseen circumstances, your application has been rescheduled. Please be advised that your inspection will now take place on the following new dates:</p>

                        <div class='schedule'>
                            <strong>New Inspection Schedule: $schedule</strong>
                        </div>

                        <p>We kindly ask for your understanding regarding this change and appreciate your cooperation. Should you have any questions or need further assistance, please do not hesitate to contact us.</p>

                        <p>Thank you for your attention to this matter. We look forward to assisting you in the next steps of the process.</p>
                    </div>

                    <!-- Footer -->
                    <div class='email-footer'>
                        <p>For inquiries or support, feel free to <a href='https://www.facebook.com/minglanillafire?mibextid=ZbWKwL'>Contact Us</a>.</p>
                        <p>&copy; <?php echo date('Y'); ?> Bureau of Fire Protection</p>
                    </div>
                </div>
            </body>
        </html>
    ";
        }
    
        // Send email notification
        try {
            $this->mail->addAddress($email);
            $this->mail->Subject = 'Inspection Update';
            $this->mail->Body = $message;
    
            if (!$this->mail->send()) {
                echo 'Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo;
            } else {
                echo 'success';
            }
        } catch (Exception $e) {
            echo "Error sending email: " . $e->getMessage();
        }
    }
    

    function update_multi_inspections(){
        extract($_POST);

         // Ensure remarks is sent when status is 'Waiting for Compliance' (status 2)
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : null;

        foreach ($appIds as $appId) {

            $sql = "SELECT email_address FROM applications WHERE id = ?";

            $stmt = $this->con->prepare($sql);
            $stmt->bind_param("i", $appId);
            
            if($stmt->execute()){
                $stmt->store_result();

                if ($stmt->num_rows >  0) {
                    $stmt->bind_result($email);
                    $stmt->fetch();

                    $stmt->close();

                    // $sql = "UPDATE inspections SET status = ? WHERE application_id = ?";
                    // Update the status and inspection date if status is 'Inspected'
                    if ($status == 1) {
                        $sql = "UPDATE inspections SET status = ?, inspection_date = CURDATE() WHERE application_id = ?";
                    } else {
                        $sql = "UPDATE inspections SET status = ? WHERE application_id = ?";
                    }

                    $stmt = $this->con->prepare($sql);
                    $stmt->bind_param("ii", $status, $appId);

                    $inspect_status = getInspection($status);

                    if($stmt->execute()){
                         // Add remarks if status is 'Waiting for Compliance' (status 2)
                        if ($status == 2 && $remarks) {
                            // Insert remarks into the database (adjust as per your table structure)
                            $sql = "UPDATE inspections SET remarks = ? WHERE application_id = ?";
                            $stmt = $this->con->prepare($sql);
                            $stmt->bind_param("si", $remarks, $appId);
                            if (!$stmt->execute()) {
                                echo "Error: " . $sql . "<br>" . $stmt->error;
                            }
                        }

                        if($status == 3){
                            $stmt->close();

                            $sql = "SELECT * FROM issuance WHERE application_id = ?";

                            $stmt = $this->con->prepare($sql);
                            $stmt->bind_param("i", $appId);

                            if($stmt->execute()){
                                $stmt->store_result();

                                if ($stmt->num_rows == 0) {
                                    $stmt->close();

                                    $sql = "INSERT INTO issuance (application_id, additional, status) VALUES (?, '[]', 0)";
            
                                    $stmt = $this->con->prepare($sql);
                                    $stmt->bind_param("i", $appId);
            
                                    if(!$stmt->execute()){
                                        echo "Error: " . $sql . "<br>" . $stmt->error;
                                    }
                                }
                            }else{
                                echo "Error: " . $sql . "<br>" . $stmt->error;
                            }
                        }
                        
                         // Generate the QR code URL
                    // $currentUrl = "https://yourdomain.com/bfpMingla/scanned_inspection.php?appid=" . $appId;
                        // Generate the QR code URL
                    $currentUrl = "http://localhost/bfpMingla/scanned_inspection.php?appid=" . $appId;
                    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($currentUrl) . "&size=150x150";

                    // Prepare the email message with the new design
                    $message = "
                    <html>
                        <head>
                            <style>
                                body {
                                    font-family: 'Poppins', sans-serif;
                                    margin: 0;
                                    padding: 0;
                                    background-color: #f4f5f7;
                                    color: #333;
                                }
                                .email-container {
                                    max-width: 600px;
                                    margin: 20px auto;
                                    background: #ffffff;
                                    border-radius: 8px;
                                    overflow: hidden;
                                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                                }
                                .email-header {
                                    background: linear-gradient(90deg, #d32f2f, #e53935);
                                    color: #ffffff;
                                    padding: 20px;
                                    text-align: center;
                                }
                                .email-header img {
                                    width: 60px;
                                    margin-bottom: 10px;
                                }
                                .email-header h1 {
                                    font-size: 22px;
                                    margin: 0;
                                    font-weight: 600;
                                }
                                .email-body {
                                    padding: 20px 30px;
                                }
                                .email-body h2 {
                                    color: #d32f2f;
                                    font-size: 18px;
                                    margin-bottom: 10px;
                                }
                                .email-body p {
                                    font-size: 15px;
                                    line-height: 1.6;
                                    margin: 10px 0;
                                    color: #555;
                                }
                                .email-body .schedule {
                                    font-size: 16px;
                                    font-weight: bold;
                                    color: #d32f2f;
                                    background-color: #f4f5f7;
                                    padding: 10px;
                                    border-radius: 6px;
                                    margin: 15px 0;
                                }
                                .email-footer {
                                    background: #f4f5f7;
                                    text-align: center;
                                    padding: 15px 20px;
                                    font-size: 13px;
                                    color: #888;
                                }
                                .email-footer a {
                                    color: #d32f2f;
                                    text-decoration: none;
                                    font-weight: 500;
                                }
                                .email-footer a:hover {
                                    text-decoration: underline;
                                }
                            </style>
                        </head>
                        <body>
                            <div class='email-container'>
                                <!-- Header -->
                                <div class='email-header'>
                                    <img src='./img/bfp3.png' alt='BFP Logo'>
                                    <h1>Bureau of Fire Protection</h1>
                                </div>

                                <!-- Body -->
                                <div class='email-body'>
                                    <h2>Inspection Status Update</h2>
                                    <p>Dear Client,</p>

                                    <p>The status of your application has been updated:</p>
                                    
                                    <div class='schedule'>
                                        <strong>New Inspection Status: $inspect_status</strong>
                                    </div>

                                    <p>Please find the QR code below for your inspection process:</p>
                                    <p><img src='$qrCodeUrl' alt='QR Code'></p>

                                    <p>We appreciate your cooperation and look forward to assisting you in the next steps.</p>

                                    <p>Best regards,<br>BFP</p>
                                </div>

                                <!-- Footer -->
                                <div class='email-footer'>
                                    <p>For inquiries or support, feel free to <a href='https://www.facebook.com/minglanillafire?mibextid=ZbWKwL'>Contact Us</a>.</p>
                                    <p>&copy; <?php echo date('Y'); ?> Bureau of Fire Protection</p>
                                </div>
                            </div>
                        </body>
                    </html>
                    ";

                    // Set the email headers
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
                    $headers .= "From: tessnarval11@gmail.com" . "\r\n"; 


                        $this->mail->addAddress($email);
                        $this->mail->Subject = 'Inspection Update';
                        $this->mail->Body = $message;

                        if (!$this->mail->send()) {
                            echo 'Message could not be sent.';
                            echo 'Mailer Error: ' . $this->mail->ErrorInfo;
                        } else {
                            'Message has been sent';
                        }
                    }else{
                        echo "Error: " . $sql . "<br>" . $stmt->error;
                    }
                }else{
                    echo "user-not-found";
                }
                
            }else{
                echo "Error: " . $sql . "<br>" . $stmt->error;
            }
        }

        echo 'success';
    }

    public function get_inspection_date() {
        $appId = $_GET['appId'];  // Use $_GET instead of $_POST since you are sending GET request
    
        $sql = "SELECT inspection_date FROM inspections WHERE application_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $appId);
    
        $response = array();
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($inspection_date);
                $stmt->fetch();
                $response['inspection_date'] = $inspection_date;  // Return inspection date
            } else {
                $response['inspection_date'] = '';  // Return an empty string if no result found
            }
        } else {
            $response['error'] = 'Error fetching inspection date.';
        }
    
        echo json_encode($response);  // Send back a JSON response
    }
    
    
    function save_issuance(){
        extract($_POST);

        $sql = "UPDATE issuance SET additional = ?, status = 1 WHERE application_id = ?";

        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("si", $additionals, $appId);

        if($stmt->execute()){
            $stmt->close();

            $sql = "SELECT email_address FROM applications WHERE id = ?";

            $stmt = $this->con->prepare($sql);
            $stmt->bind_param("i", $appId);

            if($stmt->execute()){
                $stmt->store_result();

                if ($stmt->num_rows >  0) {
                    $stmt->bind_result($email);
                    $stmt->fetch();

                    $message = "<html>
                            <head>
                                <style>
                                    body {
                                        font-family: 'Poppins', sans-serif;
                                        margin: 0;
                                        padding: 0;
                                        background-color: #f4f5f7;
                                        color: #333;
                                    }
                                    .email-container {
                                        max-width: 600px;
                                        margin: 20px auto;
                                        background: #ffffff;
                                        border-radius: 8px;
                                        overflow: hidden;
                                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                                    }
                                    .email-header {
                                        background: linear-gradient(90deg, #d32f2f, #e53935);
                                        color: #ffffff;
                                        padding: 20px;
                                        text-align: center;
                                    }
                                    .email-header img {
                                        width: 60px;
                                        margin-bottom: 10px;
                                    }
                                    .email-header h1 {
                                        font-size: 22px;
                                        margin: 0;
                                        font-weight: 600;
                                    }
                                    .email-body {
                                        padding: 20px 30px;
                                    }
                                    .email-body h2 {
                                        color: #d32f2f;
                                        font-size: 18px;
                                        margin-bottom: 10px;
                                    }
                                    .email-body p {
                                        font-size: 15px;
                                        line-height: 1.6;
                                        margin: 10px 0;
                                        color: #555;
                                    }
                                    .email-footer {
                                        background: #f4f5f7;
                                        text-align: center;
                                        padding: 15px 20px;
                                        font-size: 13px;
                                        color: #888;
                                    }
                                    .email-footer a {
                                        color: #d32f2f;
                                        text-decoration: none;
                                        font-weight: 500;
                                    }
                                    .email-footer a:hover {
                                        text-decoration: underline;
                                    }
                                </style>
                            </head>
                            <body>
                                <div class='email-container'>
                                    <!-- Header -->
                                    <div class='email-header'>
                                        <img src='./img/bfp3.png' alt='BFP Logo'>
                                        <h1>Bureau of Fire Protection</h1>
                                    </div>

                                    <!-- Body -->
                                    <div class='email-body'>
                                        <h2>Fire Safety Certificate (FSEC) / Fire Safety Inspection Certificate (FSIC) Ready for Pickup</h2>
                                        <p>Dear Valued Client,</p>

                                        <p>We are pleased to inform you that your certificate</strong> is now <strong>ready for pickup</strong>.</p>

                                        <p>Your application has been successfully processed, and the certificate has been issued. This marks the completion of the application process for your business. You may now proceed to collect your certificate from our office.</p>

                                        <p>We appreciate your cooperation and timely submission of the required documents, which has allowed us to expedite the issuance of your certificate.</p>

                                        <p>Should you have any further questions or need assistance, please do not hesitate to contact us. Thank you once again for your prompt compliance with the requirements.</p>
                                    </div>

                                    <!-- Footer -->
                                    <div class='email-footer'>
                                        <p>For inquiries or support, feel free to <a href='https://www.facebook.com/minglanillafire?mibextid=ZbWKwL'>Contact Us</a>.</p>
                                        <p>&copy; <?php echo date('Y'); ?> Bureau of Fire Protection</p>
                                    </div>
                                </div>
                            </body>
                        </html>
                        ";

                    $this->mail->addAddress($email);
                    $this->mail->Subject = 'FSIC Issuance';
                    $this->mail->Body = $message;

                    if (!$this->mail->send()) {
                        echo 'Message could not be sent.';
                        echo 'Mailer Error: ' . $this->mail->ErrorInfo;
                    } else {
                        'Message has been sent';
                    }

                    echo 'success';
                }else{
                    echo "user-not-found";
                }
            }else{
                echo "Error: " . $sql . "<br>" . $stmt->error;
            }
        }else{
            echo "Error: " . $sql . "<br>" . $stmt->error;
        }
    }
    
    function send_message(){
        extract($_POST);

        foreach ($appIds as $appId) {

            $sql = "SELECT email_address FROM applications WHERE id = ?";

            $stmt = $this->con->prepare($sql);
            $stmt->bind_param("i", $appId);

            if($stmt->execute()){
                $stmt->store_result();
                $stmt->bind_result($email);
                $stmt->fetch();

                $messages = "
                    <html>
                        <body>
                            <p>Dear Client,</p>
                            <p>$message</p>
                            <p>Best regards,<br>
                            BFP</p>
                        </body>
                    </html>
                ";

                $this->mail->addAddress($email);
                $this->mail->Subject = 'BFP Notice';
                $this->mail->Body = $messages;

                if (!$this->mail->send()) {
                    echo 'Message could not be sent.';
                    echo 'Mailer Error: ' . $this->mail->ErrorInfo;
                } else {
                    'Message has been sent';
                }
            }else{
                echo "Error: " . $sql . "<br>" . $stmt->error;
            }
        }
        echo 'success';
    }
    
    function update_multi_equipment(){
        extract($_POST);

        foreach ($equipmentIds as $equipmentId) {
            $sql = "UPDATE equipment SET status = ? WHERE id = ?";

            $stmt = $this->con->prepare($sql);
            $stmt->bind_param("si", $status, $equipmentId);

            if(!$stmt->execute()){
                echo "Error: " . $sql . "<br>" . $stmt->error;
            }
        }

        echo 'success';
    }

    // function delete_equipment(){
    //     extract($_POST);

    //     if (!empty($equipmentIds) && is_array($equipmentIds)) {
    //         $placeholders = implode(',', array_fill(0, count($equipmentIds), '?'));
            
    //         $stmt = $this->con->prepare("DELETE FROM equipment WHERE id IN ($placeholders)");
            
    //         $types = str_repeat('i', count($equipmentIds));
    //         $stmt->bind_param($types, ...$equipmentIds);
            
    //         if ($stmt->execute()) {
    //             echo 'success'; 
    //         } else {
    //             echo "Error: " . $sql . "<br>" . $stmt->error;
    //         }
    
    //         $stmt->close();
    //     } else {
    //         echo 'error'; 
    //     }
    // }

    function delete_equipment() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $equipmentIds = $data['equipmentIds'] ?? [];
    
            if (!empty($equipmentIds) && is_array($equipmentIds)) {
                $placeholders = implode(',', array_fill(0, count($equipmentIds), '?'));
    
                $stmt = $this->con->prepare("DELETE FROM equipment WHERE id IN ($placeholders)");
    
                $types = str_repeat('i', count($equipmentIds));
                $stmt->bind_param($types, ...$equipmentIds);
    
                if ($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $stmt->error]);
                }
    
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid equipment IDs']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    

    function delete_incident(){
        extract($_POST);

        if (!empty($incidentIds) && is_array($incidentIds)) {
            $placeholders = implode(',', array_fill(0, count($incidentIds), '?'));
            
            $stmt = $this->con->prepare("DELETE FROM incident_reports WHERE id IN ($placeholders)");
            
            $types = str_repeat('i', count($incidentIds));
            $stmt->bind_param($types, ...$incidentIds);
            
            if ($stmt->execute()) {
                echo 'success'; 
            } else {
                echo "Error: " . $sql . "<br>" . $stmt->error;
            }
    
            $stmt->close();
        } else {
            echo 'error'; 
        }
    }

    function delete_personnel(){
        extract($_POST);

        if (!empty($personnelIds) && is_array($personnelIds)) {
            $placeholders = implode(',', array_fill(0, count($personnelIds), '?'));
            
            $stmt = $this->con->prepare("DELETE FROM personnel WHERE id IN ($placeholders)");
            
            $types = str_repeat('i', count($personnelIds));
            $stmt->bind_param($types, ...$personnelIds);
            
            if ($stmt->execute()) {
                echo 'success'; 
            } else {
                echo "Error: " . $sql . "<br>" . $stmt->error;
            }
    
            $stmt->close();
        } else {
            echo 'error'; 
        }
    }

    function update_incident(){
        extract($_POST);

        $sql = "UPDATE incident_reports SET incident_date = ?, time = ?, location = ?, owner_occupant = ?, occupancy_type = ?, cause_of_fire = ?, estimated_damages = ?, casualties_injuries = ?, fire_control_time = ?, inspector_in_charge = ?, investigation_report_date = ?, status = ? WHERE id = ?";

        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("ssssssssssssi", $incidentDate, $time, $location, $owner, $occupancyType, $causeOfFire, $estimatedDamages, $casualtiesInjuries, $fireControlTime, $inspector, $reportDate, $status, $incidentId);

        if ($stmt->execute()) {
            echo 'success'; 
        } else {
            echo "Error: " . $sql . "<br>" . $stmt->error;
        }
    }

    function delete_user(){
        extract($_POST);

        if (!empty($userIds) && is_array($userIds)) {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            
            $stmt = $this->con->prepare("DELETE FROM users WHERE id IN ($placeholders)");
            
            $types = str_repeat('i', count($userIds));
            $stmt->bind_param($types, ...$userIds);
            
            if ($stmt->execute()) {
                echo 'success'; 
            } else {
                echo "Error: " . $sql . "<br>" . $stmt->error;
            }
    
            $stmt->close();
        } else {
            echo 'error'; 
        }
    }
}
