<?php
include "../db_connect.php";

// Cek hak akses HR
if ($_SESSION['role'] != 'hr') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// --- LOGIKA UPDATE GAJI JABATAN ---
if (isset($_POST['update_gaji'])) {
    $id_jabatan = $_POST['id_jabatan'];
    $gapok = $_POST['gaji_pokok'];
    $tunjangan = $_POST['tunjangan'];
    $uang_makan = $_POST['uang_makan']; // Contoh tambahan tunjangan harian

    // Kita asumsikan tabel jabatan sudah memiliki kolom gaji_pokok, tunjangan, dll.
    // Jika belum, Anda perlu menambahkannya via database (ALTER TABLE).
    $query = "UPDATE jabatan SET 
              gaji_pokok = '$gapok', 
              tunjangan = '$tunjangan', 
              uang_makan = '$uang_makan' 
              WHERE id_jabatan = '$id_jabatan'";

    if (mysqli_query($koneksi, $query)) {
        header("Location: gaji_jabatan.php?pesan=berhasil");
    }
}

// Ambil semua data jabatan
$query_jabatan = mysqli_query($koneksi, "SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Set Gaji Jabatan |
        <?= $set['nama_perusahaan'] ?>
    </title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --button-color: <?= $set['warna_button'] ?>;
        }

        .card-gaji {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 14px;
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .grid-input {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }

        .btn-save {
            background: var(--button-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include "sidebar.php"; ?>

        <div class="main-content">
            <?php include "../admin/header.php"; ?>

            <div class="content-body">
                <h2>💰 Pengaturan Gaji per Jabatan</h2>
                <p>Tentukan standar gaji pokok dan tunjangan untuk setiap posisi jabatan.</p>
                <hr><br>

                <?php while ($row = mysqli_fetch_assoc($query_jabatan)): ?>
                    <div class="card-gaji">
                        <form method="POST">
                            <input type="hidden" name="id_jabatan" value="<?= $row['id_jabatan'] ?>">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h3 style="margin:0; color: var(--primary-color);">💼
                                    <?= $row['nama_jabatan'] ?>
                                </h3>
                                <button type="submit" name="update_gaji" class="btn-save">Simpan Perubahan</button>
                            </div>

                            <div class="grid-input">
                                <div class="input-group">
                                    <label>Gaji Pokok (Rp)</label>
                                    <input type="number" name="gaji_pokok" value="<?= $row['gaji_pokok'] ?>" placeholder="0"
                                        required>
                                </div>
                                <div class="input-group">
                                    <label>Tunjangan Jabatan (Rp)</label>
                                    <input type="number" name="tunjangan" value="<?= $row['tunjangan'] ?>" placeholder="0"
                                        required>
                                </div>
                                <div class="input-group">
                                    <label>Uang Makan / Hari (Rp)</label>
                                    <input type="number" name="uang_makan" value="<?= $row['uang_makan'] ?>" placeholder="0"
                                        required>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

</body>

</html>