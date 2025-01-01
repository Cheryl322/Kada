<?php
session_start();
include "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get personal info from session
        $personal_info = $_SESSION['personal_info'];
        
        // Start transaction
        mysqli_begin_transaction($con);

        // Insert personal info
        $sql = "INSERT INTO members (nama_penuh, no_kp, alamat, poskod, negeri, no_tel) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", 
            $personal_info['nama_penuh'],
            $personal_info['no_kp'],
            $personal_info['alamat'],
            $personal_info['poskod'],
            $personal_info['negeri'],
            $personal_info['no_tel']
        );
        mysqli_stmt_execute($stmt);

        // Insert family members
        $member_id = mysqli_insert_id($con);
        foreach ($_POST['hubungan'] as $key => $hubungan) {
            if (!empty($hubungan) && !empty($_POST['nama_waris'][$key])) {
                $sql = "INSERT INTO member_waris (member_id, hubungan, nama, no_kp) 
                        VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($con, $sql);
                mysqli_stmt_bind_param($stmt, "isss", 
                    $member_id,
                    $_POST['hubungan'][$key],
                    $_POST['nama_waris'][$key],
                    $_POST['no_kp_waris'][$key]
                );
                mysqli_stmt_execute($stmt);
            }
        }

        // Insert fees
        $sql = "INSERT INTO member_fees (member_id, fee_masuk, modal_syer, modal_yuran, 
                wang_deposit, sumbangan_tabung, simpanan_tetap, lain_lain) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "iddddddd", 
            $member_id,
            $_POST['fee_masuk'],
            $_POST['modal_syer'],
            $_POST['modal_yuran'],
            $_POST['wang_deposit'],
            $_POST['sumbangan_tabung'],
            $_POST['simpanan_tetap'],
            $_POST['lain_lain']
        );
        mysqli_stmt_execute($stmt);

        // Commit transaction
        mysqli_commit($con);
        
        // Clear session and redirect
        unset($_SESSION['personal_info']);
        $_SESSION['success_message'] = "Pendaftaran berjaya!";
        header("Location: success.php");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($con);
        $_SESSION['error_message'] = "Ralat semasa pendaftaran: " . $e->getMessage();
        header("Location: maklumat_tambahan.php");
        exit();
    }
}
?> 