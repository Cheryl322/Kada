<?php include "headermain.php"; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* Background styling */
.page-background {
    background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)),
                url('img/padi.jpg') no-repeat center center fixed;
    background-size: cover;
    min-height: 100vh;
    padding: 40px 0;
}

.contact-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.contact-header {
    text-align: center;
    margin-bottom: 50px;
    background: rgba(255, 255, 255, 0.95);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.contact-title {
    color: #2277d2;
    font-size: 2.5rem;
    margin-bottom: 20px;
    font-weight: 600;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.contact-subtitle {
    color: #666;
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    margin-top: 50px;
}

.contact-info {
    background: rgba(255, 255, 255, 0.95);
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: transform 0.3s ease;
}

.contact-info:hover {
    transform: translateY(-5px);
}

.info-title {
    color: #2277d2;
    font-size: 1.5rem;
    margin-bottom: 30px;
    font-weight: 600;
    border-bottom: 2px solid #2277d2;
    padding-bottom: 10px;
}

.info-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 25px;
    padding: 15px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 10px;
    transition: background-color 0.3s ease;
}

.info-item:hover {
    background: rgba(255, 255, 255, 0.8);
}

.info-icon {
    color: #2277d2;
    font-size: 1.5rem;
    margin-right: 15px;
    min-width: 30px;
    transition: transform 0.3s ease;
}

.info-item:hover .info-icon {
    transform: scale(1.1);
}

.info-content {
    color: #444;
}

.info-label {
    font-weight: 600;
    margin-bottom: 5px;
    color: #2277d2;
}

.info-text {
    line-height: 1.5;
}

.contact-map {
    border-radius: 15px;
    overflow: hidden;
    height: 100%;
    min-height: 400px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.contact-map iframe {
    width: 100%;
    height: 100%;
    border: none;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    justify-content: center;
}

.social-link {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #2277d2;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(34, 119, 210, 0.2);
}

.social-link:hover {
    transform: translateY(-3px) rotate(8deg);
    color: white;
    background: #1a5fa8;
}

.operating-hours {
    margin-top: 30px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 10px;
}

.hours-title {
    color: #2277d2;
    font-size: 1.2rem;
    margin-bottom: 15px;
    font-weight: 600;
    text-align: center;
    border-bottom: 2px solid #2277d2;
    padding-bottom: 10px;
}

.hours-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    color: #444;
    padding: 8px 0;
    border-bottom: 1px dashed #ddd;
}

.hours-item:last-child {
    border-bottom: none;
}

@media (max-width: 768px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }

    .contact-title {
        font-size: 2rem;
    }

    .contact-map {
        min-height: 300px;
    }

    .contact-container {
        margin: 20px auto;
    }

    .info-item {
        padding: 10px;
    }

    .social-link {
        width: 40px;
        height: 40px;
    }
}

/* Animation keyframes */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.contact-header,
.contact-info,
.contact-map {
    animation: fadeIn 0.8s ease-out forwards;
}
</style>

<div class="page-background">
    <div class="contact-container">
        <div class="contact-header">
            <h1 class="contact-title">Hubungi Kami</h1>
            <p class="contact-subtitle">Kami sedia membantu anda. Sila hubungi kami melalui maklumat di bawah.</p>
        </div>

        <div class="contact-grid">
            <div class="contact-info">
                <h2 class="info-title">Maklumat Perhubungan</h2>
                
                <div class="info-item">
                    <i class="fas fa-map-marker-alt info-icon"></i>
                    <div class="info-content">
                        <div class="info-label">Alamat</div>
                        <div class="info-text">
                            Koperasi Kakitangan KADA Kelantan Berhad,<br>
                            Tingkat 4, Bangunan KADA,<br>
                            Jalan Sultan Yahya Petra,<br>
                            15200 Kota Bharu, Kelantan
                        </div>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-phone info-icon"></i>
                    <div class="info-content">
                        <div class="info-label">Telefon</div>
                        <div class="info-text">09-7481101</div>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-envelope info-icon"></i>
                    <div class="info-content">
                        <div class="info-label">Email</div>
                        <div class="info-text">koperasi_kada@yahoo.com</div>
                    </div>
                </div>

                <div class="operating-hours">
                    <h3 class="hours-title">Waktu Operasi</h3>
                    <div class="hours-item">
                        <span>Isnin - Khamis</span>
                        <span>8:00 AM - 5:00 PM</span>
                    </div>
                    <div class="hours-item">
                        <span>Jumaat</span>
                        <span>8:00 AM - 12:15 PM, 2:45 PM - 5:00 PM</span>
                    </div>
                    <div class="hours-item">
                        <span>Sabtu - Ahad</span>
                        <span>Tutup</span>
                    </div>
                </div>

                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <div class="contact-map">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.0374764667305!2d102.23843007454847!3d6.133333027532403!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31b6af8a78c3ce39%3A0x66b9491f3bd8453c!2sLembaga%20Kemajuan%20Pertanian%20Kemubu%20(KADA)!5e0!3m2!1sen!2smy!4v1709704716899!5m2!1sen!2smy"
                    allowfullscreen="" 
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
