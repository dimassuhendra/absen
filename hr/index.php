<?php
include "../db_connect.php";

// Proteksi Halaman: Hanya HR yang bisa masuk
if ($_SESSION['role'] != 'hr') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);
$tgl_sekarang = date('Y-m-d');

// --- QUERY STATISTIK HR ---
$jml_pegawai = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_pegawai FROM pegawai WHERE role = 'pegawai'"));
$hadir_hari_ini = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_absensi FROM absensi WHERE tanggal = '$tgl_sekarang'"));
$cuti_pending = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_cuti FROM pengajuan_cuti WHERE status = 'Pending'"));

// Daftar Pegawai yang Absen Hari Ini
$query_absen = "SELECT a.*, p.nama_lengkap, j.nama_jabatan 
                FROM absensi a 
                JOIN pegawai p ON a.id_pegawai = p.id_pegawai 
                JOIN jabatan j ON p.id_jabatan = j.id_jabatan 
                WHERE a.tanggal = '$tgl_sekarang' 
                ORDER BY a.jam_masuk DESC LIMIT 5";
$res_absen = mysqli_query($koneksi, $query_absen);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HR | <?= $set['nama_perusahaan'] ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --accent-color: <?= $set['warna_button'] ?>;
            --font-on-accent: <?= $set['warna_font'] ?>;
        }

        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .main-content { margin-left: 280px; padding: 40px; min-height: 100vh; }

        /* Header Styling */
        .page-header { margin-bottom: 30px; }
        .page-header h1 { font-weight: 700; color: #2d3436; font-size: 24px; margin-bottom: 5px; }
        .page-header p { color: #636e72; font-size: 14px; }

        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card {
            background: white; padding: 25px; border-radius: 20px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 10px 20px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.05);
        }
        .stat-info p { color: #b2bec3; font-size: 12px; font-weight: 600; text-transform: uppercase; margin: 0; }
        .stat-info h3 { font-size: 28px; font-weight: 700; color: #2d3436; margin: 5px 0 0; }
        .stat-icon {
            width: 60px; height: 60px; border-radius: 15px;
            display: flex; align-items: center; justify-content: center; font-size: 24px;
        }

        /* Colors for icons */
        .icon-blue { background: #e3f2fd; color: #1976d2; }
        .icon-green { background: #e8f5e9; color: #2e7d32; }
        .icon-yellow { background: #fffde7; color: #fbc02d; }

        /* Alert Box for Cuti */
        .alert-cuti {
            background: #fff4e5; border: 1px solid #ffe2c2; border-radius: 15px;
            padding: 20px; margin-bottom: 30px; display: flex; align-items: center;
            justify-content: space-between; animation: pulse-border 2s infinite;
        }
        @keyframes pulse-border {
            0% { box-shadow: 0 0 0 0 rgba(255, 165, 0, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(255, 165, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 165, 0, 0); }
        }

        /* Table Card */
        .table-card {
            background: white; border-radius: 20px; padding: 25px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.02);
        }
        .table thead th {
            background: #f8f9fa; border: none; padding: 15px;
            font-size: 12px; font-weight: 600; text-transform: uppercase; color: #636e72;
        }
        .table tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f2f6; }
        .badge-presence { background: #e8f5e9; color: #2e7d32; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; }
        
        .btn-view-all {
            color: var(--accent-color); text-decoration: none; font-size: 13px; font-weight: 600;
        }
    </style>
</head>

<body>

    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="page-header">
            <h1>Panel Operasional HR</h1>
            <p>Selamat datang kembali! Berikut ringkasan SDM <strong><?= $set['nama_perusahaan'] ?></strong> hari ini.</p>
        </div>

        <?php if ($cuti_pending > 0): ?>
            <div class="alert-cuti">
                <div class="d-flex align-items: center;">
                    <i class="fa-solid fa-bell-exclamation fa-2x text-warning me-3"></i>
                    <div>
                        <h6 class="mb-1 fw-bold">Persetujuan Cuti Diperlukan</h6>
                        <p class="mb-0 text-muted small">Ada <strong><?= $cuti_pending ?> pengajuan</strong> baru yang menunggu respon Anda.</p>
                    </div>
                </div>
                <a href="konfirmasi_cuti.php" class="btn btn-warning btn-sm fw-bold rounded-3">Proses Sekarang</a>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <p>Total Pegawai</p>
                    <h3><?= $jml_pegawai ?></h3>
                </div>
                <div class="stat-icon icon-blue">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <p>Kehadiran Hari Ini</p>
                    <h3><?= $hadir_hari_ini ?></h3>
                </div>
                <div class="stat-icon icon-green">
                    <i class="fa-solid fa-user-check"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <p>Cuti Menunggu</p>
                    <h3><?= $cuti_pending ?></h3>
                </div>
                <div class="stat-icon icon-yellow">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Aktivitas Absensi Terkini</h5>
                <a href="absensi.php" class="btn-view-all">Lihat Semua Rekap <i class="fa-solid fa-arrow-right ms-1"></i></a>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pegawai</th>
                            <th>Jabatan</th>
                            <th class="text-center">Jam Masuk</th>
                            <th class="text-center">Jam Keluar</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($res_absen) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($res_absen)): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-weight: 700; color: var(--primary-color); font-size: 12px;">
                                                <?= strtoupper(substr($row['nama_lengkap'], 0, 1)) ?>
                                            </div>
                                            <span class="fw-semibold text-dark"><?= $row['nama_lengkap'] ?></span>
                                        </div>
                                    </td>
                                    <td><span class="text-muted"><?= $row['nama_jabatan'] ?></span></td>
                                    <td class="text-center fw-bold text-dark"><?= date('H:i', strtotime($row['jam_masuk'])) ?></td>
                                    <td class="text-center">
                                        <?= ($row['jam_keluar'] == '00:00:00' || !$row['jam_keluar']) ? '<span class="text-muted opacity-50">--:--</span>' : '<span class="fw-bold text-dark">'.date('H:i', strtotime($row['jam_keluar'])).'</span>' ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-presence"><i class="fa-solid fa-circle-check me-1"></i> Hadir</span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted small">Belum ada aktivitas kehadiran terekam untuk hari ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>