<?php
include "../db_connect.php";

// Cek hak akses HR
if ($_SESSION['role'] != 'hr') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// Logika Filter Tanggal (Default: Hari Ini)
$tgl_filter = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Ambil data absensi
$query = "SELECT a.*, p.nama_lengkap, p.nip, j.nama_jabatan 
          FROM absensi a 
          JOIN pegawai p ON a.id_pegawai = p.id_pegawai 
          JOIN jabatan j ON p.id_jabatan = j.id_jabatan 
          WHERE a.tanggal = '$tgl_filter' 
          ORDER BY a.jam_masuk DESC";

$data_absensi = mysqli_query($koneksi, $query);
$count_data = mysqli_num_rows($data_absensi);

// Hitung Ringkasan untuk Hari Tersebut
$di_kantor = 0;
$sudah_pulang = 0;
mysqli_data_seek($data_absensi, 0); // Reset pointer untuk menghitung
while ($c = mysqli_fetch_assoc($data_absensi)) {
    if ($c['jam_keluar'] == '00:00:00' || $c['jam_keluar'] == NULL) $di_kantor++;
    else $sudah_pulang++;
}
mysqli_data_seek($data_absensi, 0); // Reset pointer kembali ke awal untuk tabel
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi | <?= $set['nama_perusahaan'] ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --primary: <?= $set['warna_header'] ?>;
            --accent: <?= $set['warna_button'] ?>;
            --font-accent: <?= $set['warna_font'] ?>;
        }

        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; margin: 0; }
        .main-content { margin-left: 280px; padding: 40px; min-height: 100vh; }

        /* Filter Section */
        .header-flex { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
        .filter-card { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; }
        
        .input-group { display: flex; flex-direction: column; gap: 5px; }
        .input-group label { font-size: 11px; font-weight: 700; color: #b2bec3; text-transform: uppercase; }
        .input-date { padding: 10px; border: 1px solid #dfe6e9; border-radius: 8px; font-family: inherit; color: #2d3436; outline: none; }
        
        .btn-search { background: var(--accent); color: var(--font-accent); border: none; padding: 11px 25px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; margin-top: auto; }
        .btn-search:hover { filter: brightness(90%); }

        /* Summary Chips */
        .summary-wrapper { display: flex; gap: 15px; margin-bottom: 25px; }
        .chip { background: white; padding: 12px 20px; border-radius: 12px; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid #edf2f7; }
        .chip i { font-size: 18px; }
        .chip-text { font-size: 13px; font-weight: 500; color: #636e72; }
        .chip-val { font-weight: 700; color: #2d3436; }

        /* Table Styling */
        .table-container { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #fdfdfd; padding: 18px; text-align: left; font-size: 12px; font-weight: 700; color: #b2bec3; text-transform: uppercase; border-bottom: 2px solid #f1f2f6; }
        tbody td { padding: 18px; border-bottom: 1px solid #f1f2f6; font-size: 14px; color: #2d3436; }
        
        /* Status Badges */
        .badge { padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; }
        .badge-home { background: #fff4e5; color: #f39c12; } /* Sedang di kantor */
        .badge-done { background: #e8f5e9; color: #27ae60; } /* Sudah pulang */

        .nip-tag { background: #f1f2f6; padding: 3px 8px; border-radius: 4px; font-family: monospace; font-size: 12px; color: #636e72; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #b2bec3; }
        .empty-state i { font-size: 50px; margin-bottom: 15px; opacity: 0.3; }
    </style>
</head>
<body>

    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <?php include "../admin/header.php"; ?>

        <div class="header-flex">
            <div>
                <h2 style="font-weight: 700; margin: 0;">Rekap Kehadiran</h2>
                <p style="color: #636e72; font-size: 14px; margin-top: 5px;">
                    Menampilkan data tanggal: <strong><?= date('d F Y', strtotime($tgl_filter)) ?></strong>
                </p>
            </div>
            
            <form method="GET" class="filter-card">
                <div class="input-group">
                    <label>Filter Tanggal</label>
                    <input type="date" name="tanggal" class="input-date" value="<?= $tgl_filter ?>">
                </div>
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
                <?php if($tgl_filter != date('Y-m-d')): ?>
                    <a href="absensi.php" style="font-size: 12px; color: var(--primary); text-decoration: none; font-weight: 600;">Reset ke Hari Ini</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="summary-wrapper">
            <div class="chip">
                <i class="fa-solid fa-users" style="color: #3498db;"></i>
                <span class="chip-text">Total Hadir: <span class="chip-val"><?= $count_data ?></span></span>
            </div>
            <div class="chip">
                <i class="fa-solid fa-building-user" style="color: #f39c12;"></i>
                <span class="chip-text">Masih di Kantor: <span class="chip-val"><?= $di_kantor ?></span></span>
            </div>
            <div class="chip">
                <i class="fa-solid fa-house-chimney-user" style="color: #27ae60;"></i>
                <span class="chip-text">Sudah Pulang: <span class="chip-val"><?= $sudah_pulang ?></span></span>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>NIP & Pegawai</th>
                        <th>Jabatan</th>
                        <th style="text-align: center;">Jam Masuk</th>
                        <th style="text-align: center;">Jam Keluar</th>
                        <th style="text-align: center;">Durasi Kerja</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($count_data > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($data_absensi)): ?>
                            <tr>
                                <td>
                                    <span class="nip-tag"><?= $row['nip'] ?></span><br>
                                    <div style="font-weight: 600; margin-top: 5px;"><?= $row['nama_lengkap'] ?></div>
                                </td>
                                <td style="color: #636e72;"><?= $row['nama_jabatan'] ?></td>
                                <td align="center" style="font-weight: 600; color: #2d3436;">
                                    <i class="fa-regular fa-clock text-success me-1" style="font-size: 12px;"></i> 
                                    <?= date('H:i', strtotime($row['jam_masuk'])) ?>
                                </td>
                                <td align="center">
                                    <?php if ($row['jam_keluar'] == '00:00:00' || $row['jam_keluar'] == NULL): ?>
                                        <span style="color: #fab1a0; font-style: italic; font-size: 13px;">-- : --</span>
                                    <?php else: ?>
                                        <span style="font-weight: 600; color: #2d3436;">
                                            <i class="fa-regular fa-clock text-danger me-1" style="font-size: 12px;"></i>
                                            <?= date('H:i', strtotime($row['jam_keluar'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td align="center">
                                    <?php 
                                        if($row['jam_keluar'] != '00:00:00' && $row['jam_keluar'] != NULL){
                                            $awal  = strtotime($row['jam_masuk']);
                                            $akhir = strtotime($row['jam_keluar']);
                                            $diff  = $akhir - $awal;
                                            $jam   = floor($diff / (60 * 60));
                                            $menit = $diff - $jam * (60 * 60);
                                            echo "<strong style='color:#0984e3'>".$jam."j ".floor($menit/60)."m</strong>";
                                        } else {
                                            echo "<small style='color:#b2bec3'>Menghitung...</small>";
                                        }
                                    ?>
                                </td>
                                <td align="center">
                                    <?php if ($row['jam_keluar'] != '00:00:00' && $row['jam_keluar'] != NULL): ?>
                                        <span class="badge badge-done"><i class="fa-solid fa-check-double me-1"></i> SELESAI</span>
                                    <?php else: ?>
                                        <span class="badge badge-home"><i class="fa-solid fa-spinner fa-spin me-1"></i> DI KANTOR</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fa-solid fa-calendar-xmark"></i>
                                    <p>Tidak ada data kehadiran ditemukan untuk tanggal ini.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>