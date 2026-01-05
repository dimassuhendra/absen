<?php
include "../db_connect.php";

// Cek hak akses
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Ambil setting web
$set = getSetting($koneksi);

// Ambil statistik sederhana
$count_pegawai = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM pegawai"));
$count_jabatan = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM jabatan"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin | <?= $set['nama_perusahaan'] ?></title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --button-color: <?= $set['warna_button'] ?>;
            --font-color: <?= $set['warna_font'] ?>;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <?php include "header.php"; ?>

        <div class="content-body">
            <h2>Ringkasan Data</h2>
            <hr>
            
            <div class="card-container">
                <div class="card">
                    <h3><?= $count_pegawai ?></h3>
                    <p>Total Pegawai</p>
                </div>
                <div class="card">
                    <h3><?= $count_jabatan ?></h3>
                    <p>Total Jabatan</p>
                </div>
                <div class="card">
                    <h3>Aktif</h3>
                    <p>Status Sistem</p>
                </div>
            </div>

            <div style="margin-top: 30px; background: white; padding: 20px; border-radius: 10px;">
                <h3>Panduan Cepat</h3>
                <p>Gunakan menu di samping kiri untuk mengelola data perusahaan Anda. Perubahan warna dan logo dapat dilakukan melalui menu <b>Pengaturan Web</b>.</p>
            </div>
        </div>

        <footer style="margin-top: auto; padding: 20px; text-align: center; background: #eee;">
            <?= $set['footer_text'] ?>
        </footer>
    </div>
</div>

</body>
</html>