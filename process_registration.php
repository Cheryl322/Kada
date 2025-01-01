<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get personal info from session
        $personal_info = $_SESSION['personal_info'];
        
        // Start transaction
        mysqli_begin_transaction($con);

        // Insert personal info - Updated to match your table structure
        $sql = "INSERT INTO members (
            nama_penuh, 
            alamat_emel, 
            mykad_passport, 
            taraf_perkahwinan, 
            alamat_rumah, 
            poskod, 
            negeri,
            jantina,
            agama,
            bangsa,
            no_anggota,
            no_pf,
            jawatan_gred,
            alamat_pejabat,
            no_tel_bimbit,
            no_tel_rumah,
            gaji_bulanan
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssssssssssd", 
            $personal_info['nama_penuh'],
            $personal_info['alamat_emel'],
            $personal_info['mykad_passport'],
            $personal_info['taraf_perkahwinan'],
            $personal_info['alamat_rumah'],
            $personal_info['poskod'],
            $personal_info['negeri'],
            $personal_info['jantina'],
            $personal_info['agama'],
            $personal_info['bangsa'],
            $personal_info['no_anggota'],
            $personal_info['no_pf'],
            $personal_info['jawatan_gred'],
            $personal_info['alamat_pejabat'],
            $personal_info['no_tel_bimbit'],
            $personal_info['no_tel_rumah'],
            $personal_info['gaji_bulanan']
        );
        mysqli_stmt_execute($stmt);

        // Get the inserted member's ID
        $member_id = mysqli_insert_id($con);

        // Insert family members
        foreach ($_POST['hubungan'] as $key => $hubungan) {
            if (!empty($hubungan) && !empty($_POST['nama_waris'][$key])) {
                $sql = "INSERT INTO member_waris (member_id, hubungan, nama, no_kp) 
                        VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($con, $sql);
                mysqli_stmt_bind_param($stmt, "isss", 
                    $member_id,
                    $hubungan,
                    $_POST['nama_waris'][$key],
                    $_POST['no_kp_waris'][$key]
                );
                mysqli_stmt_execute($stmt);
            }
        }

        // Insert fees
        $sql = "INSERT INTO member_fees (
            member_id, 
            fee_masuk, 
            modal_syer, 
            modal_yuran,
            wang_deposit,
            sumbangan_tabung,
            simpanan_tetap,
            lain_lain
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
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