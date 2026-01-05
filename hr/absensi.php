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

// Ambil data absensi berdasarkan tanggal dan join ke tabel pegawai & jabatan
$query = "SELECT a.*, p.nama_lengkap, p.nip, j.nama_jabatan 
          FROM absensi a 
          JOIN pegawai p ON a.id_pegawai = p.id_pegawai 
          JOIN jabatan j ON p.id_jabatan = j.id_jabatan 
          WHERE a.tanggal = '$tgl_filter' 
          ORDER BY a.jam_masuk DESC";

$data_absensi = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi |
        <?= $set['nama_perusahaan'] ?>
    </title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --button-color: <?= $set['warna_button'] ?>;
            --font-color: <?= $set['warna_font'] ?>;
        }

        .filter-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }

        .badge-masuk {
            background: #2ecc71;
        }

        .badge-selesai {
            background: #3498db;
        }

        .badge-proses {
            background: #f1c40f;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include "sidebar.php"; ?>

        <div class="main-content">
            <?php include "../admin/header.php"; ?>

            <div class="content-body">
                <h2>🕒 Rekap Absensi Pegawai</h2>
                <p>Memantau kehadiran pegawai secara real-time.</p>
                <hr><br>

                <div class="filter-box">
                    <form method="GET" style="display: flex; align-items: center; gap: 10px;">
                        <label>Pilih Tanggal:</label>
                        <input type="date" name="tanggal" value="<?= $tgl_filter ?>"
                            style="padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                        <button type="submit" class="btn-login" style="margin: 0; padding: 8px 20px;">Cari Data</button>
                    </form>
                    <?php if ($tgl_filter != date('Y-m-d')): ?>
                        <a href="absensi.php" style="font-size: 13px; color: var(--primary-color);">Tampilkan Hari Ini</a>
                    <?php endif; ?>
                </div>

                <div
                    style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <table width="100%">
                        <thead>
                            <tr>
                                <th>NIP</th>
                                <th>Nama Pegawai</th>
                                <th>Jabatan</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($data_absensi) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($data_absensi)): ?>
                                    <tr>
                                        <td align="center"><code><?= $row['nip'] ?></code></td>
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
                                            <?= ($row['jam_keluar'] == '00:00:00' || $row['jam_keluar'] == NULL) ? '<span style="color:#e67e22">Belum Absen</span>' : $row['jam_keluar'] ?>
                                        </td>
                                        <td align="center">
                                            <?php if ($row['jam_keluar'] != '00:00:00' && $row['jam_keluar'] != NULL): ?>
                                                <span class="status-badge badge-selesai">SELESAI</span>
                                            <?php else: ?>
                                                <span class="status-badge badge-proses">DI KANTOR</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" align="center" style="padding: 30px;">
                                        <img src="../assets/img/no-data.png" width="100" style="opacity: 0.5;"><br>
                                        Tidak ada data absensi untuk tanggal <b>
                                            <?= date('d/m/Y', strtotime($tgl_filter)) ?>
                                        </b>
                                    </td>
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