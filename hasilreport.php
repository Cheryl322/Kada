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

#loadingScreen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

#loadingScreen .spinner-border {
    width: 4rem;
    height: 4rem;


}
</style>
</head>

<body>

<div id="loadingScreen" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>



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
    <h2>Hasil Report</h2>
    <hr style="margin-top: 10px; margin-bottom: 20px;">
    
    <div class="report-boxes">
        <div class="report-box">
            <h4>1. Pilih Jenis Laporan</h4>
            <div class="form-check mt-3">
                <input class="form-check-input" type="radio" name="reportType" id="ahli" value="ahli">
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
        </div>
        <div class="report-box">
            <h4>2. Pilih Julat Tarikh</h4>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex align-items-center">
                        <label class="me-2">Dalam:</label>
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dateRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Select Range
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dateRangeDropdown">
                                <li><a class="dropdown-item" href="#">Past 7 days</a></li>
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
                            <th scope="col">Nama</th>
                            <th scope="col">Status</th>
                            <th scope="col">Tarikh Daftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input member-checkbox" name="selected_members[]" value="1">
                            </td>
                            <td>1</td>
                            <td>Ahmad bin Abdullah</td>
                            <td>Aktif</td>
                            <td>01/03/2024</td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input member-checkbox" name="selected_members[]" value="2">
                            </td>
                            <td>2</td>
                            <td>Siti binti Rahman</td>
                            <td>Aktif</td>
                            <td>05/03/2024</td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input member-checkbox" name="selected_members[]" value="3">
                            </td>
                            <td>3</td>
                            <td>Mohamed bin Ismail</td>
                            <td>Aktif</td>
                            <td>08/03/2024</td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input member-checkbox" name="selected_members[]" value="4">
                            </td>
                            <td>4</td>
                            <td>Nurul binti Hassan</td>
                            <td>Aktif</td>
                            <td>10/03/2024</td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input member-checkbox" name="selected_members[]" value="5">
                            </td>
                            <td>5</td>
                            <td>Kamal bin Zain</td>
                            <td>Aktif</td>
                            <td>12/03/2024</td>
                        </tr>
                    </tbody>
                </table>
                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination justify-content-end">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="report-box">
            <h4>4. Hasil Report</h4>
            <div class="form-check mt-3">
                <input class="form-check-input" type="radio" name="reportFormat" id="pdf" value="pdf">
                <label class="form-check-label" for="pdf">
                    PDF
                </label>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="radio" name="reportFormat" id="excel" value="excel">
                <label class="form-check-label" for="excel">
                    Excel
                </label>
            </div>

            <button type="button" class="btn btn-primary" onclick="showLoadingScreen()">Hasil Report</button>
        </div>
    </div>
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
        <a class="nav-link" href="#">Ahli Semasa</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Pendaftaran Ahli</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Permohonan Pinjaman</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="hasilreport.php">Hasil Laporan</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="adminviewreport.php">Cek Laporan</a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="#">Info KADA</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Media</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Hubungi Kami</a>
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
                <button type="button" class="btn btn-primary" onclick="downloadReport()">Ya</button>
            </div>
        </div>
    </div>

</div>

<script>
const memberData = {
    1: [
        { id: 1, name: 'Ahmad bin Abdullah', status: 'Aktif', date: '01/03/2024' },
        { id: 2, name: 'Siti binti Rahman', status: 'Aktif', date: '05/03/2024' },
        { id: 3, name: 'Mohamed bin Ismail', status: 'Aktif', date: '08/03/2024' },
        { id: 4, name: 'Nurul binti Hassan', status: 'Aktif', date: '10/03/2024' },
        { id: 5, name: 'Kamal bin Zain', status: 'Aktif', date: '12/03/2024' }
    ],
    2: [
        { id: 6, name: 'Sarah binti Ali', status: 'Aktif', date: '15/03/2024' },
        { id: 7, name: 'Razak bin Omar', status: 'Aktif', date: '18/03/2024' },
        { id: 8, name: 'Farah binti Karim', status: 'Aktif', date: '20/03/2024' },
        { id: 9, name: 'Hassan bin Ahmad', status: 'Aktif', date: '22/03/2024' },
        { id: 10, name: 'Aminah binti Yusof', status: 'Aktif', date: '25/03/2024' }
    ],
    3: [
        { id: 11, name: 'Zainab binti Mahmud', status: 'Aktif', date: '27/03/2024' },
        { id: 12, name: 'Ismail bin Hashim', status: 'Aktif', date: '29/03/2024' },
        { id: 13, name: 'Fatimah binti Abdul', status: 'Aktif', date: '01/04/2024' },
        { id: 14, name: 'Aziz bin Rahman', status: 'Aktif', date: '03/04/2024' },
        { id: 15, name: 'Noraini binti Said', status: 'Aktif', date: '05/04/2024' }
    ]
};

function updateTable(pageNumber) {
    const tbody = document.querySelector('table tbody');
    const members = memberData[pageNumber];
    
    // Clear existing rows
    tbody.innerHTML = '';
    
    // Add new rows
    members.forEach(member => {
        tbody.innerHTML += `
            <tr>
                <td class="text-center">
                    <input type="checkbox" class="form-check-input member-checkbox" name="selected_members[]" value="${member.id}">
                </td>
                <td>${member.id}</td>
                <td>${member.name}</td>
                <td>${member.status}</td>
                <td>${member.date}</td>
            </tr>
        `;
    });

    // Update active page in pagination
    document.querySelectorAll('.pagination .page-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`.pagination .page-item:nth-child(${pageNumber + 1})`).classList.add('active');

    // Update Previous/Next buttons
    const prevButton = document.querySelector('.pagination .page-item:first-child');
    const nextButton = document.querySelector('.pagination .page-item:last-child');
    
    prevButton.classList.toggle('disabled', pageNumber === 1);
    nextButton.classList.toggle('disabled', pageNumber === 3);

    // Reset select all checkbox
    document.getElementById('selectAll').checked = false;

    // After updating the table content, add event listeners to new checkboxes
    document.querySelectorAll('.member-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Reset counter when changing pages
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkedBoxes = document.querySelectorAll('.member-checkbox:checked').length;
    document.querySelector('#selectedCount span').textContent = checkedBoxes;
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
            
            if (text === 'Previous') {
                const activePage = parseInt(document.querySelector('.pagination .active').textContent);
                if (activePage > 1) {
                    updateTable(activePage - 1);
                }
            } else if (text === 'Next') {
                const activePage = parseInt(document.querySelector('.pagination .active').textContent);
                if (activePage < 3) {
                    updateTable(activePage + 1);
                }
            } else {
                updateTable(parseInt(text));
            }
        });
    });
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
    document.getElementById('loadingScreen').style.display = 'flex';
    
    // Simulate processing time
    setTimeout(() => {
        document.getElementById('loadingScreen').style.display = 'none';
        // Show confirmation modal
        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        confirmationModal.show();
    }, 2000);
}

function downloadReport() {
    // Redirect to adminviewreport.php
    window.location.href = 'adminviewreport.php';

}
</script>

<?php include 'footer.php';?>

