function toggleFilterPanel() {
    const panel = document.getElementById("filterPanel");
    panel.style.display = (panel.style.display === "none" || panel.style.display === "") ? "flex" : "none";
}

function filterTable(filterType, filterValue) {
    console.log("Filtering by", filterType, "with value", filterValue);
}

// function clearFilters() {
//     document.getElementById('applicationType').value = '';
//     document.getElementById('status').value = '';
//     filterTable('application_type', 'all');
//     filterTable('status', 'all');
// }

// let currentFilters = {
//     application_type: 'all',
//     status: 'all'
// };

// function filterTable(filterType, filterValue) {
//     currentFilters[filterType] = filterValue;

//     const rows = document.querySelectorAll('#applicationTableBody tr');

//     rows.forEach(row => {
//         const applicationType = row.cells[1].textContent.trim();
//         const status = row.cells[4].textContent.trim();
        
//         const matchesApplicationType = currentFilters.application_type === 'all' || applicationType === currentFilters.application_type;
//         const matchesStatus = currentFilters.status === 'all' || status === currentFilters.status;

//         if (matchesApplicationType && matchesStatus) {
//             row.style.display = '';
//         } else {
//             row.style.display = 'none';
//         }
//     });
// }

// new:



// // search:
// // new
// function debouncedSearch() {
// clearTimeout(window.searchTimeout);
// window.searchTimeout = setTimeout(searchApplications, 300);
// }

// function searchApplications() {
//     const searchInput = document.getElementById('searchInput').value.toLowerCase();
//     const rows = document.querySelectorAll('#applicationTableBody tr');
//     rows.forEach(row => {
//         const cells = row.querySelectorAll('td');
//         const rowContainsSearchTerm = Array.from(cells).some(cell => 
//             cell.textContent.toLowerCase().includes(searchInput)
//         );
//         row.style.display = rowContainsSearchTerm ? '' : 'none';
//     });
// }

// $(document).ready(function() {
//     const table = $('#applicationTable').DataTable({
//     responsive: true,
//     autoWidth: false,
//     searching: false,
//     paging: true,
//     info: true
//     });
// });

// function handleAction(select) {
//     const row = select.closest('tr');
//     const applicationType = row.cells[1].innerText;
//     const ownerName = row.cells[2].innerText;
//     const schedule = row.cells[3].innerText;
//     const status = row.cells[4].innerText;
//     const appId = row.cells[0].querySelector('input[type="checkbox"]').value;

//     const facilityName = row.querySelector('.facilityName').value;
//     const address = row.querySelector('.address').value;
//     const email = row.querySelector('.email').value;

//     if (select.value === "view") {
//         viewClient(applicationType, facilityName, ownerName, status, address, email, appId, schedule);
//     } else if (select.value === "edit") {
//         editClient(applicationType, facilityName, ownerName, status, address, email, appId, schedule);
//     }

//     select.value = "";  
// }

function viewClient(applicationType, facilityName, ownerName, status, address, email, appId, schedule) {
    const viewInfoList = document.getElementById("viewClientInfo");
    viewInfoList.innerHTML = ""; 
    let appLabel = 'Business/Trade Name'

    if(applicationType == 'building' || applicationType == 'occupancy'){
        appLabel = 'Structure/Facility Name'
    }


    const details = [
        { label: "Application Type", value: applicationType },
        { label: appLabel, value: facilityName },
        { label: "Owner Name", value: ownerName },
        { label: "Status", value: status },
        { label: "Address", value: address },
        { label: "Email", value: email },
        { label: "Application ID", value: appId },
        { label: "Inspection Schedule", value: schedule },
        { label: "Inspection Status", value: status },
    ];

    //qr
    const currentUrl = window.location.origin; 
    const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(currentUrl + "/bfpMingla/scanned_inspection.php?appid=" + appId)}&size=150x150`;
    document.getElementById('qrCodeImage').src = qrCodeUrl;
    document.getElementById('qrCodeImage').style.display = 'block';

    //details
    details.forEach(detail => {
        const listItem = document.createElement("li");
        listItem.innerHTML = `<strong>${detail.label}:</strong> ${detail.value}`;
        viewInfoList.appendChild(listItem);
    });

    // Checklist based on applicationType
    let checklist = [];
    if (applicationType === 'building') {
        checklist = [
            "Endorsement from OBO",
            "Application for Building Permit Form (From OBO/should be filled out)",
            "Building Plan (1 set) signed and sealed by the designer/contractor",
            "Photocopy of valid PRC I.D of Structural Plan",
            "Photocopy of valid PRC I.D of Electrical Plan",
            "Photocopy of valid PRC I.D of Plumbing Plan",
            "Bill of Materials signed and sealed by the designer/contractor duly notarized (Original Copy)",
            "Barangay Clearance",
            "Tax Declaration, Tax Clearance",
            "SPA/Authorization from the owner (Photocopy of Owner's & Representative's ID)"
        ];
    } else if (applicationType === 'occupancy') {
        checklist = [
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
        ];
    } else if (applicationType === 'new_business_permit') {
        checklist = [
            "Application for Business Permit (From BPLO)",
            "OR from BPLO",
            "OR from BFP",
            "Occupancy Permit (LGU)",
            "FSIC for Occupancy Permit (BFP)",
            "Lessor Permit (LGU)",
            "FSIC for business as lessor (owner of the building)",
            "Contract of Lease",
            "Insurance Policy (if any)",
            "Fire Safety Maintenance Report",
            "SPA/Authorization Letter from the owner (if representative)",
            "Photocopy of Owner's & Representative's I.D",
            "Photograph of the business establishment",
            "Sketch",
            "Tax Declaration"
        ];
    } else if (applicationType === 'renewal_business_permit') {
        checklist = [
            "Application for Business Permit (From BPLO)",
            "OR from BPLO",
            "OR from BFP",
            "Occupancy Permit (LGU)",
            "FSIC for Occupancy Permit (BFP)",
            "Lessor Permit (LGU)",
            "FSIC for business as lessor (owner of the building)",
            "Contract of Lease",
            "Insurance Policy (if any)",
            "Fire Safety Maintenance Report",
            "SPA/Authorization Letter from the owner (if representative)",
            "Photocopy of Owner's & Representative's I.D",
            "Photograph of the business establishment",
            "Sketch",
            "Tax Declaration"
        ];
    }

    // Display the checklist
    const checklistSection = document.createElement("section");
    checklistSection.innerHTML = "<h6>Required Documents for Onsite Inspection</h6>";
    const checklistList = document.createElement("ul");

    checklist.forEach(item => {
        const checklistItem = document.createElement("li");
        checklistItem.textContent = item;
        checklistList.appendChild(checklistItem);
    });

    checklistSection.appendChild(checklistList);
    viewInfoList.appendChild(checklistSection);
    
    document.getElementById("viewClientModal").style.display = "block"; 
}

// function editClient(applicationType, facilityName, ownerName, status, address, email, appId, schedule) {
//     document.getElementById("clientId").value = appId; 
//     document.getElementById("schedule").value = schedule;

//     const statusSelect = document.getElementById("status");

//     for (let i = 0; i < statusSelect.options.length; i++) {
//         if (statusSelect.options[i].textContent === status) {
//             statusSelect.selectedIndex = i;
//             break;
//         }
//     }
//     document.getElementById("editClientModal").style.display = "block"; 
// }

function editClient(applicationType, facilityName, ownerName, status, address, email, appId, schedule) {
    document.getElementById("clientId").value = appId;

    if (schedule) {
        const scheduleParts = schedule.split(' - ');

        if (scheduleParts.length === 2) {
            const startDate = new Date(scheduleParts[0]);  
            const endDate = new Date(scheduleParts[1]);    
            
            console.log("Start Date: ", startDate);  
            console.log("End Date: ", endDate);      

            if (startDate.getFullYear() < 1900 || endDate.getFullYear() < 1900) {
                alert("Invalid date. Please check the schedule format.");
                return;
            }

            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;  
            };

            document.getElementById("inspectionDate").value = formatDate(startDate);
        }
    }

    const statusSelect = document.getElementById("status");
    for (let i = 0; i < statusSelect.options.length; i++) {
        if (statusSelect.options[i].textContent === status) {
            statusSelect.selectedIndex = i;
            break;
        }
    }

    document.getElementById("editClientModal").style.display = "block"; 
}


function saveClient() {
const appId = document.getElementById("clientId").value;
const inspectionDateInput = document.getElementById("inspectionDate");
const startDate = new Date(inspectionDateInput.value);

if (isNaN(startDate)) {
    alert("Please select a valid inspection date.");
    return;
}

 const endDate = new Date(startDate);
 endDate.setDate(startDate.getDate() + 2);  

 const options = { year: 'numeric', month: 'short', day: 'numeric' };
 const formattedStartDate = startDate.toLocaleDateString('en-US', options);
 const formattedEndDate = endDate.toLocaleDateString('en-US', options);

 const scheduleRange = `${formattedStartDate} - ${formattedEndDate}`;

console.log({ appId, scheduleRange });

$.ajax({
    url: '../ajax.php?action=update_inspection',
    method: 'POST',
    data: {
        appId: appId,
        // schedule: schedule
        schedule: scheduleRange
    },
    beforeSend: function() {
        Swal.fire({
            icon: "info",
            title: "Please wait...",
            timer: 60000,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    },
    success: function(data) {
        console.log('Response:', data);
        if (data === 'success') {
            window.location.reload();
        } else {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Something went wrong!",
            });
        }
    },
});
}



function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}

function toggleAllCheckboxes(master) {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => checkbox.checked = master.checked);
}

window.onclick = function(event) {
    const viewModal = document.getElementById("viewClientModal");
    const editModal = document.getElementById("editClientModal");
    if (event.target === viewModal) {
        viewModal.style.display = "none";
    }
    if (event.target === editModal) {
        editModal.style.display = "none";
    }
};

document.getElementById('goButton').addEventListener('click', function() {
    const action = document.getElementById('actionSelect').value;
    
    if (action === 'update') {
        const checkedCheckboxes = document.querySelectorAll('input[name="action[]"]:checked');
        
        const appIds = [];
        checkedCheckboxes.forEach(function(checkbox) {
            appIds.push(checkbox.value);
        });

        if (appIds.length > 0) {
            document.getElementById('updateStatusModal').style.display = 'block';
            
            document.querySelector('#updateStatusModal .btn').onclick = function() {
                const newStatus = document.getElementById('action_status').value;
                const remarks = document.getElementById('remarks').value;

                $.ajax({
                    url: '../ajax.php?action=update_multi_inspections',
                    method: 'POST',
                    data: {
                        appIds: appIds,
                        status: newStatus,
                        remarks: remarks
                    },
                    beforeSend: function() {
                        Swal.fire({
                            icon: "info",
                            title: "Please wait...",
                            timer: 60000,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(data) {
                        document.getElementById('updateStatusModal').style.display = 'none';  
                        console.log('Response:', data);
                        if (data === 'success') {
                            Swal.fire(
                                'Updated!',
                                'The selected inspections have been updated successfully.',
                                'success'
                            ).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                text: "Something went wrong with the update!",
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: "Failed to update inspections. Please try again.",
                        });
                    }
                });
            };
        } else {
            Swal.fire({
                icon: "warning",
                title: "No Selection",
                text: "Please select at least one inspection to update.",
            });
        }
    } else {
        Swal.fire({
            icon: "warning",
            title: "No Action Selected",
            text: "Please select an action from the dropdown.",
        });
    }
});