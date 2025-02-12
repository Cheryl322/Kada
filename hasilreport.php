<?php
// Start the session if needed
session_start();

// Include the header file
include 'headeradmin.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Sistem Koperasi KADA</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <style>
    /* Remove all duplicated styles that are already in headeradmin.php */
    /* Keep only styles specific to hasilreport.php */
    
    .report-boxes {
        display: flex;
        gap: 20px;
        margin: 20px 0;
        flex-wrap: wrap;
    }

    .report-box {
        flex: 0 0 100%;
        padding: 20px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #e0e0e0;
        margin-bottom: 20px;
    }

    .report-box h4 {
        margin: 0;
        color: rgb(34, 119, 210);
        font-size: 18px;
    }

    .invalid-feedback {
        display: none;
        color: #dc3545;
        margin-top: 0.25rem;
    }

    .required-field.is-invalid {
        border-color: #dc3545;
    }

    #dateRangeSelect {
        cursor: pointer;
        padding: 0.375rem 2.25rem 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        border-radius: 0.25rem;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    #dateRangeSelect:hover {
        background-color: #5f9ea0;
        border-color: #5f9ea0;
    }

    #dateRangeSelect:focus {
        box-shadow: 0 0 0 0.25rem rgba(102, 205, 170, 0.25);
        border-color: #5f9ea0;
    }

    /* Style the dropdown arrow */
    #dateRangeSelect {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
    }

    .content-container {
        padding: 80px 20px 20px 20px;
        width: 100%;
    }

    body.sidebar-open .content-container {
        margin-left: 0;
        width: 100%;
    }

    /* Simplify the counter styling */
    #selectedCount {
        display: inline-block;
        padding: 8px 12px;
        background-color: #cce5ff;
        border: 1px solid #b8daff;
        border-radius: 4px;
        margin: 0;
        position: static;  /* Force normal document flow */
        width: auto;
    }

    /* Remove any complex positioning from its container */
    .d-flex.justify-content-between.align-items-center {
        padding: 10px 0;
    }

    .search-container {
        display: inline-block;
    }

    .report-action-container {
        margin-top: -20px;
        margin-bottom: 40px;
        padding-left: 20px;
        position: relative;
    }

    .report-action-container .btn-primary {
        padding: 10px 20px;
        font-size: 16px;
        background-color: MediumAquamarine;  /* Matches your system's color scheme */
        border-color: MediumAquamarine;
    }

    /* Add hover state */
    .report-action-container .btn-primary:hover {
        background-color: #5f9ea0;  /* Slightly darker shade for hover */
        border-color: #5f9ea0;
    }

    /* Add this to your existing styles */
    footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 1000;  /* Very high z-index to ensure it's always on top */
        background-color: #fff;  /* Or whatever your footer's background color is */
    }
  </style>
</head>

<body>
    <!-- Remove the duplicate header div since it's already in headeradmin.php -->
    
    <div class="content-container">
        <!-- Update form to submit to adminviewreport.php -->
        <form id="reportForm" method="POST" action="adminviewreport.php">
            <input type="hidden" name="reportType" id="reportTypeInput">
            <!-- Add hidden input for selected members -->
            <input type="hidden" name="selected_members" id="selectedMembersInput">
            
            <h2 style="color: rgb(34, 119, 210);">Hasil Laporan</h2>
            <hr style="border: 1px solid #ddd; margin-top: 10px; margin-bottom: 20px;">
            
            <div class="report-boxes">
                <div class="report-box">
                    <h4>1. Pilih Jenis Laporan</h4>
                    <div class="form-check mt-3">
                        <input class="form-check-input required-field" type="radio" name="reportType" id="ahli" value="ahli" checked>
                        <label class="form-check-label" for="ahli">
                            Ahli
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="reportType" id="pembiayaan" value="pembiayaan">
                        <label class="form-check-label" for="pembiayaan">
                            Pembiayaan
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="reportType" id="monthly" value="monthly">
                        <label class="form-check-label" for="monthly">
                            Bulanan
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="reportType" id="yearly" value="yearly">
                        <label class="form-check-label" for="yearly">
                            Tahunan
                        </label>
                    </div>
                    <div class="invalid-feedback">
                        Sila pilih jenis laporan
                    </div>
                </div>
                <div class="report-box">
                    <h4>2. Pilih Julat Tarikh</h4>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <label class="me-2">Dalam:</label>
                                <select class="form-select btn btn-success" id="dateRangeSelect" style="width: auto; background-color: MediumAquamarine; color: white; border-color: MediumAquamarine;">
                                    <option value="7">Past 7 days</option>
                                    <option value="30" selected>Past 30 days</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <label class="me-2">Dari:</label>
                                <input type="date" class="form-control" id="fromDate">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <label class="me-2">Hingga:</label>
                                <input type="date" class="form-control" id="toDate">
                            </div>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        Sila pilih tarikh
                    </div>
                </div>
                <div class="report-box">
                    <h4>3. Pilih Ahli</h4>
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                        <div class="alert alert-info mb-0" id="selectedCount">
                            Jumlah ahli dipilih: <span>0</span>
                        </div>
                        <div class="search-container">
                            <div class="input-group" style="width: 250px;">
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari ahli...">
                                <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" width="50">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th scope="col">No.</th>
                                    <th scope="col">ID </th>
                                    <th scope="col">Nama</th>
                                    <th scope="col">Tarikh Daftar</th>
                                    <th scope="col">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody id="memberTableBody">
                                <tr id="noSelectionMessage">
                                    <td colspan="6" class="text-center">Sila pilih jenis laporan terlebih dahulu</td>
                                </tr>
                            </tbody>
                        </table>
                        <nav aria-label="Page navigation" class="mt-3">
                            <ul class="pagination justify-content-end" id="pagination">
                                <!-- Pagination will be populated by JavaScript -->
                            </ul>
                        </nav>
                    </div>
                    <div class="invalid-feedback">
                        Sila pilih sekurang-kurangnya seorang ahli
                    </div>
                </div>
            </div>

            <!-- Add the button outside the report-boxes div -->
            <div class="report-action-container">
                <button type="button" class="btn btn-primary" onclick="validateAndSubmit()">Hasil Laporan</button>
            </div>
        </form>
    </div>

    <!-- Update the confirmation modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Pengesahan Hasil Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk menghasilkan laporan ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                    <button type="button" class="btn btn-primary" id="confirmSubmit">Ya</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    let selectedItems = new Set();

    function fetchMembers(page = 1, search = '') {
        const type = document.querySelector('input[name="reportType"]:checked')?.value;
        const fromDate = document.getElementById('fromDate').value;
        const toDate = document.getElementById('toDate').value;

        if (!type) {
            return;
        }

        fetch(`get_report_data.php?page=${page}&search=${search}&type=${type}&fromDate=${fromDate}&toDate=${toDate}&limit=5&status=Diluluskan`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const tbody = document.getElementById('memberTableBody');
                tbody.innerHTML = '';

                if (data.members.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center">Tiada rekod ditemui</td>
                        </tr>
                    `;
                } else {
                    data.members.forEach((member, index) => {
                        const row = document.createElement('tr');
                        const rowNum = ((page - 1) * 5) + index + 1;
                        const memberId = type === 'pembiayaan' ? member.loanApplicationID : member.employeeID;
                        
                        if (type === 'pembiayaan') {
                            row.innerHTML = `
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input member-checkbox" 
                                           name="selected_loans[]" value="${memberId}"
                                           ${selectedItems.has(memberId.toString()) ? 'checked' : ''}>
                                </td>
                                <td>${rowNum}</td>
                                <td>${member.loanApplicationID}</td>
                                <td>${member.memberName}</td>
                                <td>${new Date(member.created_at).toLocaleDateString('en-GB')}</td>
                                <td>
                                    <a href="penyatapermohonanpinjaman.php?id=${member.loanApplicationID}" 
                                       class="btn btn-primary btn-sm">
                                        Lihat
                                    </a>
                                </td>
                            `;
                        } else {
                            row.innerHTML = `
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input member-checkbox" 
                                           name="selected_members[]" value="${memberId}"
                                           ${selectedItems.has(memberId.toString()) ? 'checked' : ''}>
                                </td>
                                <td>${rowNum}</td>
                                <td>${member.employeeID}</td>
                                <td>${member.memberName}</td>
                                <td>${new Date(member.created_at).toLocaleDateString('en-GB')}</td>
                                <td>
                                    <a href="penyatapermohonananggota.php?id=${member.employeeID}" 
                                       class="btn btn-primary btn-sm">
                                        Lihat
                                    </a>
                                </td>
                            `;
                        }
                        tbody.appendChild(row);
                    });
                }

                updatePagination(Math.ceil(data.totalRecords / 5), page);
                attachCheckboxListeners();
                updateSelectAllCheckbox();
                updateSelectedCount();
            })
            .catch(error => {
                console.error('Error:', error);
                const tbody = document.getElementById('memberTableBody');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center">Error loading data: ${error.message}</td>
                    </tr>
                `;
            });
    }

    function updatePagination(totalPages, currentPage) {
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = '';

        // Previous button
        pagination.innerHTML += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
            </li>
        `;

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            pagination.innerHTML += `
                <li class="page-item ${currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        // Next button
        pagination.innerHTML += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
            </li>
        `;

        // Add click listeners to pagination
        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(e.target.dataset.page);
                if (!isNaN(page)) {
                    fetchMembers(page, document.getElementById('searchInput').value);
                }
            });
        });
    }

    // Add this function to format dates in DD/MM/YYYY format
    function formatDate(date) {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${year}-${month}-${day}`; // Format for input[type="date"]
    }

    // Update the date range handler
    document.addEventListener('DOMContentLoaded', function() {
        const dateRangeSelect = document.getElementById('dateRangeSelect');
        const fromDate = document.getElementById('fromDate');
        const toDate = document.getElementById('toDate');

        function updateDateRange(days) {
            const today = new Date();
            const startDate = new Date();
            startDate.setDate(today.getDate() - (days - 1));
            
            fromDate.value = formatDate(startDate);
            toDate.value = formatDate(today);
            
            // Fetch updated data
            fetchMembers(1, document.getElementById('searchInput').value);
        }

        // Handle select change
        dateRangeSelect.addEventListener('change', function() {
            const days = parseInt(this.value);
            updateDateRange(days);
        });

        // Initial update
        updateDateRange(30);
    });

    function attachCheckboxListeners() {
        const selectAll = document.getElementById('selectAll');
        const memberCheckboxes = document.getElementsByClassName('member-checkbox');
        
        selectAll.addEventListener('change', function() {
            Array.from(memberCheckboxes).forEach(checkbox => {
                checkbox.checked = this.checked;
                if (this.checked) {
                    selectedItems.add(checkbox.value);
                } else {
                    selectedItems.delete(checkbox.value);
                }
            });
            updateSelectedCount();
        });

        Array.from(memberCheckboxes).forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    selectedItems.add(this.value);
                } else {
                    selectedItems.delete(this.value);
                }
                updateSelectAllCheckbox();
                updateSelectedCount();
            });
        });
    }

    function updateSelectAllCheckbox() {
        const selectAll = document.getElementById('selectAll');
        const memberCheckboxes = document.getElementsByClassName('member-checkbox');
        const allChecked = Array.from(memberCheckboxes).every(checkbox => checkbox.checked);
        const someChecked = Array.from(memberCheckboxes).some(checkbox => checkbox.checked);
        
        selectAll.checked = allChecked;
        selectAll.indeterminate = someChecked && !allChecked;
    }

    function updateSelectedCount() {
        document.querySelector('#selectedCount span').textContent = selectedItems.size;
    }

    function showLoadingScreen() {
        // Show confirmation modal immediately instead of after delay
        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        confirmationModal.show();
    }

    function validateAndSubmit() {
        if (selectedItems.size === 0) {
            alert('Sila pilih sekurang-kurangnya seorang ahli');
            return;
        }
        
        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        confirmationModal.show();
    }

    // Add event listener for the confirm submit button
    document.getElementById('confirmSubmit').addEventListener('click', function() {
        // Set the report type
        const reportType = document.querySelector('input[name="reportType"]:checked').value;
        document.getElementById('reportTypeInput').value = reportType;
        
        // Clear any existing hidden inputs
        const existingInputs = document.querySelectorAll('input[name="selected_members[]"]');
        existingInputs.forEach(input => input.remove());
        
        // Add hidden inputs for selected items
        selectedItems.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_members[]';
            input.value = id;
            document.getElementById('reportForm').appendChild(input);
        });
        
        // Hide the modal
        const confirmationModal = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
        confirmationModal.hide();
        
        // Submit the form
        document.getElementById('reportForm').submit();
    });

    // Add event listeners to hide validation messages when user makes selections
    document.querySelectorAll('input[name="reportType"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelector('.report-box:nth-child(1) .invalid-feedback').style.display = 'none';
        });
    });

    document.querySelectorAll('#fromDate, #toDate').forEach(input => {
        input.addEventListener('change', () => {
            document.querySelector('.report-box:nth-child(2) .invalid-feedback').style.display = 'none';
        });
    });

    // Add event listeners for report type radio buttons
    document.querySelectorAll('input[name="reportType"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedType = this.value; // 'ahli' or 'pembiayaan'
            updateTable(1, selectedType); // Reset to first page when switching types
        });
    });

    // Update the search functionality
    document.addEventListener('DOMContentLoaded', function() {
        // ... existing code ...

        // Add event listeners for search
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');
        
        // Prevent form submission on Enter key in search input
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault(); // Prevent form submission
                fetchMembers(1, searchInput.value);
            }
        });

        // Search on button click
        searchButton.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent any default button behavior
            fetchMembers(1, searchInput.value);
        });
    });

    // Update the report type change handler
    function handleReportTypeChange() {
        const reportType = document.querySelector('input[name="reportType"]:checked').value;
        
        // Update table headers based on report type
        const thead = document.querySelector('#memberTable thead tr');
        if (reportType === 'pembiayaan') {
            thead.innerHTML = `
                <th class="text-center"><input type="checkbox" id="selectAll"></th>
                <th>No.</th>
                <th>ID</th>
                <th>Nama</th>
                <th>Jumlah Dipinjam</th>
            `;
        } else {
            thead.innerHTML = `
                <th class="text-center"><input type="checkbox" id="selectAll"></th>
                <th>No.</th>
                <th>ID</th>
                <th>Nama</th>
                <th>Tarikh Daftar</th>
            `;
        }

        // Fetch new data based on selected type
        fetchMembers(1, document.getElementById('searchInput').value);

        // Reattach select all functionality
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', handleSelectAll);
        }
    }

    // Add event listener to radio buttons
    document.querySelectorAll('input[name="reportType"]').forEach(radio => {
        radio.addEventListener('change', handleReportTypeChange);
    });

    // Add this new function for handling the view action
    function viewMember(employeeID) {
        // Get the selected report type
        const reportType = document.querySelector('input[name="reportType"]:checked').value;
        
        // Determine which page to redirect to based on report type
        if (reportType === 'pembiayaan') {
            // For loan applications, redirect to loan application form
            window.location.href = `senaraiPermohonanPinjaman.php?id=${employeeID}`;
        } else {
            // For members, redirect to member details form
            window.location.href = `senaraiahli.php?id=${employeeID}`;
        }
    }

    // Add this to clear selections when changing report type
    document.querySelectorAll('input[name="reportType"]').forEach(radio => {
        radio.addEventListener('change', () => {
            selectedItems.clear();
            updateSelectedCount();
            fetchMembers(1, document.getElementById('searchInput').value);
        });
    });
    </script>
</body>
</html>

<?php include 'footer.php';?>