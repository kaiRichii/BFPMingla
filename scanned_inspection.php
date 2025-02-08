<?php
require_once('./function.php');

if (isset($_GET['appid'])) {
    $result = GetInspectionStatus('applications.id', $_GET['appid']);
    $application = $result->fetch_assoc();

    if (!$application) {
        header("location: ./index.php");
        exit;
    }

    $application['checklist'] = json_decode($application['checklist'], true);
    // Get the inspection status
    $status = (int)$application['inspection_status'];

    // Get the remarks
    $remarks = isset($application['remarks']) ? $application['remarks'] : 'No remarks available';

    // Get the inspector name
    $inspectorName = isset($application['inspector_name']) ? $application['inspector_name'] : 'N/A';
} else {
    header("location: ./index/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .main-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            width: 100%;
            max-width: 1000px; 
            margin: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            background-color: #ffffff;
            flex-direction: column;
            padding: 30px;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .header-logo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }

        h1 {
            font-size: 1.5rem; /* Slightly smaller title */
            font-weight: 600;
            color: #d32f2f;
            margin: 0;
        }

        .progress-container {
            width: 100%;
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 30px;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            width: 100%;
        }

        .step-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: #ddd;  /* Default inactive color */
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
            transition: background-color 0.3s ease;
            z-index: 2;
        }

        .step-label {
            font-size: 1rem;
            font-weight: 500;
            color: #333;
            margin-top: 10px;
        }

        .progress-bar {
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 5px;
            background-color: #ddd;
            z-index: 1;
        }

        /* Status colors for each step */
        .completed .step-circle {
            background-color: #388e3c; /* Green for completed */
        }

        .current .step-circle {
            background-color: #1976d2; /* Blue for current */
        }

        .next .step-circle {
            background-color: #bdbdbd; /* Gray for next */
        }

        .special .step-circle {
            background-color: #d32f2f; /* Red for special (next inspection) */
        }

        .completed .progress-bar {
            background-color: #388e3c;
        }

        .current .progress-bar {
            background-color: #1976d2;
        }

        /* Information Section */
        .info-container {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 40px;
        }

        .info-item {
            width: calc(50% - 10px);
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .info-item strong {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1976d2; /* Blue accent */
            margin-bottom: 8px;
        }

        .info-item span {
            font-size: 1rem;
            color: #555;
        }
        /* Status Reminder styles */
        .status-reminder {
            font-size: 1rem; 
            font-weight: 600;
            margin-bottom: 20px;
            padding: 15px;
            min-width: 100%;
            background-color: #fff3e0;
            border-left: 5px solid #ff9800;
            color: #ff9800; 
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); 
        }

        .status-reminder p {
            margin: 0;
            line-height: 1.6; 
        }

        .status-reminder ul {
            margin-top: 10px;
            margin-left: 20px;
        }

        .status-reminder li {
            margin-bottom: 8px;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                margin: 10px;
                padding: 20px;
            }

            .progress-step .step-circle {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .step-label {
                font-size: 0.9rem;
            }

            .info-item {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .header-logo {
                width: 45px;
                height: 45px;
            }

            h1 {
                font-size: 1.2rem;
            }

            .info-item {
                width: 100%;
                padding: 15px;
            }

            .step-circle {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }

            .step-label {
                font-size: 0.8rem;
            }
        }
        .message-container {
            display: flex;
            justify-content: center; 
            align-items: center;  
            width: 100%;
            background-color: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            margin-top: 15px;
            margin-bottom: 20px;
            position: relative;
            flex-direction: column;  
        }

        .input-row {
            display: flex;  
            align-items: center; 
            width: 100%;  
        }

        textarea {
            width: 85%;  
            height: 60px;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 1rem; 
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            resize: none;
            outline: none;
            transition: border 0.3s ease;
        }

        textarea:focus {
            border-color: #1976d2;
        }

        .send-button {
            width: 40px; 
            height: 40px;
            background-color: #1976d2;
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 1.2rem; 
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-left: 10px; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .send-button:hover {
            background-color: #1565c0;
        }

        .send-button:focus {
            outline: none;
        }

        .send-button i {
            pointer-events: none;
        }

        .message-label {
            font-size: 0.9rem; 
            font-weight: 500;
            color: #555;
            margin-top: 8px; 
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .message-container {
                flex-direction: column; 
            }

            .input-row {
                width: 100%;  
            }

            .send-button {
                width: 35px;
                height: 35px;
            }

            textarea {
                font-size: 0.95rem;
                height: 50px;
            }
        }

        @media (max-width: 480px) {
            .message-container {
                padding: 10px;
                justify-content: center; 
            }

            .input-row {
                width: 100%;
                justify-content: space-between; 
            }

            .send-button {
                width: 35px;
                height: 35px; 
                font-size: 1rem;
            }

            textarea {
                height: 40px; 
                font-size: 0.9rem;
            }

            .message-label {
                font-size: 0.8rem; 
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header">
            <img src="./img/bfp3.png" alt="BFP Logo" class="header-logo">
            <h1>BFP Minglanilla Fire Station</h1>
        </div>

        <!-- Progress Tracker -->
        <div class="progress-container">
            <?php
            if ($status === 0) {
                $stages = ['Pending Inspection', 'Inspected'];
            } elseif ($status === 1) { 
                $stages = ['Pending Inspection', 'Inspected', 'Waiting for compliance'];
            } elseif ($status === 2) {
                $stages = ['Pending Inspection', 'Inspected', 'Waiting for compliance', 'Complied'];
            } elseif ($status === 3) {
                $stages = ['Pending Inspection', 'Inspected', 'Waiting for compliance', 'Complied'];
            } else {
                $stages = ['Pending Inspection', 'Inspected', 'Waiting for compliance', getInspection($status)];
            }

            foreach ($stages as $index => $stage) {
                // Determine the class based on the current progress status
                $stepClass = '';
                if ($index < $status) {
                    $stepClass = 'completed';
                } elseif ($index == $status) {
                    $stepClass = 'current';
                } elseif ($index == count($stages) - 1 && $status >= count($stages) - 1) {
                    $stepClass = 'special';
                } else {
                    $stepClass = 'next';
                }

                // Create the step with circle and label
                echo "<div class='progress-step $stepClass'>
                        <div class='step-circle'>$index</div>
                        <div class='progress-bar'></div>
                        <span class='step-label'>$stage</span>
                    </div>";
            }
            ?>
        </div>

        <!-- Information Sections -->
        <h3>Inspection Information</h3><hr>
        <div class="info-container">
            <div class="info-item">
                <strong>Inspector Name:</strong>
                <span><?= htmlspecialchars($inspectorName) ?></span>
            </div>
            <div class="info-item">
                <strong>Inspection Schedule:</strong>
                <span><?= htmlspecialchars($application['schedule']) ?></span>
            </div>
        </div>

        <h3>Client Information</h3><hr>
        <div class="info-container">
            <div class="info-item">
                <strong>Application Type:</strong>
                <span><?= htmlspecialchars($application['application_type']) ?></span>
            </div>
            <div class="info-item">
                <strong>Client Name:</strong>
                <span><?= htmlspecialchars($application['owner_name']) ?></span>
            </div>
        </div>

        <!-- Remarks Section -->
        <h3>Remarks</h3><hr>
        <div class="status-reminder">
            <p><?= nl2br(htmlspecialchars($remarks)) ?></p>
        </div>
        
        <!-- Message Input Form -->
        <label for="message" class="message-label">Type your message to the inspector:</label>
        <div class="message-container">
            <div class="input-row">
                <textarea id="message" placeholder="Type your message here..." <?php echo ($remarks === 'No remarks available' || empty($remarks)) ? 'disabled' : ''; ?>>Complied</textarea>
                <button id="sendMessage" class="send-button" <?php echo ($remarks === 'No remarks available' || empty($remarks)) ? 'disabled' : ''; ?>>
                    <i class="fas fa-paper-plane"></i> 
                </button>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('sendMessage').addEventListener('click', function () {
        var message = document.getElementById('message').value;
        var appid = <?= $application['id'] ?>; 

        if (message.trim() === '') {
            alert("Please enter a message.");
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'send_message.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                alert("Message sent successfully!");
                document.getElementById('message').value = ''; 
            }
        };
        xhr.send('appid=' + appid + '&message=' + encodeURIComponent(message));
    });
    </script>
</body>
</html>
