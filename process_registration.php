<?php
session_start();
include "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get personal info from session
        $personal_info = $_SESSION['personal_info'];
        
        // Start transaction
        if (!$conn) {
            throw new Exception("Database connection failed");
        }
        
        mysqli_begin_transaction($conn);

        // First insert the member data (your existing member insertion code)
        $sql = "INSERT INTO tb_member (employeeId, memberName, email, ic, maritalStatus, 
                sex, religion, nation, no_pf, position, phoneNumber, phoneHome, monthlySalary) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }

        // Convert monthlySalary to float
        $monthlySalary = floatval($personal_info['monthlySalary']);
        
        mysqli_stmt_bind_param($stmt, "ssssssssssssd", 
            $personal_info['no_anggota'],      // employeeId
            $personal_info['nama_penuh'],      // memberName
            $personal_info['alamat_emel'],     // email
            $personal_info['ic'],              // ic
            $personal_info['maritalStatus'],   // maritalStatus
            $personal_info['sex'],             // sex
            $personal_info['religion'],        // religion
            $personal_info['nation'],          // nation
            $personal_info['no_pf'],           // no_pf
            $personal_info['position'],        // position
            $personal_info['phoneNumber'],     // phoneNumber
            $personal_info['phoneHome'],       // phoneHome
            $monthlySalary                     // monthlySalary
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to save member: " . mysqli_stmt_error($stmt));
        }

        $employeeId = $personal_info['no_anggota'];

        // Save family members
        if (isset($personal_info['family_members']) && is_array($personal_info['family_members'])) {
            $sql = "INSERT INTO tb_memberregistration_familymemberinfo 
                    (memberRegistrationID, icFamilyMember, relationship, name) 
                    VALUES (?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Prepare failed for family members: " . mysqli_error($conn));
            }

            foreach ($personal_info['family_members'] as $family) {
                mysqli_stmt_bind_param($stmt, "ssss", 
                    $employeeId,
                    $family['ic'],           // IC number
                    $family['relationship'], // Relationship
                    $family['name']         // Name
                );
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to save family member: " . mysqli_stmt_error($stmt));
                }
            }
        }

        // Save fees and contributions
        if (isset($personal_info['fees'])) {
            $sql = "INSERT INTO tb_memberregistration_feesandcontribution 
                    (memberRegistrationID, entryFee, modalShare, feeCapital, 
                    deposit, contribution, fixedDeposit, others) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Prepare failed for fees: " . mysqli_error($conn));
            }

            $fees = $personal_info['fees'];
            mysqli_stmt_bind_param($stmt, "siiiiii", 
                $employeeId,
                $fees['entryFee'],
                $fees['modalShare'],
                $fees['feeCapital'],
                $fees['deposit'],
                $fees['contribution'],
                $fees['fixedDeposit'],
                $fees['others']
            );
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to save fees: " . mysqli_stmt_error($stmt));
            }
        }

        // Commit transaction
        mysqli_commit($conn);
        
        // Clear session and redirect
        unset($_SESSION['personal_info']);
        $_SESSION['success_message'] = "Pendaftaran berjaya!";
        header("Location: success.php");
        exit();

    } catch (Exception $e) {
        if (isset($conn)) {
            mysqli_rollback($conn);
        }
        $_SESSION['error_message'] = "Ralat semasa pendaftaran: " . $e->getMessage();
        header("Location: maklumat_tambahan.php");
        exit();
    }
}
?> 