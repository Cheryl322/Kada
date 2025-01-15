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
    transition: all 0.3s ease-in-out;
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
    transition: all 0.3s ease-in-out;
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
    transition: all 0.3s ease-in-out;
}

body {
    transition: all 0.3s ease-in-out;
}

/* Add these new styles for the profile dropdown */
.profile-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 5px;
    display: none;
    z-index: 1000;
    min-width: 120px;
}

.profile-dropdown.show {
    display: block;
}

.profile-dropdown a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    color: #333;
    text-decoration: none;
    transition: background-color 0.3s ease;
    font-size: 0.9rem;
}

.profile-dropdown a:hover {
    background-color: #f5f5f5;
    border-radius: 4px;
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
        <div class="icon-button" style="position: relative;">
            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Profile" class="profile-pic" id="profileButton">
            <div class="profile-dropdown" id="profileDropdown">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
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
        <a class="nav-link" href="#">Info KADA</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Media</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Hubungi Kami</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php">Log Out</a>
      </li>
    </ul>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const menuButton = document.getElementById('menuButton');
    const closeSidebar = document.getElementById('closeSidebar');
    const mainContent = document.body;
    const profileButton = document.getElementById('profileButton');
    const profileDropdown = document.getElementById('profileDropdown');

    // Remove initial-state class after a brief delay
    setTimeout(() => {
        sidebar.classList.remove('initial-state');
    }, 100);

    function toggleSidebar() {
        sidebar.classList.toggle('closed');
        mainContent.classList.toggle('sidebar-open');
    }

    // Toggle profile dropdown
    profileButton.addEventListener('click', function(e) {
        e.stopPropagation();
        profileDropdown.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!profileButton.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.remove('show');
        }
    });

    menuButton.addEventListener('click', toggleSidebar);
    closeSidebar.addEventListener('click', toggleSidebar);
});
</script>
