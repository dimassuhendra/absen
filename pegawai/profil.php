<?php
include "../db_connect.php";

// Cek hak akses pegawai
if ($_SESSION['role'] != 'pegawai') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);
$id_pegawai = $_SESSION['id_user'];

// Ambil data detail pegawai
$query = "SELECT p.*, j.nama_jabatan 
          FROM pegawai p 
          LEFT JOIN jabatan j ON p.id_jabatan = j.id_jabatan 
          WHERE p.id_pegawai = '$id_pegawai'";
$res = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($res);

// --- LOGIKA UPDATE PROFIL ---
if (isset($_POST['update'])) {
    $nama_baru = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $email_baru = mysqli_real_escape_string($koneksi, $_POST['email']);

    // Update data dasar
    $update_query = "UPDATE pegawai SET nama_lengkap = '$nama_baru', email = '$email_baru' WHERE id_pegawai = '$id_pegawai'";
    mysqli_query($koneksi, $update_query);

    // Update Password jika diisi
    if (!empty($_POST['password_baru'])) {
        $pass_hash = password_hash($_POST['password_baru'], PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE pegawai SET password = '$pass_hash' WHERE id_pegawai = '$id_pegawai'");
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
    <title>Edit Profil |
        <?= $set['nama_perusahaan'] ?>
    </title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --button-color: <?= $set['warna_button'] ?>;
            --font-color: <?= $set['warna_font'] ?>;
        }

        .profile-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .info-locked {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #ddd;
        }

        .info-locked label {
            font-size: 12px;
            color: #666;
            display: block;
        }

        .info-locked span {
            font-weight: bold;
            color: #333;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include "sidebar.php"; ?>

        <div class="main-content">
            <?php include "../admin/header.php"; ?>

            <div class="content-body">
                <div class="profile-container">
                    <h2>👤 Pengaturan Profil</h2>
                    <p>Kelola informasi akun Anda di sini.</p>
                    <hr><br>

                    <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil'): ?>
                        <div class="alert-success">Profil berhasil diperbarui!</div>
                    <?php endif; ?>

                    <div class="info-locked">
                        <label>Nomor Induk Pegawai (NIP)</label>
                        <span>
                            <?= $user['nip'] ?>
                        </span>
                    </div>
                    <div class="info-locked">
                        <label>Jabatan Saat Ini</label>
                        <span>
                            <?= $user['nama_jabatan'] ?? 'N/A' ?>
                        </span>
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?= $user['nama_lengkap'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Alamat Email</label>
                            <input type="email" name="email" value="<?= $user['email'] ?>" required>
                        </div>

                        <div style="margin-top: 30px; padding: 15px; border: 1px solid #eee; border-radius: 10px;">
                            <h4 style="margin-top: 0;">Ganti Kata Sandi</h4>
                            <p style="font-size: 12px; color: #888;">Kosongkan jika tidak ingin mengganti password.</p>
                            <div class="form-group">
                                <label>Password Baru</label>
                                <input type="password" name="password_baru" placeholder="Minimal 6 karakter">
                            </div>
                        </div>

                        <br>
                        <button type="submit" name="update" class="btn-login" style="width: 100%;">Simpan
                            Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

</html>