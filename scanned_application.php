<?php
require_once('./function.php');

if (isset($_GET['appid'])) {
    $result = GetApplication('id', $_GET['appid']);
    $application = $result->fetch_assoc();

    if (!$application) {
        header("Location: ./index/index.php");
        exit;
    }

    // Sanitize and check the issuance_status
    $status = strtolower(trim($application['issuance_status'])); // Ensure it's in lowercase and trimmed
    $application['checklist'] = json_decode($application['checklist'], true);
} else {
    header("Location: ./index/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Tracking - BFP Minglanilla</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
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
        max-width: 1000px; /* Reduced max-width for a more balanced look */
        margin: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        background-color: #ffffff;
        flex-direction: column;
    }

    .content-container {
        width: 100%;
        padding: 20px; /* Reduced padding for better proportion */
    }

    .header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 25px; /* Reduced bottom margin */
    }

    .header-logo {
        width: 50px;
        height: 50px;
        object-fit: cover;
    }

    h1 {
        font-size: 1.5rem; /* Slightly smaller title */
        font-weight: 600;
        color: #d32f2f;
        margin: 0;
    }

    /* Status Reminder styles */
    .status-reminder {
        font-size: 1rem; /* Smaller font size */
        font-weight: 600;
        margin-bottom: 20px;
        padding: 15px;
        background-color: #f8f8f8;
        border-left: 5px solid #d32f2f;
        color: #333;
        border-radius: 5px;
    }

    .status-reminder.completed {
        background-color: #e8f5e9;
        border-left: 5px solid #388e3c;
        color: #388e3c;
    }

    .status-reminder.pending {
        background-color: #fff3e0;
        border-left: 5px solid #ff9800;
        color: #ff9800;
    }

    .requirements-container {
        display: flex;
        justify-content: space-between;
        gap: 15px; /* Reduced gap for a more compact layout */
        margin-bottom: 25px; /* Reduced bottom margin */
    }

    .requirements-section {
        width: 48%;
        padding: 15px; /* Reduced padding */
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        background-color: #fff;
    }

    .requirements-section h3 {
        font-size: 1.1rem; /* Slightly smaller font size */
        font-weight: 600;
        margin-bottom: 10px;
        color: #333;
    }

    ul {
        list-style: none;
        padding: 0;
    }

    li {
        font-size: 0.95rem; /* Smaller font size for list items */
        margin-bottom: 12px; /* Slightly reduced margin */
    }

    li strong {
        font-weight: 600;
        color: #d32f2f;
    }

    .status-submitted {
        color: #388e3c;
    }

    .status-missing {
        color: #d32f2f;
    }

    .history-item {
        display: flex;
        justify-content: space-between;
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
    }

    .history-item span {
        font-size: 0.95rem; /* Slightly smaller font size for history items */
        color: #666;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .main-container {
            flex-direction: column;
            margin: 10px;
        }

        .header h1 {
            font-size: 1.3rem;
        }

        .requirements-container {
            flex-direction: column;
        }

        .requirements-section {
            width: 100%;
            margin-bottom: 20px;
        }

        li {
            font-size: 0.9rem;
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

        .requirements-section {
            width: 100%;
            padding: 12px; /* Even smaller padding for mobile */
        }

        li {
            font-size: 0.85rem; /* Even smaller font size on mobile */
        }

        .status-submitted,
        .status-missing {
            font-size: 0.9rem;
        }
    }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="content-container">
            <div class="header">
                <img src="./img/bfp3.png" alt="BFP Logo" class="header-logo">
                <h1>BFP Minglanilla Fire Station - Application Tracker</h1>
            </div>

            <!-- Status Reminder -->
            <?php if ($status == 'pending'): ?>
                <div class="status-reminder pending">
                    Please follow up on your outstanding requirements. Ensure all required documents are submitted for processing.
                </div>
            <?php elseif ($status == 'completed'): ?>
                <div class="status-reminder completed">
                    All required documents have been submitted. You are now eligible to proceed to the second phase: Inspection.
                </div>
            <?php else: ?>
                <div class="status-reminder unknown">
                    The status of your application is currently unknown. Please contact BFP support for further assistance.
                </div>
            <?php endif; ?>

            <div class="requirements-container">
                <div class="requirements-section">
                    <h3>Submitted Requirements</h3>
                    <ul id="submittedList">
                    </ul>
                </div>

                <div class="requirements-section">
                    <h3>Missing Requirements</h3>
                    <ul id="missingList">
                    </ul>
                </div>
            </div>

            <section class="history">
                <h3>Application History</h3>
                <ul id="historyList">
                </ul>
            </section>
        </div>
    </div>
</body>
<script>
// JavaScript to dynamically handle the checklist and history data

const applicationTypeData = {
    0: {
        checklist: [
            "Endorsement from OBO",
            "Application for Building Permit Form (From OBO/should be filled out)",
            "Building Plan (1 set) signed and sealed by the designer/contractor",
            "Photocopy of each valid PRC ID of the following signatories:",
            "Barangay Clearance",
            "Tax Declaration, Tax Clearance",
            "SPA/Authorization from the owner (Photocopy of Owner's & Representative's ID)",
        ]
    },
    1: {
        checklist: [
            "Completion Form",
            "Fire Safety Evaluation Clearance",
            "Office Receipt from OBO",
            "Approved Building Plans and Specifications",
            "Accurate Floor Area",
            "Photograph of the Building",
            "Location Map using Google Map",
            "SPA / Authorization Letter from the owner (if representative)",
            "Photocopy of Owner's & Representative's ID",
            "Fire and Life Safety Master Plan (PALSAM)"
        ]
    },
    2: {
        checklist: [
            "Application for Business Permit (From BPLO)",
            "OR from BPLO",
            "OR from BFP",
            "Occupancy Permit (LGU)",
            "FSIC for business as lessor (owner of the building)",
            "Contract of Lease",
            "Insurance Policy (if any)",
            "Fire Safety Maintenance Report",
            "SPA/Authorization Letter from the owner (if representative)",
            "Photocopy of the business establishment",
            "Sketch",
            "Tax Declaration"
        ]
    },
};

document.addEventListener("DOMContentLoaded", function() {
    const appType = <?= json_encode($application['type']) ?>;
    const checklist = <?= json_encode($application['checklist']) ?> || [];

    const expectedChecklist = applicationTypeData[appType]?.checklist || [];
    const submittedList = document.getElementById("submittedList");
    const missingList = document.getElementById("missingList");

    expectedChecklist.forEach(item => {
        const listItem = document.createElement("li");
        listItem.textContent = item;

        if (checklist.includes(item.toLowerCase().replace(/\s+/g, '_'))) {
            submittedList.appendChild(listItem);
        } else {
            missingList.appendChild(listItem);
        }
    });

    // Fetching application history
    fetch(`ajax.php?action=fetch_history&email=${encodeURIComponent("<?= $application['email_address'] ?>")}`)
        .then(response => response.json())
        .then(history => {
            const historyList = document.getElementById("historyList");
            history.forEach(record => {
                const historyItem = document.createElement("li");
                historyItem.innerHTML = `<div style="display: flex; justify-content: space-between">
                    <span>${record.business_trade_name}</span> <span>${record.created_at}</span>
                </div>`;
                historyList.appendChild(historyItem);
            });
        })
        .catch(error => console.error('Error fetching application history:', error));
});
</script>
</html>
