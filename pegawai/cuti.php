<?php
include "../db_connect.php";

// Cek hak akses pegawai
if ($_SESSION['role'] != 'pegawai') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);
$id_pegawai = $_SESSION['id_user'];

// --- LOGIKA PENGAJUAN ---
if (isset($_POST['ajukan'])) {
    $jenis = $_POST['jenis_pengajuan']; // Cuti, Izin, atau Sakit
    $mulai = $_POST['tgl_mulai'];
    $selesai = $_POST['tgl_selesai'];
    $alasan = mysqli_real_escape_string($koneksi, $_POST['alasan']);

    $query = "INSERT INTO pengajuan_cuti (id_pegawai, jenis_pengajuan, tgl_mulai, tgl_selesai, alasan, status) 
              VALUES ('$id_pegawai', '$jenis', '$mulai', '$selesai', '$alasan', 'Pending')";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Pengajuan berhasil dikirim!'); window.location='cuti.php';</script>";
    }
}

// Ambil riwayat cuti pegawai ini
$riwayat = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti WHERE id_pegawai = '$id_pegawai' ORDER BY id_cuti DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cuti & Izin |
        <?= $set['nama_perusahaan'] ?>
    </title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color:
                <?= $set['warna_header'] ?>
            ;
            --button-color:
                <?= $set['warna_button'] ?>
            ;
            --font-color:
                <?= $set['warna_font'] ?>
            ;
        }

        .grid-cuti {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }

        .form-card,
        .table-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        /* Status Badges */
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            color: white;
        }

        .status-pending {
            background: #f39c12;
        }

        .status-disetujui {
            background: #27ae60;
        }

        .status-ditolak {
            background: #e74c3c;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-family: inherit;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include "sidebar.php"; ?>

        <div class="main-content">
            <?php include "../admin/header.php"; ?>

            <div class="content-body">
                <h2>📅 Pengajuan Cuti & Izin</h2>
                <hr><br>

                <div class="grid-cuti">
                    <div class="form-card">
                        <h3>Form Pengajuan</h3><br>
                        <form method="POST">
                            <div class="form-group">
                                <label>Jenis Pengajuan</label>
                                <select name="jenis_pengajuan" required
                                    style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                                    <option value="Cuti">Cuti Tahunan</option>
                                    <option value="Izin">Izin (Keperluan Mendesak)</option>
                                    <option value="Sakit">Sakit (Sertakan Surat Dokter Nanti)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Mulai</label>
                                <input type="date" name="tgl_mulai" required>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Selesai</label>
                                <input type="date" name="tgl_selesai" required>
                            </div>
                            <div class="form-group">
                                <label>Alasan / Keterangan</label>
                                <textarea name="alasan" rows="4" placeholder="Jelaskan alasan pengajuan anda..."
                                    required></textarea>
                            </div>
                            <button type="submit" name="ajukan" class="btn-login" style="margin-top: 10px;">Kirim
                                Pengajuan</button>
                        </form>
                    </div>

                    <div class="table-card">
                        <h3>Riwayat Pengajuan</h3><br>
                        <table>
                            <thead>
                                <tr>
                                    <th>Jenis</th>
                                    <th>Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($riwayat) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($riwayat)): ?>
                                        <tr>
                                            <td><b>
                                                    <?= $row['jenis_pengajuan'] ?>
                                                </b></td>
                                            <td>
                                                <small>
                                                    <?= date('d/m/y', strtotime($row['tgl_mulai'])) ?> s/d
                                                    <?= date('d/m/y', strtotime($row['tgl_selesai'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?= $row['alasan'] ?>
                                            </td>
                                            <td align="center">
                                                <span class="status status-<?= strtolower($row['status']) ?>">
                                                    <?= strtoupper($row['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" align="center">Belum ada riwayat pengajuan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>