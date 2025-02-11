<?php
session_start();
if (!isset($_SESSION['status'])) {
    header("Location: permohonanloan.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Status Permohonan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if($_SESSION['status'] == "success"){ ?>
            Swal.fire({
                icon: 'success',
                title: 'Berjaya!',
                text: '<?php echo $_SESSION['message']; ?>',
                confirmButtonText: 'Ke Status Permohonan',
                confirmButtonColor: '#75B798',
                allowOutsideClick: false
            }).then((result) => {
                window.location.href = 'statuspermohonanloan.php';
            });

        <?php } else { ?>
            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: '<?php echo $_SESSION['error']; ?>',
                confirmButtonText: 'Kembali',
                confirmButtonColor: '#75B798',
                allowOutsideClick: false
            }).then((result) => {
                // Go back to previous page instead of redirecting
                window.history.back();
            });
        <?php } ?>

    });
    </script>

    <?php
    // Clear session messages
    unset($_SESSION['status']);
    unset($_SESSION['message']);
    unset($_SESSION['error']);
    ?>
</body>
</html> 