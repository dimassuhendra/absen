<?php
include "../db_connect.php";

// Cek hak akses HR
if ($_SESSION['role'] != 'hr') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// --- LOGIKA SIMPAN GAJI ---
if (isset($_POST['proses_gaji'])) {
    $id_pegawai = $_POST['id_pegawai'];
    $bulan_tahun = $_POST['bulan_tahun']; // format: YYYY-MM
    $gaji_pokok = $_POST['gaji_pokok'];
    $tunjangan = $_POST['tunjangan'];
    $bonus = $_POST['bonus'];
    $potongan = $_POST['potongan'];

    // Cek apakah gaji untuk pegawai tersebut di bulan tersebut sudah pernah diinput
    $cek = mysqli_query($koneksi, "SELECT id_gaji FROM gaji WHERE id_pegawai = '$id_pegawai' AND bulan_tahun = '$bulan_tahun-01'");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Gaji pegawai ini untuk periode tersebut sudah diproses!'); window.location='input_gaji.php';</script>";
    } else {
        $tgl_full = $bulan_tahun . "-01";
        $query = "INSERT INTO gaji (id_pegawai, bulan_tahun, gaji_pokok, tunjangan, bonus, potongan) 
                  VALUES ('$id_pegawai', '$tgl_full', '$gaji_pokok', '$tunjangan', '$bonus', '$potongan')";

        if (mysqli_query($koneksi, $query)) {
            header("Location: input_gaji.php?pesan=sukses");
        }
    }
}

// Ambil data semua pegawai (Join Jabatan untuk ambil standar gaji)
$query_p = "SELECT p.id_pegawai, p.nama_lengkap, p.nip, j.nama_jabatan, j.gaji_pokok, j.tunjangan 
            FROM pegawai p 
            JOIN jabatan j ON p.id_jabatan = j.id_jabatan 
            WHERE p.role != 'admin' 
            ORDER BY p.nama_lengkap ASC";
$data_pegawai = mysqli_query($koneksi, $query_p);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Proses Gaji |
        <?= $set['nama_perusahaan'] ?>
    </title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --button-color: <?= $set['warna_button'] ?>;
        }

        .form-gaji {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            max-width: 700px;
            margin: auto;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .info-box {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid #3498db;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include "sidebar.php"; ?>

        <div class="main-content">
            <?php include "../admin/header.php"; ?>

            <div class="content-body">
                <h2 align="center">💸 Proses Gaji Bulanan Pegawai</h2>
                <p align="center">Gunakan formulir ini untuk menerbitkan slip gaji pegawai.</p>
                <br>

                <div class="form-gaji">
                    <form method="POST">
                        <div class="form-group">
                            <label>Pilih Pegawai</label>
                            <select name="id_pegawai" id="id_pegawai" required onchange="isiOtomatis()"
                                style="width:100%; padding:12px; border-radius:8px; border:1px solid #ddd;">
                                <option value="">-- Pilih Pegawai --</option>
                                <?php while ($p = mysqli_fetch_assoc($data_pegawai)): ?>
                                    <option value="<?= $p['id_pegawai'] ?>" data-gapok="<?= $p['gaji_pokok'] ?>"
                                        data-tunjangan="<?= $p['tunjangan'] ?>">
                                        <?= $p['nama_lengkap'] ?> (
                                        <?= $p['nama_jabatan'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Periode Bulan</label>
                            <input type="month" name="bulan_tahun" value="<?= date('Y-m') ?>" required>
                        </div>

                        <div class="info-box">
                            <p style="margin:0; font-size:13px; color:#2980b9;">Gaji Pokok & Tunjangan akan terisi
                                otomatis berdasarkan standar jabatan.</p>
                        </div>

                        <div class="grid-2">
                            <div class="form-group">
                                <label>Gaji Pokok (Rp)</label>
                                <input type="number" name="gaji_pokok" id="gaji_pokok" readonly
                                    style="background:#f9f9f9;">
                            </div>
                            <div class="form-group">
                                <label>Tunjangan (Rp)</label>
                                <input type="number" name="tunjangan" id="tunjangan" readonly
                                    style="background:#f9f9f9;">
                            </div>
                        </div>

                        <div class="grid-2">
                            <div class="form-group">
                                <label>Bonus / Lembur (Rp)</label>
                                <input type="number" name="bonus" value="0" required>
                            </div>
                            <div class="form-group">
                                <label>Potongan / Pajak / Denda (Rp)</label>
                                <input type="number" name="potongan" value="0" required>
                            </div>
                        </div>

                        <br>
                        <button type="submit" name="proses_gaji" class="btn-login"
                            style="width:100%; padding:15px; font-size:16px;">
                            TERBITKAN SLIP GAJI
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function isiOtomatis() {
            var select = document.getElementById("id_pegawai");
            var option = select.options[select.selectedIndex];

            // Ambil data dari atribut 'data-'
            var gapok = option.getAttribute("data-gapok");
            var tunjangan = option.getAttribute("data-tunjangan");

            // Masukkan ke input
            document.getElementById("gaji_pokok").value = gapok;
            document.getElementById("tunjangan").value = tunjangan;
        }
    </script>

</body>

</html>