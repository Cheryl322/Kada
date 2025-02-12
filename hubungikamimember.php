<?php

include "headermember.php";
include "footer.php";
?>

<div class="wrapper">
    <div class="container mt-5">
        <h1 class="mb-5">Hubungi Kami</h1>
        
        <div class="row justify-content-center text-center g-4">
            <!-- Address Card -->
            <div class="col-md-4">
                <div class="contact-card">
                    <div class="icon-wrapper mb-3">
                        <img src="img/location-icon.png" alt="Location" class="contact-icon">
                    </div>
                    <h5>ALAMAT</h5>
                    <p class="mb-2">Lembaga Kemajuan Pertanian Kemubu</p>
                    <p>Jalan Dato' Lundang, 15710 Kota Bharu, Kelantan</p>
                </div>
            </div>

            <!-- Phone Card -->
            <div class="col-md-4">
                <div class="contact-card">
                    <div class="icon-wrapper mb-3">
                        <img src="img/phone-icon.png" alt="Phone" class="contact-icon">
                    </div>
                    <h5>TELEFON</h5>
                    <p><a href="tel:+609-744 7088" class="contact-link">+609-744 7088</a></p>
                </div>
            </div>

            <!-- Email Card -->
            <div class="col-md-4">
                <div class="contact-card">
                    <div class="icon-wrapper mb-3">
                        <img src="img/email-icon.png" alt="Email" class="contact-icon">
                    </div>
                    <h5>EMEL</h5>
                    <p><a href="mailto:koperasi_kada@yahoo.com" class="contact-link">koperasi_kada@yahoo.com</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.wrapper {
    min-height: calc(100vh - 60px);
    position: relative;
    background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), url('img/padi.jpg') no-repeat center center fixed;
    background-size: cover;
}

.container {
    position: relative;
    z-index: 1;
    padding-bottom: 60px;
}

.contact-card {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    height: 100%;
    transition: transform 0.3s ease;
}

.contact-card:hover {
    transform: translateY(-5px);
}

.icon-wrapper {
    width: 80px;
    height: 80px;
    margin: 0 auto;
}

.contact-icon {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

h1 {
    color: #5CBA9B;
    font-weight: 600;
}

h5 {
    color: #5CBA9B;
    margin-bottom: 1rem;
}

.contact-link {
    color: #5CBA9B;
    text-decoration: none;
    transition: color 0.3s ease;
}

.contact-link:hover {
    color: #75B798;
}

p {
    color: #666;
    margin-bottom: 0;
}
</style>

<?php
include "footer.php";
?>
