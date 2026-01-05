<?php
include "../db_connect.php";

if ($_SESSION['role'] != 'pegawai') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);
$id_pegawai = $_SESSION['id_user'];

// Ambil data gaji terakhir pegawai ini (Join dengan tabel jabatan untuk tunjangan standar)
$query_gaji = "SELECT g.*, p.nama_lengkap, j.nama_jabatan 
               FROM gaji g
               JOIN pegawai p ON g.id_pegawai = p.id_pegawai
               JOIN jabatan j ON p.id_jabatan = j.id_jabatan
               WHERE g.id_pegawai = '$id_pegawai' 
               ORDER BY g.id_gaji DESC LIMIT 1";

$res_gaji = mysqli_query($koneksi, $query_gaji);
$data = mysqli_fetch_assoc($res_gaji);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Slip Gaji |
        <?= $set['nama_perusahaan'] ?>
    </title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --button-color: <?= $set['warna_button'] ?>;
        }

        .salary-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: auto;
        }

        .row-detail {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #eee;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .section-title {
            background: #f8f9fa;
            padding: 8px 15px;
            border-left: 4px solid var(--primary-color);
            margin: 20px 0 10px 0;
            font-weight: bold;
        }

        /* CSS KHUSUS CETAK */
        @media print {
            body * {
                visibility: hidden;
            }

            .sidebar,
            .btn-print,
            .header,
            .sidebar-menu {
                display: none !important;
            }

            .salary-card,
            .salary-card * {
                visibility: visible;
            }

            .salary-card {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none;
                border: 1px solid #000;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include "sidebar.php"; ?>
        <div class="main-content">
            <?php include "../admin/header.php"; ?>

            <div class="content-body">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; max-width: 800px; margin: auto; margin-bottom: 20px;">
                    <h2>Rincian Gaji Bulan Ini</h2>
                    <button onclick="window.print()" class="btn-print"
                        style="background: #27ae60; color: white; padding: 10px 20px; border:none; border-radius: 5px; cursor:pointer;">🖨️
                        Cetak PDF / Slip Gaji</button>
                </div>

                <?php if ($data): ?>
                    <div class="salary-card">
                        <div style="text-align: center;">
                            <img src="../assets/img/<?= $set['logo'] ?>" width="60">
                            <h3>SLIP GAJI KARYAWAN</h3>
                            <p>
                                <?= $set['nama_perusahaan'] ?>
                            </p>
                            <hr>
                        </div>

                        <table style="width: 100%; margin-bottom: 20px;">
                            <tr>
                                <td width="150">Nama Pegawai</td>
                                <td>: <b>
                                        <?= $data['nama_lengkap'] ?>
                                    </b></td>
                                <td width="150">Bulan / Tahun</td>
                                <td>:
                                    <?= date('F Y', strtotime($data['bulan_gaji'])) ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Jabatan</td>
                                <td>:
                                    <?= $data['nama_jabatan'] ?>
                                </td>
                                <td>Status Bayar</td>
                                <td>: <span style="color: green;">Sudah Terbayar</span></td>
                            </tr>
                        </table>

                        <div class="section-title">PENGHASILAN (+)</div>
                        <div class="row-detail"><span>Gaji Pokok</span> <span>Rp
                                <?= number_format($data['gaji_pokok'], 0, ',', '.') ?>
                            </span></div>
                        <div class="row-detail"><span>Tunjangan Jabatan</span> <span>Rp
                                <?= number_format($data['tunjangan'], 0, ',', '.') ?>
                            </span></div>
                        <div class="row-detail"><span>Bonus / Lembur</span> <span>Rp
                                <?= number_format($data['bonus'], 0, ',', '.') ?>
                            </span></div>

                        <div class="section-title">POTONGAN (-)</div>
                        <div class="row-detail"><span>Potongan (BPJS/Pajak/Denda)</span> <span style="color: red;">Rp
                                <?= number_format($data['potongan'], 0, ',', '.') ?>
                            </span></div>

                        <div style="margin-top: 30px; border-top: 2px solid #000;"></div>
                        <div class="total-row">
                            <span>TOTAL GAJI BERSIH (TAKE HOME PAY)</span>
                            <span>Rp
                                <?= number_format(($data['gaji_pokok'] + $data['tunjangan'] + $data['bonus']) - $data['potongan'], 0, ',', '.') ?>
                            </span>
                        </div>

                        <div style="margin-top: 50px; display: flex; justify-content: flex-end;">
                            <div style="text-align: center; width: 200px;">
                                <p>Dicetak pada:
                                    <?= date('d/m/Y') ?>
                                </p>
                                <br><br><br>
                                <p><b>Manager HRD</b></p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="salary-card" style="text-align: center;">
                        <p>Data gaji bulan ini belum tersedia. Silakan hubungi bagian HR.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>