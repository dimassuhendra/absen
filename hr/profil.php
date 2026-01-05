<?php
include "../db_connect.php";

// Cek hak akses HR
if ($_SESSION['role'] != 'hr') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);
$id_hr = $_SESSION['id_user'];

// Ambil data detail HR
$query = "SELECT p.*, j.nama_jabatan 
          FROM pegawai p 
          LEFT JOIN jabatan j ON p.id_jabatan = j.id_jabatan 
          WHERE p.id_pegawai = '$id_hr'";
$res = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($res);

// --- LOGIKA UPDATE PROFIL ---
if (isset($_POST['update'])) {
    $nama_baru = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $email_baru = mysqli_real_escape_string($koneksi, $_POST['email']);

    // Update data dasar
    $update_query = "UPDATE pegawai SET nama_lengkap = '$nama_baru', email = '$email_baru' WHERE id_pegawai = '$id_hr'";
    mysqli_query($koneksi, $update_query);

    // Update Password jika diisi
    if (!empty($_POST['password_baru'])) {
        $pass_hash = password_hash($_POST['password_baru'], PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE pegawai SET password = '$pass_hash' WHERE id_pegawai = '$id_hr'");
    }

    // Update Session nama agar di header langsung berubah
    $_SESSION['nama'] = $nama_baru;

    header("Location: profil.php?pesan=berhasil");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Profil Saya | Panel HR</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --button-color: <?= $set['warna_button'] ?>;
            --font-color: <?= $set['warna_font'] ?>;
        }

        .profile-wrapper {
            max-width: 500px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .badge-info {
            background: #f1f2f6;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 5px solid var(--primary-color);
        }

        .badge-info label {
            font-size: 11px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .badge-info p {
            margin: 5px 0 0;
            font-weight: bold;
            color: #2c3e50;
        }

        .alert-berhasil {
            background: #2ecc71;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include "sidebar.php"; ?>

        <div class="main-content">
            <?php include "../admin/header.php"; ?>

            <div class="content-body">
                <div class="profile-wrapper">
                    <h2 align="center">👤 Profil Saya</h2>
                    <p align="center" style="color: #888;">Kelola informasi akun HR Anda</p>
                    <hr><br>

                    <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil'): ?>
                        <div class="alert-berhasil">Data profil berhasil diperbarui!</div>
                    <?php endif; ?>

                    <div class="badge-info">
                        <label>Nomor Induk Pegawai (NIP)</label>
                        <p>
                            <?= $user['nip'] ?>
                        </p>
                    </div>

                    <div class="badge-info">
                        <label>Jabatan / Akses</label>
                        <p>
                            <?= $user['nama_jabatan'] ?> (HR)
                        </p>
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?= $user['nama_lengkap'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Email Perusahaan</label>
                            <input type="email" name="email" value="<?= $user['email'] ?>" required>
                        </div>

                        <div
                            style="background: #fffaf0; padding: 15px; border-radius: 10px; border: 1px dashed #f39c12; margin-top: 20px;">
                            <label><b>Ganti Kata Sandi</b></label>
                            <p style="font-size: 11px; color: #e67e22; margin-bottom: 10px;">Biarkan kosong jika tidak
                                ingin mengubah password.</p>
                            <input type="password" name="password_baru" placeholder="Masukkan password baru...">
                        </div>

                        <br>
                        <button type="submit" name="update" class="btn-login"
                            style="width: 100%; padding: 12px; font-weight: bold;">
                            SIMPAN PERUBAHAN
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

</html>