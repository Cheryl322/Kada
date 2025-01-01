-- Create the database
CREATE DATABASE IF NOT EXISTS db_kada;
USE db_kada;

-- Create members table
CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_penuh VARCHAR(255) NOT NULL,
    alamat_emel VARCHAR(255) NOT NULL,
    mykad_passport VARCHAR(20) NOT NULL,
    taraf_perkahwinan VARCHAR(20) NOT NULL,
    alamat_rumah TEXT NOT NULL,
    poskod VARCHAR(10) NOT NULL,
    negeri VARCHAR(50) NOT NULL,
    jantina VARCHAR(10),
    agama VARCHAR(50),
    bangsa VARCHAR(50),
    no_anggota VARCHAR(50),
    no_pf VARCHAR(50),
    jawatan_gred VARCHAR(100),
    alamat_pejabat TEXT,
    no_tel_bimbit VARCHAR(20),
    no_tel_rumah VARCHAR(20),
    gaji_bulanan DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create table for family members/beneficiaries
CREATE TABLE member_waris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT,
    hubungan VARCHAR(50),
    nama VARCHAR(100),
    no_kp VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create table for fees and contributions
CREATE TABLE member_fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT,
    fee_masuk DECIMAL(10,2),
    modal_syer DECIMAL(10,2),
    modal_yuran DECIMAL(10,2),
    wang_deposit DECIMAL(10,2),
    sumbangan_tabung DECIMAL(10,2),
    simpanan_tetap DECIMAL(10,2),
    lain_lain DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;