<?php
include "../db_connect.php";

// Cek hak akses HR
if ($_SESSION['role'] != 'hr') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// --- LOGIKA PERSETUJUAN ---
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id_cuti = $_GET['id'];
    $status_baru = ($_GET['aksi'] == 'setuju') ? 'Disetujui' : 'Ditolak';

    $query = "UPDATE pengajuan_cuti SET status = '$status_baru' WHERE id_cuti = '$id_cuti'";
    if (mysqli_query($koneksi, $query)) {
        header("Location: konfirmasi_cuti.php?pesan=update_berhasil");
        exit();
    }
}

// Ambil data pengajuan Pending
$query_pending = "SELECT c.*, p.nama_lengkap, p.nip, j.nama_jabatan 
                  FROM pengajuan_cuti c
                  JOIN pegawai p ON c.id_pegawai = p.id_pegawai
                  JOIN jabatan j ON p.id_jabatan = j.id_jabatan
                  WHERE c.status = 'Pending' 
                  ORDER BY c.id_cuti ASC";
$res_pending = mysqli_query($koneksi, $query_pending);

// Ambil riwayat (Limit 5)
$query_history = "SELECT c.*, p.nama_lengkap 
                  FROM pengajuan_cuti c
                  JOIN pegawai p ON c.id_pegawai = p.id_pegawai
                  WHERE c.status != 'Pending' 
                  ORDER BY c.id_cuti DESC LIMIT 5";
$res_history = mysqli_query($koneksi, $query_history);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Cuti | <?= $set['nama_perusahaan'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: <?= $set['warna_header'] ?>;
            --accent: <?= $set['warna_button'] ?>;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; }
        .main-content { margin-left: 280px; padding: 40px; }
        
        /* Card & Table Style */
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 30px; }
        .card h3 { margin-top: 0; font-size: 18px; display: flex; align-items: center; gap: 10px; color: #2d3436; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; padding: 12px 15px; font-size: 12px; color: #b2bec3; text-transform: uppercase; border-bottom: 2px solid #f1f2f6; }
        td { padding: 15px; border-bottom: 1px solid #f1f2f6; font-size: 14px; vertical-align: middle; }

        /* Status & Badges */
        .type-badge { background: #e8f0fe; color: #1a73e8; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .status-pill { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; color: white; }
        .bg-disetujui { background: #2ecc71; }
        .bg-ditolak { background: #e74c3c; }
        
        /* Action Buttons */
        .btn-action { padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.3s; display: inline-flex; align-items: center; gap: 5px; }
        .btn-approve { background: #2ecc71; color: white; border: 1px solid #27ae60; }
        .btn-approve:hover { background: #27ae60; box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2); }
        .btn-reject { background: #fff; color: #e74c3c; border: 1px solid #e74c3c; }
        .btn-reject:hover { background: #e74c3c; color: white; }

        .reason-box { background: #f8f9fa; border-left: 3px solid #dfe6e9; padding: 8px 12px; border-radius: 4px; font-style: italic; color: #636e72; font-size: 13px; max-width: 250px; }
    </style>
</head>
<body>

    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <?php include "../admin/header.php"; ?>

        <div style="margin-bottom: 30px;">
            <h2 style="font-weight: 700; margin-bottom: 5px;">Persetujuan Izin & Cuti</h2>
            <p style="color: #636e72; margin: 0;">Tinjau alasan dan durasi sebelum memberikan keputusan.</p>
        </div>

        <div class="card">
            <h3><i class="fa-solid fa-clock-rotate-left" style="color: #f1c40f;"></i> Menunggu Review</h3>
            <table>
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Kategori</th>
                        <th>Durasi Tanggal</th>
                        <th>Alasan Karyawan</th>
                        <th style="text-align: center;">Keputusan HR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($res_pending) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($res_pending)): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: #2d3436;"><?= $row['nama_lengkap'] ?></div>
                                    <div style="font-size: 12px; color: #b2bec3;"><?= $row['nama_jabatan'] ?> • <?= $row['nip'] ?></div>
                                </td>
                                <td><span class="type-badge"><?= $row['jenis_pengajuan'] ?></span></td>
                                <td>
                                    <div style="font-weight: 500;"><i class="fa-regular fa-calendar-days me-1"></i> <?= date('d M', strtotime($row['tgl_mulai'])) ?> - <?= date('d M Y', strtotime($row['tgl_selesai'])) ?></div>
                                    <?php 
                                        $diff = (strtotime($row['tgl_selesai']) - strtotime($row['tgl_mulai'])) / (60 * 60 * 24) + 1;
                                        echo "<small style='color: #636e72;'>Total: $diff Hari</small>";
                                    ?>
                                </td>
                                <td>
                                    <div class="reason-box">"<?= $row['alasan'] ?>"</div>
                                </td>
                                <td align="center">
                                    <div style="display: flex; gap: 8px; justify-content: center;">
                                        <a href="konfirmasi_cuti.php?aksi=setuju&id=<?= $row['id_cuti'] ?>" 
                                           class="btn-action btn-approve" onclick="return confirm('Setujui cuti ini?')">
                                           <i class="fa-solid fa-check"></i> Setuju
                                        </a>
                                        <a href="konfirmasi_cuti.php?aksi=tolak&id=<?= $row['id_cuti'] ?>" 
                                           class="btn-action btn-reject" onclick="return confirm('Tolak cuti ini?')">
                                           <i class="fa-solid fa-xmark"></i> Tolak
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" align="center" style="padding: 40px; color: #b2bec3;">
                                <i class="fa-solid fa-face-smile" style="font-size: 30px; margin-bottom: 10px;"></i><br>
                                Tidak ada pengajuan cuti yang perlu diproses saat ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card" style="opacity: 0.9;">
            <h3 style="font-size: 16px;"><i class="fa-solid fa-history"></i> Riwayat Keputusan Terakhir</h3>
            <table style="font-size: 13px;">
                <thead style="background: #fdfdfd;">
                    <tr>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Tanggal</th>
                        <th>Status Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($h = mysqli_fetch_assoc($res_history)): ?>
                        <tr>
                            <td><?= $h['nama_lengkap'] ?></td>
                            <td><?= $h['jenis_pengajuan'] ?></td>
                            <td style="color: #636e72;"><?= date('d/m/Y', strtotime($h['tgl_mulai'])) ?></td>
                            <td>
                                <span class="status-pill bg-<?= strtolower($h['status']) ?>">
                                    <?= $h['status'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>