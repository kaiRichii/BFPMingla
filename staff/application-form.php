<?php
session_start();

include '../db_connection.php'; 

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index/index.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Sidebar - Enhanced</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <!-- Logo Section -->
        <nav class="menu" role="navigation">
            <a href="staff.php" class="menu-item">
                <i class="fas fa-house-chimney"></i> 
                <span>Home</span>
            </a>
            <div class="menu-item clients active">
                <a href="application-list.php" class="list">
                    <i class="fas fa-user-friends"></i> 
                    <span>Clients</span>
                </a>
                <a href="application-form.php" class="add-link">
                    <i class="fas fa-circle-plus"></i> 
                    <span>Add</span>
                </a>
            </div>
            <a href="report.php" class="menu-item clients">
                <i class="fas fa-chart-pie"></i> 
                <span>Reports</span>
            </a>
        </nav>
        <footer class="sidebar-footer">
            <!-- <a href="profile.php" class="footer-item">
                <i class="fas fa-user-circle"></i> 
                <span>Profile</span>
            </a> -->
            <a href="changePassword.php" class="footer-item">
                <i class="fa-solid fa-key"></i> 
                <span>Change Password</span>
            </a>
        </footer>
    </aside>

    <header class="header" id="header">
        <button class="toggle-btn" id="toggle-btn"><i class="fa-solid fa-bars"></i></button>
        <nav class="header-nav">
            <a href="../logout.php" class="header-nav-item">
                <i class="fas fa-right-from-bracket"></i> 
                Logout
            </a>
        </nav>
    </header>

    <div class="content" id="mainContent">
        <h2>Application Form</h2>
        <form action="../submit_application.php" method="post" id="applicationForm">
            <div class="form-group">
                <label for="owner_name">Owner Name:</label>
                <input type="text" name="owner_name" id="owner_name" oninput="fetchApplicationData(this.value)">
                <div id="suggestions" class="suggestions-container"></div>
            </div>

            <div class="form-group">
                <label for="application_type">Application Type:</label>
                <select id="application_type" name="application_type" onchange="updateForm()">
                    <option value="">------</option>
                    <option value="building">Building</option>
                    <option value="occupancy">Occupancy</option>
                    <option value="new_business_permit">New Business Permit</option>
                    <option value="renewal_business_permit">Renewal Business Permit</option>
                </select>
            </div>
            
            <div id="dynamicFields" class="dynamic-fields-container"></div>
            
            <div id="checklistContainer" class="checklist-container">
                <h3>Checklist of Requirements</h3>
                <div class="checklist-item">
                    <input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes(this)">
                    <label for="selectAll">Select All</label>
                </div>
                <div id="dynamicChecklist"></div>
            </div>

            <input type="hidden" name="inspection_status" value="Pending Inspection">
            <input type="hidden" name="inspection_date" value="<?php echo date('Y-m-d'); ?>">

            <div class="button-container">
                <button type="submit">SAVE</button>
                <button type="button" onclick="clearForm()">Clear Form</button>
            </div>
        </form>
    </div>

    <script src="../sidebar/sidebar-toggle.js"></script>
    <script>
        function clearForm() {
         document.getElementById('applicationForm').reset();
        }

        const applicationTypeData = {
            building: {
                fields: ["Structure/Facility Name", "Address", "Contact Number", "Email Address"],
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
            occupancy: {
                fields: ["Structure/Facility Name", "Address", "Contact Number", "Email Address"],
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
            new_business_permit: {
                fields: ["Business/Trade Name", "Address", "Contact Number", "Email Address"],
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
            renewal_business_permit: {
                fields: ["Business/Trade Name", "Address", "Contact Number", "Email Address"],
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
            }
        };

        function updateForm() {
            const type = document.getElementById("application_type").value;
            const dynamicFields = document.getElementById("dynamicFields");
            const dynamicChecklist = document.getElementById("dynamicChecklist");

            dynamicFields.innerHTML = "";
            dynamicChecklist.innerHTML = "";

            if (type) {
                const data = applicationTypeData[type];

                data.fields.forEach(field => {
                    let fieldName = field.toLowerCase().replace(/\s+/g, '_');

                    if (field == "Structure/Facility Name" || field == "Business/Trade Name") {
                        fieldName = 'business_trade_name';
                    }

                    const div = document.createElement("div");
                    div.classList.add("form-group");
                    div.innerHTML = `<label for="${fieldName}">${field}:</label><input type="text" name="${fieldName}" id="${fieldName}" required>`;
                    dynamicFields.appendChild(div);
                });
                

                data.checklist.forEach(item => {
                    const itemId = item.toLowerCase().replace(/\s+/g, '_');
                    const div = document.createElement("div");
                    div.classList.add("checklist-item");
                    div.innerHTML = `<input type="checkbox" class="requirement-checkbox" id="checklist_${itemId}" name="checklist_${itemId}">
                                     <label for="checklist_${itemId}">${item}</label>`;
                    dynamicChecklist.appendChild(div);
                });

                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "type";
                hiddenInput.value = type === "building" ? "0" : type === "occupancy" ? "1" : "2";
                dynamicFields.appendChild(hiddenInput);
            }
        }

        function toggleAllCheckboxes(selectAllCheckbox) {
            const checkboxes = document.querySelectorAll("#dynamicChecklist input[type='checkbox']");
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        document.getElementById("applicationForm").onsubmit = function() {
            const type = document.getElementById("application_type").value;
            if (!type) {
                alert("Please select an application type.");
                return false;
            }

            const fields = document.querySelectorAll("#dynamicFields input");
            let missingFields = [];

            for (let field of fields) {
                if (!field.value.trim()) {
                    missingFields.push(field.previousElementSibling.innerText);
                    field.focus();
                    return false; // Prevent form submission
                }
            }

            if (missingFields.length > 0) {
                alert(`Please fill in the following required fields: ${missingFields.join(', ')}`);
                return false;
            }

            return true; // Allow form submission if validations pass
        };

        function fetchApplicationData(name) {
            if (name.length < 2) {
                document.getElementById('suggestions').innerHTML = "";
                return;
            }

            fetch(`../fetch_data.php?name=${encodeURIComponent(name)}`)
                .then(response => response.json())
                .then(data => {
                    const suggestionsContainer = document.getElementById('suggestions');
                    suggestionsContainer.innerHTML = "";

                    data.forEach(app => {
                        const suggestionItem = document.createElement("div");
                        suggestionItem.innerText = app.owner_name;
                        suggestionItem.classList.add("suggestion-item");
                        suggestionItem.onclick = () => autofillFields(app);
                        suggestionsContainer.appendChild(suggestionItem);
                    });
                })
                .catch(error => console.error("Error fetching data:", error));
        }

        function autofillFields(app) {
            document.getElementById("owner_name").value = app.owner_name;
            document.getElementById("business_trade_name").value = app.business_trade_name;
            document.getElementById("address").value = app.address;
            document.getElementById("contact_number").value = app.contact_number;
            document.getElementById("email_address").value = app.email_address;

            document.getElementById('suggestions').innerHTML = "";
        }
    </script>
</body>
</html>
