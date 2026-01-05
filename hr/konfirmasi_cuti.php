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
    }
}

// Ambil semua data pengajuan cuti yang masih Pending
$query_pending = "SELECT c.*, p.nama_lengkap, p.nip, j.nama_jabatan 
                  FROM pengajuan_cuti c
                  JOIN pegawai p ON c.id_pegawai = p.id_pegawai
                  JOIN jabatan j ON p.id_jabatan = j.id_jabatan
                  WHERE c.status = 'Pending' 
                  ORDER BY c.id_cuti ASC";
$res_pending = mysqli_query($koneksi, $query_pending);

// Ambil riwayat cuti yang sudah diproses (Limit 10 terbaru)
$query_history = "SELECT c.*, p.nama_lengkap 
                  FROM pengajuan_cuti c
                  JOIN pegawai p ON c.id_pegawai = p.id_pegawai
                  WHERE c.status != 'Pending' 
                  ORDER BY c.id_cuti DESC LIMIT 10";
$res_history = mysqli_query($koneksi, $query_history);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Persetujuan Cuti |
        <?= $set['nama_perusahaan'] ?>
    </title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --button-color: <?= $set['warna_button'] ?>;
        }

        .btn-approve {
            background: #27ae60;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
        }

        .btn-reject {
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            color: white;
            text-transform: uppercase;
        }

        .bg-disetujui {
            background: #2ecc71;
        }

        .bg-ditolak {
            background: #95a5a6;
        }

        .card-table {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include "sidebar.php"; ?>

        <div class="main-content">
            <?php include "../admin/header.php"; ?>

            <div class="content-body">
                <h2>📝 Persetujuan Cuti & Izin</h2>
                <p>Tinjau dan proses permohonan absen pegawai di bawah ini.</p>
                <hr><br>

                <div class="card-table">
                    <h3>Menunggu Persetujuan</h3><br>
                    <table>
                        <thead>
                            <tr>
                                <th>Pegawai</th>
                                <th>Jenis</th>
                                <th>Tanggal</th>
                                <th>Alasan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($res_pending) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($res_pending)): ?>
                                    <tr>
                                        <td>
                                            <b>
                                                <?= $row['nama_lengkap'] ?>
                                            </b><br>
                                            <small>
                                                <?= $row['nama_jabatan'] ?> (
                                                <?= $row['nip'] ?>)
                                            </small>
                                        </td>
                                        <td>
                                            <?= $row['jenis_pengajuan'] ?>
                                        </td>
                                        <td>
                                            <small>
                                                <?= date('d/m/y', strtotime($row['tgl_mulai'])) ?> s/d
                                                <?= date('d/m/y', strtotime($row['tgl_selesai'])) ?>
                                            </small>
                                        </td>
                                        <td><i>"
                                                <?= $row['alasan'] ?>"
                                            </i></td>
                                        <td align="center">
                                            <a href="konfirmasi_cuti.php?aksi=setuju&id=<?= $row['id_cuti'] ?>"
                                                class="btn-approve"
                                                onclick="return confirm('Setujui permohonan ini?')">Terima</a>
                                            <a href="konfirmasi_cuti.php?aksi=tolak&id=<?= $row['id_cuti'] ?>"
                                                class="btn-reject" onclick="return confirm('Tolak permohonan ini?')">Tolak</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" align="center" style="padding:20px; color:gray;">Tidak ada pengajuan
                                        baru.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-table">
                    <h3>Riwayat Keputusan Terbaru</h3><br>
                    <table style="font-size: 13px; opacity: 0.8;">
                        <thead>
                            <tr>
                                <th>Nama Pegawai</th>
                                <th>Jenis</th>
                                <th>Tanggal Cuti</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($h = mysqli_fetch_assoc($res_history)): ?>
                                <tr>
                                    <td>
                                        <?= $h['nama_lengkap'] ?>
                                    </td>
                                    <td>
                                        <?= $h['jenis_pengajuan'] ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($h['tgl_mulai'])) ?>
                                    </td>
                                    <td align="center">
                                        <span class="status-badge bg-<?= strtolower($h['status']) ?>">
                                            <?= $h['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>