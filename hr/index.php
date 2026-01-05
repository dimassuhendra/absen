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

// 1. Total Pegawai Aktif
$jml_pegawai = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_pegawai FROM pegawai WHERE role != 'admin'"));

// 2. Pegawai Hadir Hari Ini
$hadir_hari_ini = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_absensi FROM absensi WHERE tanggal = '$tgl_sekarang'"));

// 3. Pengajuan Cuti Menunggu Persetujuan (Status Pending)
$cuti_pending = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_cuti FROM pengajuan_cuti WHERE status = 'Pending'"));

// 4. Daftar Pegawai yang Absen Hari Ini (Untuk Tabel Ringkasan)
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
    <title>Dashboard HR |
        <?= $set['nama_perusahaan'] ?>
    </title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --button-color: <?= $set['warna_button'] ?>;
            --font-color: <?= $set['warna_font'] ?>;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .card h3 {
            font-size: 2.5rem;
            margin: 10px 0;
            color: var(--primary-color);
        }

        .card p {
            color: #888;
            font-weight: bold;
            margin: 0;
        }

        .alert-box {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-action {
            background: var(--button-color);
            color: var(--font-color);
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include "sidebar.php"; ?>

        <div class="main-content">
            <?php include "../admin/header.php"; ?>

            <div class="content-body">
                <div style="margin-bottom: 30px;">
                    <h1>Panel Operasional HR</h1>
                    <p>Monitor kehadiran dan manajemen SDM <strong>
                            <?= $set['nama_perusahaan'] ?>
                        </strong>.</p>
                </div>

                <?php if ($cuti_pending > 0): ?>
                    <div class="alert-box">
                        <span>📢 Ada <b>
                                <?= $cuti_pending ?>
                            </b> pengajuan cuti/izin baru yang memerlukan persetujuan Anda.</span>
                        <a href="konfirmasi_cuti.php" class="btn-action">Proses Sekarang</a>
                    </div>
                <?php endif; ?>

                <div class="stats-container">
                    <div class="card">
                        <p>TOTAL PEGAWAI</p>
                        <h3>
                            <?= $jml_pegawai ?>
                        </h3>
                    </div>
                    <div class="card">
                        <p>HADIR HARI INI</p>
                        <h3>
                            <?= $hadir_hari_ini ?>
                        </h3>
                    </div>
                    <div class="card">
                        <p>PENGAJUAN CUTI</p>
                        <h3>
                            <?= $cuti_pending ?>
                        </h3>
                    </div>
                </div>

                <div class="card" style="padding: 20px;">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="font-size: 1.2rem; color: #333;">Aktivitas Absensi Terkini</h3>
                        <a href="absensi.php" style="font-size: 13px; color: var(--primary-color);">Lihat Semua Rekap
                            →</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Pegawai</th>
                                <th>Jabatan</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($res_absen) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($res_absen)): ?>
                                    <tr>
                                        <td><b>
                                                <?= $row['nama_lengkap'] ?>
                                            </b></td>
                                        <td>
                                            <?= $row['nama_jabatan'] ?>
                                        </td>
                                        <td align="center">
                                            <?= $row['jam_masuk'] ?>
                                        </td>
                                        <td align="center">
                                            <?= ($row['jam_keluar'] == '00:00:00') ? '-' : $row['jam_keluar'] ?>
                                        </td>
                                        <td align="center"><span style="color: green;">✔ Hadir</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" align="center">Belum ada aktivitas absensi hari ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>