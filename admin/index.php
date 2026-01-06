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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin |
        <?= $set['nama_perusahaan'] ?>
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />

    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --button-color: <?= $set['warna_button'] ?>;
            --font-color: <?= $set['warna_font'] ?>;
            --bg-light: #f4f7fa;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            display: flex;
        }

        /* Menyesuaikan dengan Sidebar melayang sebelumnya */
        .main-content {
            margin-left: 320px;
            /* Jarak untuk sidebar */
            padding: 30px;
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Welcome Section */
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color), #2c3e50);
            border-radius: 20px;
            padding: 40px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .welcome-banner h2 {
            font-weight: 700;
            margin-bottom: 10px;
        }

        .welcome-banner p {
            opacity: 0.8;
            font-weight: 300;
        }

        /* Stats Card */
        .stat-card {
            background: white;
            border: none;
            border-radius: 20px;
            padding: 25px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.02);
            display: flex;
            align-items: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 20px;
        }

        .icon-pegawai {
            background: #e3f2fd;
            color: #1e88e5;
        }

        .icon-jabatan {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .icon-status {
            background: #fff3e0;
            color: #ef6c00;
        }

        .stat-info h3 {
            font-weight: 700;
            margin: 0;
            color: #2d3436;
        }

        .stat-info p {
            margin: 0;
            color: #636e72;
            font-size: 0.9rem;
        }

        /* Content Box */
        .guide-box {
            background: white;
            border-radius: 20px;
            padding: 30px;
            border-left: 5px solid var(--primary-color);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.02);
        }

        footer {
            margin-top: auto;
            padding: 25px;
            color: #b2bec3;
            font-size: 0.85rem;
            text-align: center;
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0">Overview</h5>
                <small class="text-muted">Pantau data perusahaan Anda di sini</small>
            </div>
            <div class="text-end">
                <span class="badge bg-white text-dark shadow-sm p-2 px-3 rounded-pill">
                    <i class="fa-solid fa-calendar-day me-2 text-primary"></i>
                    <?= date('d M Y') ?>
                </span>
            </div>
        </div>

        <div class="welcome-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>Halo, Admin! 👋</h2>
                    <p>Selamat datang kembali di panel kendali
                        <?= $set['nama_perusahaan'] ?>. <br>Semua sistem berjalan dengan optimal hari ini.
                    </p>
                </div>
                <div class="col-md-4 text-end d-none d-md-block">
                    <i class="fa-solid fa-chart-line fa-5x opacity-25"></i>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-pegawai">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <div class="stat-info">
                        <h3>
                            <?= $count_pegawai ?>
                        </h3>
                        <p>Total Pegawai</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-jabatan">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div class="stat-info">
                        <h3>
                            <?= $count_jabatan ?>
                        </h3>
                        <p>Total Jabatan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-status">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Aktif</h3>
                        <p>Status Sistem</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="guide-box mb-4">
            <div class="d-flex align-items-center mb-3">
                <i class="fa-solid fa-lightbulb text-warning me-3 fs-4"></i>
                <h5 class="mb-0 fw-bold">Panduan Cepat Pengelolaan</h5>
            </div>
            <p class="text-muted mb-0">
                Gunakan navigasi di sisi kiri untuk mengakses data. Anda dapat memperbarui
                <span class="badge bg-light text-dark">Data Jabatan</span>, menambahkan
                <span class="badge bg-light text-dark">Pegawai Baru</span>, atau menyesuaikan identitas visual
                perusahaan melalui
                <span class="badge bg-light text-dark">Pengaturan Web</span>.
            </p>
        </div>

        <footer>
            &copy;
            <?= date('Y') ?> <b>
                <?= $set['nama_perusahaan'] ?>
            </b>.
            <?= $set['footer_text'] ?>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>