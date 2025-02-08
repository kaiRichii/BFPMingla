function toggleAllCheckboxes(master) {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => checkbox.checked = master.checked);
}
function toggleFilterPanel() {
const panel = document.querySelector(".filter-panel");
const toggleButton = document.querySelectorAll(".filter-toggle")[0]; 

if (panel.style.display === "none" || panel.style.display === "") {
   
    panel.style.display = "flex";
   
    toggleButton.style.display = "none";
} else {
   
    panel.style.display = "none";
   
    toggleButton.style.display = "flex";
}
}

// new
$(document).ready(function() {
const table = $('#incidentTable').DataTable({ 
    responsive: true,
    autoWidth: false,
    searching: true,  
    paging: true,
    info: true
});

// Attach the custom search input function
$('#searchInput').on('input', function() {
    const searchValue = $(this).val();
    table.search(searchValue).draw();  
});
});




function populateLocationFilter() {
const rows = document.querySelectorAll('#incidentTable tbody tr');
const locationSet = new Set(); // Using Set to avoid duplicates

rows.forEach(row => {
    const location = row.cells[3].textContent.trim().toLowerCase();
    locationSet.add(location);
});

const locationSelect = document.getElementById('locationFilter');
locationSelect.innerHTML = '<option value="all">All</option>'; 
locationSet.forEach(location => {
    const option = document.createElement('option');
    option.value = location;
    option.textContent = location.charAt(0).toUpperCase() + location.slice(1);
    locationSelect.appendChild(option);
});
}

function filterTable(filterType, filterValue) {
const rows = document.querySelectorAll('#incidentTable tbody tr');
let match;

rows.forEach(row => {
    match = true;

    // Get row data
    const incidentDate = row.cells[1].textContent.trim();
    const time = row.cells[2].textContent.trim();
    const location = row.cells[3].textContent.trim().toLowerCase();
    const status = row.cells[6].textContent.trim().toLowerCase();

    const standardizedStatus = status.replace(/\s+/g, '_');  

    if (filterType === 'date' && filterValue !== '' && !incidentDate.includes(filterValue)) {
        match = false;
    }

    if (filterType === 'time' && filterValue !== '' && !time.includes(filterValue)) {
        match = false;
    }

    if (filterType === 'location' && filterValue !== 'all' && !location.includes(filterValue.toLowerCase())) {
        match = false;
    }

    if (filterType === 'status' && filterValue !== 'all' && standardizedStatus !== filterValue.toLowerCase()) {
        match = false;
    }
    row.style.display = match ? '' : 'none';
});
}


function clearFilters() {
document.getElementById('dateFilter').value = '';
document.getElementById('timeFilter').value = '';
document.getElementById('locationFilter').value = 'all';
document.getElementById('statusFilter').value = 'all';

const rows = document.querySelectorAll('#incidentTable tbody tr');
rows.forEach(row => {
    row.style.display = '';
});
}


document.addEventListener("DOMContentLoaded", function() {
populateLocationFilter();
});


function toggleSelectAll(selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked; 
    });
}

function handleAction(select, incidentId) {
const action = select.value;

if (action === "view" || action === "update") {
    // Make an AJAX request to fetch data from PHP
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'fetch_incident_details.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const data = JSON.parse(xhr.responseText);
            
            if (action === "view") {
                viewIncident(data);
            } else if (action === "update") {
                openUpdateModal(data);
            }
        }
    };
    xhr.send("id=" + incidentId);  
}

select.value = "";  
}

// Populate the view modal with the fetched data
function viewIncident(data) {
const incidentInfo = `
    <li><strong>ID:</strong> ${data.id}</li>
    <li><strong>Incident Date:</strong> ${data.incident_date}</li>
    <li><strong>Time:</strong> ${data.time}</li>
    <li><strong>Location:</strong> ${data.location}</li>
    <li><strong>Owner/Occupant:</strong> ${data.owner_occupant}</li>
    <li><strong>Occupancy Type:</strong> ${data.occupancy_type}</li>
    <li><strong>Cause of Fire:</strong> ${data.cause_of_fire}</li>
    <li><strong>Estimated Damages (PHP):</strong> ${data.estimated_damages}</li>
    <li><strong>Casualties/Injuries:</strong> ${data.casualties_injuries}</li>
    <li><strong>Fire Control Time:</strong> ${data.fire_control_time}</li>
    <li><strong>Inspector In Charge:</strong> ${data.inspector_in_charge}</li>
    <li><strong>Investigation Report Date:</strong> ${data.investigation_report_date}</li>
    <li><strong>Status:</strong> ${data.status}</li>
`;
document.getElementById("viewIncidentInfo").innerHTML = incidentInfo;
document.getElementById("viewIncidentModal").style.display = "block";
}

// Populate the update modal with the fetched data
function openUpdateModal(data) {
document.getElementById("incidentId").value = data.id;
document.getElementById("updateIncidentDate").value = data.incident_date;
document.getElementById("updateTime").value = data.time;
document.getElementById("updateLocation").value = data.location;
document.getElementById("updateOwner").value = data.owner_occupant;
document.getElementById("updateOccupancyType").value = data.occupancy_type;
document.getElementById("updateCauseOfFire").value = data.cause_of_fire;
document.getElementById("updateEstimatedDamages").value = data.estimated_damages;
document.getElementById("updateCasualtiesInjuries").value = data.casualties_injuries;
document.getElementById("updateFireControlTime").value = data.fire_control_time;
document.getElementById("updateInspector").value = data.inspector_in_charge;
document.getElementById("updateReportDate").value = data.investigation_report_date;
document.getElementById("updateStatus").value = data.status;

document.getElementById("updateIncidentModal").style.display = "block";
}
// Close view modal
function closeViewModal() {
    document.getElementById("viewIncidentModal").style.display = "none";
}

// Close update modal
function closeUpdateModal() {
    document.getElementById("updateIncidentModal").style.display = "none";
}

// Update incident function
function updateIncident(event) {
    event.preventDefault(); 
    const formData = {
        incidentId: document.getElementById('incidentId').value, 
        incidentDate: document.getElementById('updateIncidentDate').value,
        time: document.getElementById('updateTime').value,
        location: document.getElementById('updateLocation').value,
        owner: document.getElementById('updateOwner').value,
        occupancyType: document.getElementById('updateOccupancyType').value,
        causeOfFire: document.getElementById('updateCauseOfFire').value,
        estimatedDamages: document.getElementById('updateEstimatedDamages').value,
        casualtiesInjuries: document.getElementById('updateCasualtiesInjuries').value,
        fireControlTime: document.getElementById('updateFireControlTime').value,
        inspector: document.getElementById('updateInspector').value,
        reportDate: document.getElementById('updateReportDate').value,
        status: document.getElementById('updateStatus').value,
    };

    $.ajax({
        url: '../ajax.php?action=update_incident',
        method: 'POST',
        data: formData,
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
                Swal.fire(
                    'Success!',
                    'Incident updated successfully.',
                    'success'
                ).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Something went wrong!",
                });
            }
        },
    })

    closeUpdateModal();
}

document.getElementById('goButton').addEventListener('click', function() {
    const action = document.getElementById('actionSelect').value;
    
    if (action === 'export_pdf') {
        const checkedCheckboxes = document.querySelectorAll('input[name="action[]"]:checked');

        const incidentIds = [];
        checkedCheckboxes.forEach(function(checkbox) {
            incidentIds.push(checkbox.value);
        });

        if (incidentIds.length > 0) {
            window.location.href = 'export_incident.php?action=pdf&incidentIds=' + encodeURIComponent(incidentIds.join(','));
        } else {
            alert('Please select at least one incident to export.');
        }
    } else if (action === 'export_excel'){
        const checkedCheckboxes = document.querySelectorAll('input[name="action[]"]:checked');

        const incidentIds = [];
        checkedCheckboxes.forEach(function(checkbox) {
            incidentIds.push(checkbox.value);
        });

        if (incidentIds.length > 0) {
            window.location.href = 'export_incident.php?action=excel&incidentIds=' + encodeURIComponent(incidentIds.join(','));
        } else {
            alert('Please select at least one incident to export.');
        }
    } else if (action === 'delete'){
        const checkedCheckboxes = document.querySelectorAll('input[name="action[]"]:checked');

        const incidentIds = [];
        checkedCheckboxes.forEach(function(checkbox) {
            incidentIds.push(checkbox.value);
        });

        if (incidentIds.length > 0) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../ajax.php?action=delete_incident',
                        method: 'POST',
                        data: {
                            incidentIds: incidentIds
                        },
                        success: function(data) {
                            console.log('Response:', data);
                            if (data === 'success') {
                                Swal.fire(
                                    'Deleted!',
                                    'The selected incidents have been deleted.',
                                    'success'
                                ).then(() => {
                                    window.location.reload(); 
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Oops...",
                                    text: "Something went wrong with the deletion!",
                                });
                            }
                        }
                    });
                }
            })
        } else {
            Swal.fire({
                icon: "warning",
                title: "No Selection",
                text: "Please select at least one incident to update.",
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

window.onclick = function(event) {
    const viewModal = document.getElementById("viewIncidentModal");
    const updateModal = document.getElementById("updateIncidentModal");
    if (event.target === viewModal) {
        closeViewModal();
    }
    if (event.target === updateModal) {
        closeUpdateModal();
    }
}