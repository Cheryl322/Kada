<!DOCTYPE html>
<html lang="en">

<head>
  <title>Sistem Koperasi KADA</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="css/bootstrap.min (1).css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
.footer {
   position: fixed;
   left: 0;
   bottom: 0;
   width: 100%;
   background-color: MediumAquamarine;
   color: white;
   text-align: center;
}

.navbar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 250px;
    background-color: rgb(34, 119, 210);
    padding-top: 20px;
    margin-top: 60px;
    transform: translateX(-250px);
    transition: transform 0.3s ease-in-out;
    z-index: 998;
}

.navbar.closed {
    transform: translateX(-250px);
}

.navbar:not(.closed) {
    transform: translateX(0);
}

.navbar.initial-state {
    transition: none !important;
}

.container-fluid {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.navbar-nav {
    flex-direction: column;
    width: 100%;
}
.nav-item {
    width: 100%;
}
.nav-link {
    color: white;
    padding: 10px 15px;
}

#sidebarToggle {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1000;
    display: none;
}

.navbar.closed + #sidebarToggle {
    display: block;
}

#closeSidebar {
    transition: transform 0.3s ease;
}

#closeSidebar:hover {
    transform: scale(1.2);
}

.main-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 60px;
    background-color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    z-index: 999;
}

.menu-button {
    background: none;
    border: none;
    font-size: 24px;
    color: rgb(34, 119, 210);
    cursor: pointer;
    padding: 10px;
    transition: transform 0.3s ease;
}

.menu-button:hover {
    transform: scale(1.1);
}

.top-right-icons {
    display: flex;
    gap: 20px;
    align-items: center;
}

.icon-button {
    color: rgb(34, 119, 210);
    font-size: 24px;
    cursor: pointer;
    transition: transform 0.3s ease;
    position: relative;
}

.icon-button:hover {
    transform: scale(1.1);
}

.profile-pic {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgb(34, 119, 210);
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: red;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
}

/* Add these new styles */
.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.home-button {
    color: rgb(34, 119, 210);
    font-size: 24px;
    text-decoration: none;
    transition: transform 0.3s ease;
}

.home-button:hover {
    transform: scale(1.1);
}

/* Add this new style */
.navbar.initial-state {
    transform: translateX(-250px);
}

/* Add these new styles for when sidebar is open */
.sidebar-open .circle-container {
    width: calc(100% - 250px);
    margin-left: 250px;
}

.sidebar-open .tables-container {
    margin-left: 250px;
    max-width: calc(100% - 270px);
}

/* Add these new styles */
.content-wrapper {
    transition: margin-left 0.3s ease-in-out;
    width: calc(100% - 40px);
    position: relative;
}

.sidebar-open .content-wrapper {
    margin-left: 270px;
    width: calc(100% - 290px);
}

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

/* Add these styles for sidebar-aware content shifting */
.content-container {
    transition: all 0.3s ease-in-out;
    margin-left: 0;
    width: 100%;
    padding: 80px 20px 20px;  /* Add top padding to account for header */
}

/* Style for when sidebar is open */
.sidebar-open .content-container {
    margin-left: 250px;
    width: calc(100% - 250px);
}

/* Ensure any tables or content areas are responsive */
.table-responsive {
    width: 100%;
    overflow-x: auto;
}

/* If you have any specific content wrappers, make them responsive too */
.report-section {
    width: 100%;
    margin-bottom: 20px;
}

/* Add these new styles */
.invalid-feedback {
    display: none;
    color: #dc3545;
    margin-top: 0.25rem;
}

.required-field.is-invalid {
    border-color: #dc3545;
}
</style>
</head>

<body>

<div class="main-header">
    <div class="header-left">
        <button class="menu-button" id="menuButton">
            <i class="fas fa-bars"></i>
        </button>
        <a href="adminmainpage.php" class="home-button">
            <i class="fas fa-home"></i>
        </a>
    </div>
    <div class="top-right-icons">
        <div class="icon-button">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="icon-button">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">3</span>
        </div>
        <div class="icon-button">
            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Profile" class="profile-pic">
        </div>
    </div>
</div>

<div class="content-container">
    <!-- Update form to submit to adminviewreport.php -->
    <form id="reportForm" method="POST" action="adminviewreport.php">
        <input type="hidden" name="reportType" id="reportTypeInput">
        <!-- Add hidden input for selected members -->
        <input type="hidden" name="selected_members" id="selectedMembersInput">
        
        <h2>Hasil Laporan</h2>
        <hr style="margin-top: 10px; margin-bottom: 20px;">
        
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
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dateRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    Past 7 days
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dateRangeDropdown">
                                    <li><a class="dropdown-item active" href="#">Past 7 days</a></li>
                                    <li><a class="dropdown-item" href="#">Past 30 days</a></li>
                                </ul>
                            </div>
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
            <div class="report-box">
                <h4>4. Hasil Laporan</h4>
                <div class="form-check mt-3">
                    <input class="form-check-input required-field" type="radio" name="reportFormat" id="pdf" value="pdf">
                    <label class="form-check-label" for="pdf">
                        PDF
                    </label>
                </div>
                <div class="invalid-feedback">
                    Sila pilih format laporan
                </div>

                <button type="button" class="btn btn-primary mt-4" onclick="validateAndSubmit()">Hasil Laporan</button>
            </div>
        </div>
    </form>
</div>

<div class="navbar initial-state closed" id="sidebar">
  <div class="container-fluid">
    <div style="display: flex; width: 100%; align-items: center; margin-bottom: 20px;">
      <i class="fas fa-arrow-left" id="closeSidebar" style="cursor:pointer; font-size: 24px; color: white; position: absolute; left: 20px; top: 20px;"></i>
      <a class="navbar-brand" href="index.php" style="margin: 0 auto;">
        <img src="img/kadalogo.jpg" alt="logo" height="60">
      </a>
    </div>
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link active" href="adminmainpage.php">Laman Utama
          <span class="visually-hidden">(current)</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="senaraiahli.php">Ahli Semasa</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="senaraipembiayaan.php">Permohonan Pinjaman</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="hasilreport.php">Hasil Laporan</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="adminviewreport.php">Cek Laporan</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="login.php">Log Keluar</a>
      </li>
    </ul>
  </div>
</div>

<!-- Add this new modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Laporan Berjaya Disiapkan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Report anda telah berjaya disiapkan. Adakah anda mahu cek/muat turn report ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                <button type="button" class="btn btn-primary">Ya</button>
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

    fetch(`get_report_data.php?page=${page}&search=${search}&type=${type}&fromDate=${fromDate}&toDate=${toDate}&limit=5`)
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

// Add this function to automatically update dates
function updateDateRange() {
    const today = new Date();
    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(today.getDate() - 6);
    
    const fromDate = document.getElementById('fromDate');
    const toDate = document.getElementById('toDate');
    
    fromDate.value = formatDate(sevenDaysAgo);
    toDate.value = formatDate(today);
}

// Add event listeners when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initial update of dates
    updateDateRange();

    // Update dates every day at midnight
    setInterval(() => {
        const now = new Date();
        if (now.getHours() === 0 && now.getMinutes() === 0) {
            updateDateRange();
        }
    }, 60000); // Check every minute

    // Trigger initial data fetch with default values
    fetchMembers(1, document.getElementById('searchInput').value);

    // Report type selection listener
    document.querySelectorAll('input[name="reportType"]').forEach(radio => {
        radio.addEventListener('change', () => {
            fetchMembers(1, document.getElementById('searchInput').value);
        });
    });

    // Search input listener
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    
    searchButton.addEventListener('click', () => {
        fetchMembers(1, searchInput.value);
    });

    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            fetchMembers(1, searchInput.value);
        }
    });

    // Date range dropdown handler
    const dateRangeDropdown = document.getElementById('dateRangeDropdown');
    const fromDate = document.getElementById('fromDate');
    const toDate = document.getElementById('toDate');

    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const range = this.textContent;
            dateRangeDropdown.textContent = range;

            const today = new Date();
            let startDate = new Date();

            switch(range) {
                case 'Past 7 days':
                    startDate.setDate(today.getDate() - 6);
                    break;
                case 'Past 30 days':
                    startDate.setDate(today.getDate() - 29);
                    break;
            }

            fromDate.value = startDate.toISOString().split('T')[0];
            toDate.value = today.toISOString().split('T')[0];

            // Fetch updated data with date range
            fetchMembers(1, document.getElementById('searchInput').value);
        });
    });

    // Date input handlers
    fromDate.addEventListener('change', () => updateTableWithDateRange());
    toDate.addEventListener('change', () => updateTableWithDateRange());
});

function updateTableWithDateRange() {
    fetchMembers(1, document.getElementById('searchInput').value);
}

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

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const menuButton = document.getElementById('menuButton');
    const closeSidebar = document.getElementById('closeSidebar');
    const mainContent = document.body;

    // Remove initial-state class after a brief delay
    setTimeout(() => {
        sidebar.classList.remove('initial-state');
    }, 100);

    function toggleSidebar() {
        sidebar.classList.toggle('closed');
        mainContent.classList.toggle('sidebar-open');
    }

    menuButton.addEventListener('click', toggleSidebar);
    closeSidebar.addEventListener('click', toggleSidebar);

    // Add pagination click handlers
    document.querySelectorAll('.pagination .page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const text = this.textContent;
            const selectedType = document.querySelector('input[name="reportType"]:checked')?.value || 'ahli';
            
            if (text === 'Previous') {
                const activePage = parseInt(document.querySelector('.pagination .active').textContent);
                if (activePage > 1) {
                    updateTable(activePage - 1, selectedType);
                }
            } else if (text === 'Next') {
                const activePage = parseInt(document.querySelector('.pagination .active').textContent);
                if (activePage < 3) {
                    updateTable(activePage + 1, selectedType);
                }
            } else {
                updateTable(parseInt(text), selectedType);
            }
        });
    });

    // Initialize table with empty state
    updateTable(1, null);
});

document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.getElementsByClassName('member-checkbox');
    for(let checkbox of checkboxes) {
        checkbox.checked = this.checked;
    }
    updateSelectedCount();
});

// Add event listener for individual checkboxes
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('member-checkbox')) {
        updateSelectedCount();
    }
});

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
    
    const reportType = document.querySelector('input[name="reportType"]:checked').value;
    document.getElementById('reportTypeInput').value = reportType;
    
    const reportFormat = document.querySelector('input[name="reportFormat"]:checked');
    if (!reportFormat) {
        alert('Sila pilih format laporan');
        return;
    }
    
    // Debug output
    console.log("Selected items:", Array.from(selectedItems));
    console.log("Report type:", document.querySelector('input[name="reportType"]:checked').value);
    
    // Clear any existing hidden inputs
    const existingInputs = document.querySelectorAll('input[name="selected_members[]"]');
    existingInputs.forEach(input => input.remove());
    
    // Add hidden inputs for selected items
    selectedItems.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_members[]';
        input.value = id;
        console.log("Adding input with value:", id);
        document.getElementById('reportForm').appendChild(input);
    });
    
    document.getElementById('reportForm').submit();
}

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

// Update the modal button to use validateAndSubmit instead
document.querySelector('#confirmationModal .btn-primary').onclick = validateAndSubmit;

// Add this to clear selections when changing report type
document.querySelectorAll('input[name="reportType"]').forEach(radio => {
    radio.addEventListener('change', () => {
        selectedItems.clear();
        updateSelectedCount();
        fetchMembers(1, document.getElementById('searchInput').value);
    });
});
</script>

<?php include 'footer.php';?>

