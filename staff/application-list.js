const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggle-btn');
        const header = document.getElementById('header');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            if (sidebar.classList.contains('collapsed')) {
                header.style.backgroundColor = '#242426'; 
            } else {
                header.style.backgroundColor = '#1c1c1e';
            }
        });

        // filter:
        // Toggle the filter panel visibility(new)
        function toggleFilterPanel() {
            const panel = document.getElementById("filterPanel");
            panel.style.display = (panel.style.display === "none" || panel.style.display === "") ? "flex" : "none";
        }
        function toggleFilter() {
            const filterSection = document.getElementById('filterSection');
            filterSection.classList.toggle('collapsed');
        }

        let currentFilters = {
            application_type: 'all',
            status: 'all',
            date: ''
        };
        
        function filterTable(filterType, filterValue) {
            // Update currentFilters with the selected filter
            if (filterType === 'date') {
                currentFilters.date = filterValue;
            } else {
                currentFilters[filterType] = filterValue;
            }
        
            const rows = document.querySelectorAll('#applicationTableBody tr');
        
            rows.forEach(row => {
                const applicationType = row.cells[1].textContent.trim();
                const status = row.cells[4].textContent.trim();
                const createdAt = row.cells[10].textContent.trim(); // Adjusted index to 10 (for the hidden created_at column)
        
                // Filter by application type and status
                const matchesApplicationType = currentFilters.application_type === 'all' || applicationType === currentFilters.application_type;
                const matchesStatus = currentFilters.status === 'all' || status === currentFilters.status;
        
                // Filter by date: Only compare the date part of created_at (YYYY-MM-DD)
                const matchesDate = currentFilters.date === '' || createdAt.split(' ')[0] === currentFilters.date;
        
                // Show or hide the row based on all conditions
                if (matchesApplicationType && matchesStatus && matchesDate) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Clear all filters and reset values
        function clearFilters() {
            document.getElementById("applicationType").value = "";
            document.getElementById("status").value = "";
            document.getElementById("dateFilter").value = "";
            
            // Reset the filter logic
            filterTable('application_type', "all");
            filterTable('status', "all");
            filterTable('date', "");
        }

        // search:
        function debouncedSearch() {
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(searchApplications, 300);
        }

        function searchApplications() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#applicationTableBody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const rowContainsSearchTerm = Array.from(cells).some(cell => 
                    cell.textContent.toLowerCase().includes(searchInput)
                );
                row.style.display = rowContainsSearchTerm ? '' : 'none';
            });
        }

        function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        }

        $(document).ready(function() {
        const table = $('#applicationTable').DataTable({
            responsive: true,
            autoWidth: false,
            searching: true,
            paging: true,
            info: true
        });

            
        // Handle 'Select All' checkbox within DataTable
        $('#selectAll').on('click', function() {
            const rows = table.rows({ 'search': 'applied' }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });

        // Delete Action Handling
        $('#goButton').on('click', function() {
            const action = $('#actionSelect').val();

            if (action === 'delete') {
                // Collect IDs of selected rows
                const selectedIds = [];
                const rowsToDelete = [];

                table.rows().every(function() {
                    const row = this.node();
                    const checkbox = $(row).find('input[type="checkbox"]');

                    if (checkbox.is(':checked')) {
                        selectedIds.push(checkbox.val());
                        rowsToDelete.push(this);
                    }
                });

                if (selectedIds.length > 0) {
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
                            // AJAX request to delete selected applications
                            $.ajax({
                                url: '../ajax.php?action=delete_application',
                                method: 'POST',
                                data: { appIds: selectedIds },
                                success: function(data) {
                                    if (data === 'success') {
                                        Swal.fire(
                                            'Deleted!',
                                            'The selected applications have been deleted.',
                                            'success'
                                        ).then(() => {
                                            // Remove deleted rows from DataTable without reloading
                                            rowsToDelete.forEach(row => row.remove().draw(false));
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
                    });
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "No Selection",
                        text: "Please select at least one application to delete.",
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
    });

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

        function handleAction(selectElement, checklist, type) {
        const action = selectElement.value;
        const row = selectElement.closest('tr');
        const appId = row.cells[0].querySelector('input[type="checkbox"]').value;

        if (action === 'edit') {
            editClient(
                row.cells[1].textContent,
                row.cells[2].textContent,
                row.cells[3].textContent,
                row.cells[4].textContent,
                row.cells[6].textContent,
                row.cells[7].textContent,
                appId,
                checklist || [],
                type
            );
        } else if (action === 'generateInspection') {
            if(applicationTypeData[type].checklist.length == checklist.length){
                generateInspection(appId);
            }else{
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "can't generate inspection. requirements is incomplete.",
                });
            }
        } else if (action === 'view') {
            viewClient(
                row.cells[1].textContent,
                row.cells[2].textContent,
                row.cells[3].textContent,
                row.cells[4].textContent,
                row.cells[6].textContent,
                row.cells[7].textContent,
                appId,
                checklist || [],
                type
            );
        }

        selectElement.value = "";
    }

    function viewClient(applicationType, facilityName, ownerName, status, address, email, appId, checklist, type) {
        const viewInfoList = document.getElementById("viewClientInfo");
        viewInfoList.innerHTML = ""; // Clear previous details\
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
        ];
        //qr
        const currentUrl = window.location.origin; 
        const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(currentUrl + "/bfpMinglanilla/scanned_application.php?appid=" + appId)}&size=150x150`;
            document.getElementById('qrCodeImage').src = qrCodeUrl;
            document.getElementById('qrCodeImage').style.display = 'block';

        //details
        details.forEach(detail => {
            const listItem = document.createElement("li");
            listItem.innerHTML = `<strong>${detail.label}:</strong> ${detail.value}`;
            viewInfoList.appendChild(listItem);
        });

        //requirements
        const submittedList = document.getElementById("submittedList");
        const missingList = document.getElementById("missingList");
        submittedList.innerHTML = "";
        missingList.innerHTML = "";

        const expectedChecklist = applicationTypeData[type].checklist.map(item => item );
        
        expectedChecklist.forEach(expectedItem => {

            const actualItem = checklist.find(item => item === expectedItem.toLowerCase().replace(/\s+/g, '_').replace(/\./g, '_'));

            const requirementItem = document.createElement("li");
            requirementItem.textContent = expectedItem

            if (actualItem) {
                submittedList.appendChild(requirementItem);
            } else {
                missingList.appendChild(requirementItem);
            }
        });

        //history
        fetch(`../ajax.php?action=fetch_history&email=${encodeURIComponent(email)}`)
        .then(response => response.json())
        .then(history => {
            const historyList = document.getElementById("historyList");
            historyList.innerHTML = ""; 

            history.forEach(record => {
                const historyItem = document.createElement("li");
                historyItem.innerHTML = `<div style="display: flex; justify-content: space-between"><span> ${record.business_trade_name}</span> ${record.created_at}</span></div>`;
                historyList.appendChild(historyItem);
            });
        })
        .catch(error => console.error('Error fetching application history:', error));

        document.getElementById("viewClientModal").style.display = "block"; // Show the modal
    }

    function editClient(applicationType, facilityName, ownerName, status, address, email, appId, checklist, type) {
        document.getElementById("applicationType").value = applicationType;
        document.getElementById("facilityName").value = facilityName;
        document.getElementById("ownerName").value = ownerName;
        document.getElementById("status").value = status;
        document.getElementById("address").value = address;
        document.getElementById("email").value = email;
        document.getElementById("clientId").value = appId;
        document.getElementById("type").value = type;

        const checklistItems = document.getElementById("checklistItems");
        checklistItems.innerHTML = ""; // Clear previous checklist items

        applicationTypeData[type].checklist.forEach(expectedItem => {
            const itemId = expectedItem.toLowerCase().replace(/\s+/g, '_');
            const actualItem = checklist.find(item => item === itemId);

            const div = document.createElement("div");
            div.innerHTML = `<input type="checkbox" class="requirement-checkbox" id="checklist_${itemId}" name="checklist[]" value="${itemId}" ${actualItem ? 'checked' : ''}>
            <label for="checklist_${itemId}">${expectedItem}</label>`;
            checklistItems.appendChild(div);

        });

        document.getElementById("editClientModal").style.display = "block"; // Show the modal
    }

    function saveClient() {
        const appId = document.getElementById("clientId").value;
        const type = document.getElementById("type").value;
        const applicationType = document.getElementById("applicationType").value;
        const facilityName = document.getElementById("facilityName").value;
        const ownerName = document.getElementById("ownerName").value;
        // const status = document.getElementById("status").value;
        const statusField = document.getElementById("status").value;
        const address = document.getElementById("address").value;
        const email = document.getElementById("email").value;

        // const checklist = Array.from(document.querySelectorAll('#checklistItems input[type="checkbox"]:checked'))
        //                     .map(checkbox => checkbox.value);

        const checklist = Array.from(document.querySelectorAll('#checklistItems input[type="checkbox"]:checked'))
        .map(checkbox => checkbox.value);

        const allChecklistItems = applicationTypeData[type].checklist.map(item => item.toLowerCase().replace(/\s+/g, '_'));
        const isAllChecked = allChecklistItems.every(item => checklist.includes(item));

        const status = isAllChecked ? 'Pending' : 'Pending';
        statusField.value = status;

        $.ajax({
            url: '../ajax.php?action=update_application',
            method: 'POST',
            data: {
                appId: appId,
                applicationType: applicationType,
                facilityName: facilityName,
                ownerName: ownerName,
                status: status,
                address: address,
                email: email,
                checklist: JSON.stringify(checklist)
            }, 
            success: function(data) {
                console.log('Response:', data);
                if (data == 'success') {
                    if (isAllChecked) {
                        document.getElementById("editClientModal").style.display = "none"; // Hide the modal
                        generateInspection(appId); // Call generateInspection if required
                    }else{
                        Swal.fire(
                        'Success!',
                        'Application has been saved and marked as Pending due to incomplete checklist items.',
                        'success'
                    ).then(() => {
                        window.location.reload(); 
                    });
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "something went wrong!",
                    });
                }
            },
        });
    }

    function generateInspection(appId){
        document.getElementById("clientId").value = appId;
        document.getElementById("generateInspectionModal").style.display = "block";
    }

    function saveInspection(){
        const appId = document.getElementById("clientId").value;
        const orderNumber = document.getElementById("orderNumber").value;
        const assignedInspector = document.getElementById("assignedInspector").value;

        if (!orderNumber) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Order Number is required."
            });
            return;
        }

        if (!assignedInspector || assignedInspector == "") {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Please select an Inspector."
            });
            return;
        }

        $.ajax({
            url: '../ajax.php?action=generate_inspection',
            method: 'POST',
            data: {
                appId: appId,
                orderNumber: orderNumber,
                assignedInspector: assignedInspector
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
                if (data == 'success') {
                    Swal.fire(
                        'Success!',
                        'Inspection schedule has been sent to your client.',
                        'success'
                    ).then(() => {
                        window.location.reload(); 
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "something went wrong!",
                    });
                }
            },
        });
    }

  