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

/* Add these table styles */
.tables-container {
    display: flex;
    gap: 30px;
    padding: 20px;
    margin-left: 0;
    margin-top: -50px;
    justify-content: space-between;
    max-width: 100%;
    flex-wrap: nowrap;
}

.table-wrapper {
    flex: 1;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    min-width: 0;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.table-header h3 {
    color: rgb(34, 119, 210);
    margin: 0;
}

.custom-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.custom-table th, 
.custom-table td {
    padding: 12px;
    text-align: left;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.custom-table th {
    background-color: MediumAquamarine;
    color: white;
}

.custom-table td {
    border-bottom: 1px solid #ddd;
}

/* Ensure consistent column widths across both tables */
.custom-table th:nth-child(1),
.custom-table td:nth-child(1) {
    width: 20%;
}

.custom-table th:nth-child(2),
.custom-table td:nth-child(2) {
    width: 35%;
}

.custom-table th:nth-child(3),
.custom-table td:nth-child(3) {
    width: 20%;
}

.custom-table th:nth-child(4),
.custom-table td:nth-child(4) {
    width: 25%;
}

.see-more-link {
    color: rgb(34, 119, 210);
    text-decoration: none;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.see-more-link:hover {
    color: MediumAquamarine;
}

/* Add responsive styles */
@media screen and (max-width: 1400px) {
    .tables-container {
        flex-direction: row;
        margin-top: 0;
        flex-wrap: nowrap;
        padding-top: 0;
    }

    .sidebar-open .tables-container {
        width: calc(100% - 270px);
        margin-left: 250px;
    }
}

@media screen and (max-width: 1000px) {
    .tables-container {
        flex-direction: column;
    }
    
    .table-wrapper {
        flex: 1;
        width: 100%;
        max-width: 100%;
    }
}

.tables-container.sidebar-closed {
    margin-left: 0;
    max-width: 100%;
}

/* Add these styles */
.circle-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 8px;
    max-width: 100%;
}

.circle i {
    font-size: 1.8rem;
    margin-bottom: 3px;
}

.circle span {
    display: block;
    line-height: 1.1;
    font-size: 0.9em;
    word-wrap: break-word;
    max-width: 100%;
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
.sidebar-open .tables-container,
.sidebar-open .content-wrapper {
    margin-left: 250px;
    width: calc(100% - 270px);
}

/* Header styles */
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

/* Add these for proper sidebar behavior with content */
.sidebar-open .circle-container,
.sidebar-open .tables-container,
.sidebar-open .content-wrapper {
    margin-left: 250px;
    width: calc(100% - 270px);
}

body {
    /* Remove this line */
    /* transition: all 0.3s ease-in-out; */
}

/* Update these dropdown styles */
.dropdown-menu {
    padding: 8px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.dropdown-item {
    padding: 8px 20px;
    color: #333;
    transition: background-color 0.3s;
}

.dropdown-item:hover {
    background-color: transparent; /* Remove white background on hover */
    color: #333; /* Keep text color consistent */
}

.dropdown-item i {
    color: #666;
    width: 20px;
}

/* Make profile pic clickable */
.icon-button {
    cursor: pointer;
}

/* Add hover effect to profile pic */
.profile-pic:hover {
    opacity: 0.8;
    transition: opacity 0.3s;
}

.header {
    position: fixed;
    top: 0;
    right: 0;
    padding: 10px 20px;
    z-index: 10000;
    pointer-events: auto; /* Ensure clicks are registered */
}

.profile-section {
    position: relative;
    cursor: pointer;
    z-index: 10001;
}

.profile-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.logout-dropdown {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 5px;
    padding: 8px 0;
    margin-top: 5px;
    z-index: 10002;
    min-width: 150px;
}

.logout-dropdown.show {
    display: block;
}

.logout-link {
    display: flex;
    align-items: center;
    padding: 8px 20px;
    color: #dc3545;
    text-decoration: none;
    white-space: nowrap;
}

.logout-link:hover {
    background-color: #f8f9fa;
    color: #c82333;
    text-decoration: none;
}

.logout-link i {
    margin-right: 8px;
}

.header-admin {
    position: fixed;
    top: 0;
    right: 0;
    padding: 10px 20px;
    z-index: 999999;
}

.profile-container {
    position: relative;
    cursor: pointer;
}

.profile-image {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.logout-menu {
    display: none;
    position: absolute;
    top: 45px;
    right: 0;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 5px;
    padding: 8px 0;
    min-width: 150px;
}

.logout-menu.active {
    display: block;
}

.logout-option {
    display: flex;
    align-items: center;
    padding: 8px 20px;
    color: #dc3545;
    text-decoration: none;
    transition: background-color 0.2s;
}

.logout-option:hover {
    background-color: #f8f9fa;
    color: #c82333;
    text-decoration: none;
}

.logout-option i {
    margin-right: 8px;
}

#headerAdmin {
    position: fixed;
    top: 0;
    right: 0;
    padding: 10px 20px;
    z-index: 999999;
}

#profileButton {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#logoutContainer {
    display: none;
    position: absolute;
    top: 50px;
    right: 20px;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 5px;
    min-width: 150px;
}

#logoutLink {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    color: #dc3545;
    text-decoration: none;
    font-size: 14px;
}

#logoutLink i {
    margin-right: 8px;
}

#logoutLink:hover {
    background-color: #f8f9fa;
}
</style>
</head>

<body>

<div class="main-header">
    <div class="header-left">
        <!-- <button class="menu-button" id="menuButton">
            <i class="fas fa-bars"></i>
        </button> -->
        <a href="adminmainpage.php" class="home-button">
            <i class="fas fa-home"></i>
        </a>
    </div>
    
</div>

<!-- <div class="navbar initial-state closed" id="sidebar">
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
        <a class="nav-link" href="senaraiPermohonanAhli.php">Ahli Semasa</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="senaraiPermohonanPinjaman.php">Permohonan Pinjaman</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="hasilreport.php">Hasil Laporan</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="adminviewreport.php">Cek Laporan</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="admin_upload_payment.php">Rekod Bayaran</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="manage_member_status.php">Pengurusan Status Anggota</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php">Log Keluar</a>
      </li>
    </ul>
  </div>
</div> -->

<div id="simpleHeader" style="position: fixed; top: 10px; right: 20px; z-index: 100000;">
    <div style="position: relative;">
        <img 
            src="img/admin.jpg" 
            alt="Profile" 
            style="width: 40px; height: 40px; border-radius: 50%; cursor: pointer; border: 2px solid #fff; object-fit: cover;" 
            onclick="toggleMenu()"
        >
        <div id="logoutMenu" style="display: none; position: absolute; top: 45px; right: 0; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 5px; min-width: 150px;">
            <a href="logout.php" style="display: flex; align-items: center; padding: 10px 15px; color: #dc3545; text-decoration: none;">
                <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> Log Keluar
            </a>
        </div>
    </div>
</div>

<script>
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

    // Make sure this script runs after the DOM is loaded
    window.toggleLogout = function() {
        const dropdown = document.getElementById('logoutDropdown');
        if (dropdown) {
            dropdown.classList.toggle('show');
        }
    };

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const profile = document.querySelector('.profile-section');
        const dropdown = document.getElementById('logoutDropdown');
        
        if (profile && dropdown && !profile.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });
});

function toggleMenu() {
    const menu = document.getElementById('logoutMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

// Close menu when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('logoutMenu');
    const header = document.getElementById('simpleHeader');
    if (!header.contains(e.target)) {
        menu.style.display = 'none';
    }
});
</script>
</body>
</html>