<?php
include "../db_connect.php";

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}
$set = getSetting($koneksi);

// Proses Update Setting
if (isset($_POST['simpan_setting'])) {
    $nama_per = mysqli_real_escape_string($koneksi, $_POST['nama_perusahaan']);
    $w_header = $_POST['warna_header'];
    $w_footer = $_POST['warna_footer'];
    $w_button = $_POST['warna_button'];
    $w_font = $_POST['warna_font'];
    $footer_txt = mysqli_real_escape_string($koneksi, $_POST['footer_text']);

    // Logika Upload Logo
    $logo_name = $set['logo']; // Default logo lama
    if ($_FILES['logo']['name'] != "") {
        $target_dir = "../assets/img/";
        $logo_name = time() . "_" . $_FILES['logo']['name'];
        move_uploaded_file($_FILES['logo']['tmp_name'], $target_dir . $logo_name);
    }

    $query = "UPDATE setting_web SET 
                nama_perusahaan='$nama_per', 
                logo='$logo_name', 
                warna_header='$w_header', 
                warna_footer='$w_footer', 
                warna_button='$w_button', 
                warna_font='$w_font', 
                footer_text='$footer_txt' 
              WHERE id_setting=1";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Pengaturan berhasil disimpan!'); window.location='setting_web.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pengaturan Web |
        <?= $set['nama_perusahaan'] ?>
    </title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --button-color: <?= $set['warna_button'] ?>;
            --font-color: <?= $set['warna_font'] ?>;
        }

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }

        .color-input {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .color-input input[type="color"] {
            width: 50px;
            height: 50px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include "sidebar.php"; ?>
        <div class="main-content">
            <?php include "header.php"; ?>
            <div class="content-body">
                <h2>⚙️ Pengaturan Identitas & Tampilan</h2>
                <hr><br>

                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Nama Perusahaan</label>
                            <input type="text" name="nama_perusahaan" value="<?= $set['nama_perusahaan'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Logo Perusahaan</label><br>
                            <img src="../assets/img/<?= $set['logo'] ?>" width="80"
                                style="margin-bottom: 10px; border: 1px solid #ddd; padding: 5px;">
                            <input type="file" name="logo" accept="image/*">
                        </div>

                        <div class="color-input">
                            <input type="color" name="warna_header" value="<?= $set['warna_header'] ?>">
                            <label>Warna Header & Sidebar (Primary)</label>
                        </div>

                        <div class="color-input">
                            <input type="color" name="warna_button" value="<?= $set['warna_button'] ?>">
                            <label>Warna Tombol</label>
                        </div>

                        <div class="color-input">
                            <input type="color" name="warna_font" value="<?= $set['warna_font'] ?>">
                            <label>Warna Teks (Header/Tombol)</label>
                        </div>

                        <div class="form-group">
                            <label>Teks Footer (Copyright)</label>
                            <textarea name="footer_text" rows="3"
                                style="width: 100%; border-radius: 8px; border: 1px solid #ddd; padding: 10px;"><?= $set['footer_text'] ?></textarea>
                        </div>

                        <button type="submit" name="simpan_setting" class="btn-login" style="margin-top: 20px;">Simpan
                            Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>