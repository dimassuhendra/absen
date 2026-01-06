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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya | <?= $set['nama_perusahaan'] ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --primary: <?= $set['warna_header'] ?>;
            --accent: <?= $set['warna_button'] ?>;
        }

        body { font-family: 'Poppins', sans-serif; background-color: #f0f2f5; margin: 0; }
        .main-content { margin-left: 280px; padding: 40px; min-height: 100vh; }

        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        /* Header Profil */
        .profile-header {
            background: var(--primary);
            height: 150px;
            position: relative;
            display: flex;
            justify-content: center;
        }

        .avatar-wrapper {
            position: absolute;
            bottom: -50px;
            background: white;
            padding: 5px;
            border-radius: 50%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .avatar-wrapper img, .avatar-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            font-size: 50px;
            color: var(--primary);
        }

        .profile-content {
            padding: 70px 40px 40px;
            text-align: center;
        }

        .profile-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
            text-align: left;
        }

        .info-card {
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 12px;
            border-bottom: 3px solid var(--primary);
        }

        .info-card label {
            font-size: 11px;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .info-card p {
            margin: 5px 0 0;
            font-weight: 600;
            color: #1e293b;
        }

        /* Form Styling */
        .edit-section {
            text-align: left;
            border-top: 1px solid #e2e8f0;
            padding-top: 30px;
            margin-top: 20px;
        }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-family: inherit;
            box-sizing: border-box;
            transition: 0.3s;
        }

        .form-group input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0,0,0,0.05);
        }

        .password-box {
            background: #fffbeb;
            border: 1px dashed #f59e0b;
            padding: 20px;
            border-radius: 12px;
        }

        .btn-update {
            background: var(--accent);
            color: white;
            border: none;
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 20px;
        }

        .btn-update:hover { filter: brightness(90%); transform: translateY(-2px); }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <?php include "../admin/header.php"; ?>

        <div class="profile-container">
            <div class="profile-header">
                <div class="avatar-wrapper">
                    <?php if($user['foto_profil']): ?>
                        <img src="../assets/img/<?= $user['foto_profil'] ?>" alt="Foto Profil">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-content">
                <h2 style="margin: 0; color: #1e293b;"><?= $user['nama_lengkap'] ?></h2>
                <span style="color: #64748b; font-size: 14px;">Human Resources Department</span>

                <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil'): ?>
                    <div class="alert-success" style="justify-content: center; margin-top: 20px;">
                        <i class="fa-solid fa-circle-check"></i> Perubahan profil berhasil disimpan!
                    </div>
                <?php endif; ?>

                <div class="profile-info-grid">
                    <div class="info-card">
                        <label>Nomor Induk Pegawai</label>
                        <p><?= $user['nip'] ?></p>
                    </div>
                    <div class="info-card">
                        <label>Jabatan Saat Ini</label>
                        <p><?= $user['nama_jabatan'] ?></p>
                    </div>
                </div>

                <div class="edit-section">
                    <h3 style="font-size: 16px; margin-bottom: 20px; color: var(--primary);">
                        <i class="fa-solid fa-pen-to-square"></i> Perbarui Informasi Akun
                    </h3>
                    
                    <form method="POST">
                        <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" value="<?= $user['nama_lengkap'] ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email Instansi</label>
                                <input type="email" name="email" value="<?= $user['email'] ?>" required>
                            </div>
                        </div>

                        <div class="password-box">
                            <label style="display:block; font-weight:700; color: #b45309; margin-bottom: 5px;">Keamanan Akun</label>
                            <p style="font-size: 12px; color: #d97706; margin: 0 0 15px;">Biarkan kosong jika tidak ingin mengubah kata sandi.</p>
                            <input type="password" name="password_baru" placeholder="Masukkan kata sandi baru..." 
                                   style="width:100%; padding:12px; border: 1px solid #fcd34d; border-radius:8px;">
                        </div>

                        <button type="submit" name="update" class="btn-update">
                            <i class="fa-solid fa-floppy-disk"></i> SIMPAN PERUBAHAN PROFIL
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>