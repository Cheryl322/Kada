<?php
session_start();
include"headermember.php";
include "footer.php";
?>

<div class="container mt-5">
    <div class="row">
        <!-- Left Sidebar -->
        <div class="col-md-3">
            <div class="profile-sidebar">
                <div class="profile-image">
                    <img src="img/profile.jpeg" class="rounded-circle" alt="Profile Picture">
                    <h3 class="text-left mt-3">Yuna Liew</h3>
                </div>

                <!-- Navigation Menu-->
                <div class="profile-nav">
                    <ul class="nav flex-column gap-2">
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="profil.php">Profil</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="statuskewangan.php">Pinjaman</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-primary w-75" href="statuspermohonanloan.php">Permohonan</a>
                        </li>
                        <li class="nav-item w-100">
                            <a class="btn btn-info w-75" href="logout.php">Daftar Keluar</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <h5>Status permohonan pembiayaan</h5>
            
            <!-- Status Steps -->
            <div class="status-container mt-4">
                <div class="status-steps">
                    <div class="step">
                        <div class="circle">1</div>
                        <div class="label">Permohonan serahkan</div>
                    </div>
                    <div class="step">
                        <div class="circle">2</div>
                        <div class="label">Permohonan diteliti oleh pengurusan lembaga</div>
                    </div>
                    <div class="step">
                        <div class="circle">3</div>
                        <div class="label">Permohonan lulus / gagal</div>
                    </div>
                    <div class="step">
                        <div class="circle">4</div>
                        <div class="label">Keputusan pembentangan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.status-container {
    padding: 20px;
}

.status-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-bottom: 40px;
}

.status-steps::before {
    content: '';
    position: absolute;
    top: 25px;
    left: 0;
    right: 0;
    height: 2px;
    background: #4CAF50;
    z-index: 1;
}

.step {
    text-align: center;
    position: relative;
    z-index: 2;
    width: 120px;
}

.circle {
    width: 50px;
    height: 50px;
    background-color: #4CAF50;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
    font-weight: bold;
    font-size: 18px;
}

.label {
    margin-top: 10px;
    font-size: 12px;
    color: #333;
}

/* For Ditolak status */
.step.failed .circle {
    background-color: #ff0000;
}

.step.pending .circle {
    background-color: #808080;
}

/* Add some spacing between steps */
.step:not(:last-child) {
    margin-right: 20px;
}
</style>

<script>
// Function to update status based on application status from database
function updateApplicationStatus(status) {
    const steps = document.querySelectorAll('.step');
    const statusLine = document.querySelector('.status-steps::before');
    
    if (status === 'Diluluskan') {
        steps.forEach(step => {
            step.querySelector('.circle').style.backgroundColor = '#4CAF50';
        });
        statusLine.style.backgroundColor = '#4CAF50';
    } else if (status === 'Ditolak') {
        steps.forEach((step, index) => {
            const circle = step.querySelector('.circle');
            if (index < 2) {
                circle.style.backgroundColor = '#4CAF50';
            } else if (index === 2) {
                circle.style.backgroundColor = '#ff0000';
            } else {
                circle.style.backgroundColor = '#808080';
            }
        });
        
        // Update line gradient
        statusLine.style.background = 'linear-gradient(to right, ' +
            '#4CAF50 0%, ' +
            '#4CAF50 50%, ' +
            '#ff0000 50%, ' +
            '#808080 75%, ' +
            '#808080 100%)';
    }
}

// Call this when page loads with the current application status
document.addEventListener('DOMContentLoaded', function() {
    // You would typically get this from your PHP/database
    const currentStatus = 'Diluluskan'; // or 'Ditolak'
    updateApplicationStatus(currentStatus);
});
</script>