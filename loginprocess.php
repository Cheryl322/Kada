<?php

include "headermember.php";
include "footer.php";

?>

<style>
.main-container {
    padding: 40px 0;
}

body {
    background: url('img/padi.jpg') no-repeat center center fixed;
    background-size: cover;
    position: relative;
    font-family: 'Poppins', sans-serif;
    color: #2C3E50;
}


body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(rgba(245, 245, 245, 0.85), rgba(240, 240, 240, 0.8));
    z-index: -1;
}



.content-card {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    margin-bottom: 30px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(200,200,200,0.2);
}

.company-logo {
    max-width: 200px;
    margin: 0 auto 30px;
    display: block;
    transition: transform 0.3s ease;
}

.company-logo:hover {
    transform: scale(1.05);
}

.company-title {
    color: #1a4971;
    text-align: center;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 40px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    letter-spacing: 1px;
}

.info-section {
    margin-bottom: 35px;
    animation: fadeIn 0.5s ease-out;
}

.info-label {
    color: #1a4971;
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
}

.info-label i {
    margin-right: 10px;
    color: #2980b9;
}

.info-content {
    background: #f4f6f8;
    padding: 20px;
    border-radius: 12px;
    border-left: 5px solid #2980b9;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.info-content:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.bank-list {
    list-style: none;
    padding: 0;
}

.bank-item {
    background: #f4f6f8;
    padding: 20px;
    margin-bottom: 15px;
    border-radius: 12px;
    border-left: 5px solid #2c5282;
    transition: transform 0.3s ease;
    display: flex;
    align-items: center;
}

.bank-item i {
    margin-right: 15px;
    color: #2c5282;
    font-size: 1.2rem;
}

.bank-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.committee-section {
    background: #f4f6f8;
    padding: 30px;
    border-radius: 15px;
    margin-top: 40px;
}

.section-title {
    color: #1a4971;
    text-align: center;
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 30px;
    position: relative;
    padding-bottom: 15px;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 3px;
    background: linear-gradient(to right, #2980b9, #2c5282);
    border-radius: 2px;
}

.committee-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 20px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.committee-table th,
.committee-table td {
    padding: 15px 20px;
    text-align: left;
    border: none;
    border-bottom: 1px solid #eee;
}

.committee-table th {
    background: #2c5282;
    color: white;
    font-weight: 500;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 1px;
}

.committee-table tr:last-child td {
    border-bottom: none;
}

.committee-table tr:nth-child(even) {
    background-color: #f8fafc;
}

.committee-table tr:hover {
    background-color: #edf2f7;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive adjustments */
@media screen and (max-width: 768px) {
    .content-card {
        padding: 20px;
    }
    
    .company-title {
        font-size: 2rem;
    }
    
    .committee-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>

<!-- Add Font Awesome and Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<div class="container main-container">
    <div class="content-card">
        <img src="img/kadalogo.jpg" alt="KADA Logo" class="company-logo">
        <h1 class="company-title">KOPERASI KAKITANGAN KADA</h1>

        <div class="info-section">
            <div class="info-label"><i class="fas fa-building"></i> NAMA BERDAFTAR</div>
            <div class="info-content">KOPERASI KAKITANGAN KADA SDN BHD</div>
        </div>

        <div class="info-section">
            <div class="info-label"><i class="fas fa-registered"></i> NO. PENDAFTARAN</div>
            <div class="info-content">IP5429/1</div>
        </div>

        <div class="info-section">
            <div class="info-label"><i class="fas fa-calendar-alt"></i> TARIKH DAFTAR</div>
            <div class="info-content">29 Ogos 1981</div>
        </div>

        <div class="info-section">
            <div class="info-label"><i class="fas fa-university"></i> PEJABAT BERDAFTAR</div>
            <div class="info-content">D/A Lembaga Kemajuan Pertanian Kemubu, P/S 127, 15710 Kota Bharu, Kelantan.</div>
        </div>

        <div class="info-section">
            <div class="info-label"><i class="fas fa-phone"></i> NO. TELEFON</div>
            <div class="info-content">09-7447088 samb. 5339 @ 5312</div>
        </div>

        <div class="info-section">
            <div class="info-label"><i class="fas fa-envelope"></i> EMEL</div>
            <div class="info-content">koperasi_kada@yahoo.com</div>
        </div>

        <div class="info-section">
            <div class="info-label"><i class="fas fa-landmark"></i> BANK</div>
            <ul class="bank-list">
                <li class="bank-item">
                    <i class="fas fa-landmark"></i>
                    BANK ISLAM MALAYSIA BHD - CAWANGAN KUBANG KERIAN
                </li>
                <li class="bank-item">BANK MUAMALAT MALAYSIA BERHAD - CAWANGAN JALAN SULTAN YAHYA PETRA</li>
                <li class="bank-item">BANK MUAMALAT MALAYSIA BERHAD - CAWANGAN KOTA BHARU</li>
            </ul>
        </div>

        <div class="info-section">
            <h2 class="text-center mb-4">SENARAI AHLI JAWATANKUASA KOPERASI BAGI TAHUN 2015 HINGGA 2019</h2>
            <table class="committee-table">
                <thead>
                    <tr>
                        <th>JAWATAN</th>
                        <th>NAMA AHLI</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>PENGERUSI</td><td>Wan Badri b. Wan Omar</td></tr>
                    <tr><td>TIMBALAN PENGERUSI</td><td>Shazlan b. Sekari@Shokri</td></tr>
                    <tr><td>SETIAUSAHA</td><td>Mohamed Azami b. Mohamed Salleh</td></tr>
                    <tr><td>TIMBALAN SETIAUSAHA</td><td>Zariani bt. Hussin</td></tr>
                    <tr><td>BENDAHARI</td><td>Mohd Badli Shah b. Che Mohamad</td></tr>
                    <tr><td>TIMBALAN BENDAHARI</td><td>Ab. Aziz b. Mustapha</td></tr>
                    <tr><td>PENGERUSI PERNIAGAAN & PELABURAN</td><td>Shazlan b. Sekari@Shokri</td></tr>
                    <tr><td>PENGERUSI KEBAJIKAN & SYARIE</td><td>Engku Safrudin b. Engku Chik</td></tr>
                    <tr><td>TIMB. PENGERUSI KEBAJIKAN & SYARIE</td><td>Wan Nur Hasyila bt. Wan Suleman</td></tr>
                    <tr><td>PENGERUSI PENTADBIRAN ICT & DISIPLIN</td><td>Mohammad Hazwan b. Mohamad</td></tr>
                    <tr><td>TIMB. PENGERUSI PENTADBIRAN ICT & DISIPLIN</td><td>Siti Salwani bt. Mustapha</td></tr>
                    <tr><td>PENGERUSI PERNIAGAAN & PELABURAN</td><td>Shazlan b. Sekari@Shokri</td></tr>
                    <tr><td>TIMB. PENGERUSI PERNIAGAAN & PELABURAN I</td><td>Wan Mahidin b. Wan Shafie</td></tr>
                    <tr><td>TIMB. PENGERUSI PERNIAGAAN & PELABURAN II</td><td>Mohd Zalimin b. Husin</td></tr>
                    <tr><td>AUDIT LUAR (1)</td><td>Khairuddin Hasyudeen & Razi</td></tr>
                    <tr><td>AUDIT LUAR (2)</td><td>Chartered Accountants (AF1161)</td></tr>
                    <tr><td>AUDIT DALAM (1)</td><td>Zulfikri Bin Mohamad</td></tr>
                    <tr><td>AUDIT DALAM (2)</td><td>Nor Salwana bt. Zaini</td></tr>
                    <tr><td>AUDIT DALAM (3)</td><td>Wan Shafini bt. Wan Muhamad</td></tr>
                    <tr><td>KAKITANGAN (1)</td><td>Ahmad Rohailan b. Hani</td></tr>
                    <tr><td>KAKITANGAN (2)</td><td>Noor Zafran bt. Ahmad Kamal</td></tr>
                    <tr><td>KAKITANGAN (3)</td><td>Wan Shafini bt. Wan Muhamad</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Add smooth scroll animation
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Add animation when elements come into view
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = 1;
            entry.target.style.transform = 'translateY(0)';
        }
    });
});

document.querySelectorAll('.info-section').forEach(section => {
    section.style.opacity = 0;
    section.style.transform = 'translateY(20px)';
    observer.observe(section);
});
</script>