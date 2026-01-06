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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya | <?= $set['nama_perusahaan'] ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --accent-color: <?= $set['warna_button'] ?>;
            --text-on-accent: <?= $set['warna_font'] ?>;
        }

        body { font-family: 'Poppins', sans-serif; background-color: #f4f7fa; }
        .main-content { margin-left: 320px; padding: 40px; min-height: 100vh; }

        .profile-card {
            background: white; border-radius: 20px; border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02); overflow: hidden;
            max-width: 900px; margin: auto;
        }

        .profile-header {
            background: var(--primary-color); padding: 40px; text-align: center; color: white;
        }

        .avatar-circle {
            width: 100px; height: 100px; background: white; color: var(--primary-color);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; font-weight: 700; margin: 0 auto 15px;
            border: 4px solid rgba(255,255,255,0.3);
        }

        .form-section { padding: 40px; }

        .info-box {
            background: #f8f9fa; padding: 15px; border-radius: 12px;
            border-left: 4px solid var(--primary-color); margin-bottom: 20px;
        }

        .form-label { font-size: 0.75rem; font-weight: 700; color: #b2bec3; text-transform: uppercase; }
        
        .form-control-custom {
            background: #f8f9fa; border: 2px solid transparent;
            border-radius: 12px; padding: 12px 15px; transition: 0.3s; width: 100%;
        }
        .form-control-custom:focus { background: white; border-color: var(--primary-color); outline: none; }

        .btn-update {
            background: var(--accent-color); color: var(--text-on-accent);
            border: none; padding: 15px; border-radius: 12px; width: 100%;
            font-weight: 600; transition: 0.3s; margin-top: 20px;
        }
        .btn-update:hover { opacity: 0.9; transform: translateY(-2px); }

        .password-notice {
            font-size: 0.8rem; color: #636e72; background: #fff9e6;
            padding: 10px; border-radius: 8px; margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="mb-4">
            <h4 class="fw-bold mb-0">👤 Profil Pengguna</h4>
            <p class="text-muted small">Kelola informasi data diri dan keamanan akun Anda</p>
        </div>

        <div class="profile-card">
            <div class="profile-header">
                <div class="avatar-circle">
                    <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                </div>
                <h4 class="mb-1"><?= $user['nama_lengkap'] ?></h4>
                <p class="mb-0 opacity-75 small"><?= $user['nama_jabatan'] ?? 'Pegawai' ?> • <?= $user['nip'] ?></p>
            </div>

            <div class="form-section">
                <form method="POST">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3"><i class="fa-solid fa-user-gear me-2 text-primary"></i> Data Personal</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Nomor Induk Pegawai (NIP)</label>
                                <div class="info-box mb-0">
                                    <span class="fw-bold text-dark"><?= $user['nip'] ?></span>
                                    <small class="d-block text-muted">NIP tidak dapat diubah secara mandiri</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control-custom" value="<?= $user['nama_lengkap'] ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alamat Email</label>
                                <input type="email" name="email" class="form-control-custom" value="<?= $user['email'] ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3"><i class="fa-solid fa-shield-halved me-2 text-primary"></i> Keamanan Akun</h6>
                            
                            <div class="password-notice">
                                <i class="fa-solid fa-circle-info me-2"></i> Kosongkan kolom di bawah jika Anda tidak ingin mengganti kata sandi.
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kata Sandi Baru</label>
                                <div class="position-relative">
                                    <input type="password" name="password_baru" id="passInput" class="form-control-custom" placeholder="Masukkan password baru...">
                                    <button type="button" onclick="togglePass()" class="btn position-absolute end-0 top-50 translate-middle-y border-0 text-muted">
                                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Gunakan minimal 6 karakter kombinasi angka dan huruf.</small>
                            </div>

                            <button type="submit" name="update" class="btn-update">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Fungsi Lihat Password
        function togglePass() {
            const input = document.getElementById('passInput');
            const icon = document.getElementById('eyeIcon');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Notifikasi Berhasil
        <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil'): ?>
            Swal.fire({
                icon: 'success',
                title: 'Profil Diperbarui',
                text: 'Data profil Anda telah berhasil disimpan.',
                confirmButtonColor: '<?= $set['warna_header'] ?>'
            });
        <?php endif; ?>
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>