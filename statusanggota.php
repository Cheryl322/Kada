<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['employeeID'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Include database connection
include 'dbconnect.php';
include 'headermember.php';

// Get the member's registration ID from session
$memberID = $_SESSION['employeeID'];

// Get registration status
$sql = "SELECT regisStatus, regisDate 
        FROM tb_memberregistration_memberapplicationdetails 
        WHERE memberRegistrationID = ?
        ORDER BY regisDate DESC LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $memberID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Status Permohonan Anggota</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)),
                        src('img/padi.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .content-wrapper {
            flex: 1 0 auto;
            padding: 20px 0;
        }
        .main-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .page-title {
            color: #2c3e50;
            font-size: 2rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 40px;
        }
        .status-container {
            padding: 40px;
            background: white;
            border-radius: 15px;
            text-align: center;
        }
        .status-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            transition: all 0.3s ease;
        }
        .status-circle.processing {
            background-color: #ffeeba;
            color: #856404;
            box-shadow: 0 0 15px rgba(255, 193, 7, 0.3);
        }
        .status-circle.approved {
            background-color: #d4edda;
            color: #155724;
            box-shadow: 0 0 15px rgba(40, 167, 69, 0.3);
        }
        .status-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 20px 0;
            color: #2c3e50;
        }
        .status-date {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        .btn-back {
            background-color: #5CBA9B;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        .btn-back:hover {
            background-color: #4a9d82;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(92, 186, 155, 0.3);
            color: white;
        }
        .status-info {
            background-color: rgba(248, 249, 250, 0.9);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        footer {
            flex-shrink: 0;
            background-color: #333;
            color: white;
            padding: 20px 0;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="main-container">
            <h1 class="page-title">Status Permohonan Anggota</h1>
            
            <div class="status-container">
                <?php
                if ($row && $row['regisStatus'] == 'Belum Selesai') {
                    ?>
                    <div class="status-circle processing">
                        <i class="fas fa-clock fa-4x"></i>
                    </div>
                    <h3 class="status-title">Permohonan Ahli Sedang Diproses</h3>
                    <div class="status-date">
                        <i class="far fa-calendar-alt me-2"></i>
                        Tarikh Permohonan: <?php echo date('d/m/Y', strtotime($row['regisDate'])); ?>
                    </div>
                    <div class="status-info">
                        <p class="mb-0">Permohonan anda sedang diproses oleh pihak pengurusan. 
                        Sila tunggu sementara permohonan anda disemak.</p>
                    </div>
                    <?php
                } else if ($row && $row['regisStatus'] == 'Diluluskan') {
                    ?>
                    <div class="status-circle approved">
                        <i class="fas fa-check fa-4x"></i>
                    </div>
                    <h3 class="status-title">Permohonan Diluluskan</h3>
                    <div class="status-date">
                        <i class="far fa-calendar-alt me-2"></i>
                        Tarikh Kelulusan: <?php echo date('d/m/Y', strtotime($row['regisDate'])); ?>
                    </div>
                    <div class="status-info">
                        <p class="mb-0">Tahniah! Permohonan anda telah diluluskan. 
                        Anda kini adalah ahli rasmi KADA.</p>
                    </div>
                    <?php
                }
                ?>
            </div>

            <div class="text-center mt-4">
                <a href="status.php" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
