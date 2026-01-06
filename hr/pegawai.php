<?php
include "../db_connect.php";

// Cek hak akses HR
if ($_SESSION['role'] != 'hr') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// --- LOGIKA CRUD TETAP SAMA (Tidak diubah agar fungsionalitas terjaga) ---
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jabatan = $_POST['id_jabatan'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $tgl_sekarang = date('dmy');
    $res_id = mysqli_query($koneksi, "SELECT id_pegawai FROM pegawai ORDER BY id_pegawai DESC LIMIT 1");
    $last_id = mysqli_fetch_assoc($res_id);
    $next_num = ($last_id) ? $last_id['id_pegawai'] + 1 : 1;
    $nip = $tgl_sekarang . str_pad($next_num, 3, "0", STR_PAD_LEFT);

    $domain_raw = str_replace(' ', '', strtolower($set['nama_perusahaan']));
    $nama_clean = str_replace(' ', '', strtolower($nama));
    $email = $nama_clean . "@" . $domain_raw . ".com";

    $query = "INSERT INTO pegawai (nip, nama_lengkap, email, password, id_jabatan, role) 
              VALUES ('$nip', '$nama', '$email', '$password', '$jabatan', '$role')";

    if (mysqli_query($koneksi, $query)) {
        header("Location: pegawai.php?pesan=berhasil");
    }
}

// (Logika Edit & Hapus tetap ada di sini...)
if (isset($_POST['edit'])) {
    $id = $_POST['id_pegawai'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jabatan = $_POST['id_jabatan'];
    $role = $_POST['role'];
    mysqli_query($koneksi, "UPDATE pegawai SET nama_lengkap='$nama', id_jabatan='$jabatan', role='$role' WHERE id_pegawai='$id'");
    if (!empty($_POST['password'])) {
        $pass_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE pegawai SET password='$pass_hash' WHERE id_pegawai='$id'");
    }
    header("Location: pegawai.php?pesan=update");
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM pegawai WHERE id_pegawai='$id'");
    header("Location: pegawai.php");
}

$list_jabatan = mysqli_query($koneksi, "SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
$data_pegawai = mysqli_query($koneksi, "SELECT p.*, j.nama_jabatan 
                FROM pegawai p 
                LEFT JOIN jabatan j ON p.id_jabatan = j.id_jabatan 
                WHERE p.role != 'admin'
                ORDER BY p.id_pegawai DESC"); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pegawai | <?= $set['nama_perusahaan'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: <?= $set['warna_header'] ?>;
            --accent: <?= $set['warna_button'] ?>;
            --font-accent: <?= $set['warna_font'] ?>;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; }
        .main-content { margin-left: 280px; padding: 40px; }
        
        /* Table Styling */
        .card-table { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); padding: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f8f9fa; color: #636e72; font-size: 12px; text-transform: uppercase; padding: 15px; border-bottom: 2px solid #edf2f7; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #edf2f7; font-size: 14px; color: #2d3436; }
        
        /* UI Components */
        .btn-main { background: var(--accent); color: var(--font-accent); padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-main:hover { opacity: 0.9; transform: translateY(-2px); }
        
        .badge-role { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .role-pegawai { background: #e3f2fd; color: #1976d2; }
        .role-hr { background: #f3e5f5; color: #7b1fa2; }

        .nip-code { background: #f1f2f6; padding: 4px 8px; border-radius: 5px; font-family: monospace; font-weight: bold; color: #57606f; }

        /* Modal Styling */
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); }
        .modal-content { background: white; width: 450px; margin: 50px auto; border-radius: 20px; padding: 30px; position: relative; animation: slideDown 0.4s ease; }
        @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: #2d3436; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #dfe6e9; border-radius: 10px; box-sizing: border-box; font-family: 'Poppins'; }
        
        .btn-group { display: flex; flex-direction: column; gap: 10px; margin-top: 20px; }
    </style>
</head>
<body>

<div class="wrapper">
    <?php include "sidebar.php"; ?>
    <div class="main-content">
        <?php include "../admin/header.php"; ?>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h2 style="font-weight: 700; margin: 0;">Data Pegawai</h2>
                <p style="color: #636e72; font-size: 14px; margin: 5px 0 0;">Kelola akun, jabatan, dan hak akses karyawan.</p>
            </div>
            <button class="btn-main" onclick="showModal('modalTambah')">
                <i class="fa-solid fa-user-plus"></i> Tambah Pegawai
            </button>
        </div>

        <div class="card-table">
            <table>
                <thead>
                    <tr>
                        <th>NIP</th>
                        <th>Pegawai</th>
                        <th>Kontak</th>
                        <th>Jabatan</th>
                        <th>Akses</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($data_pegawai)): ?>
                    <tr>
                        <td><span class="nip-code"><?= $row['nip'] ?></span></td>
                        <td>
                            <div style="font-weight: 600;"><?= $row['nama_lengkap'] ?></div>
                        </td>
                        <td style="font-size: 13px; color: #636e72;"><i class="fa-regular fa-envelope me-1"></i> <?= $row['email'] ?></td>
                        <td>
                            <span style="color: #2d3436;"><?= $row['nama_jabatan'] ?? '<em>Belum diset</em>' ?></span>
                        </td>
                        <td>
                            <span class="badge-role role-<?= $row['role'] ?>"><?= $row['role'] ?></span>
                        </td>
                        <td align="center">
                            <button onclick="openEdit('<?= $row['id_pegawai'] ?>','<?= $row['nama_lengkap'] ?>','<?= $row['id_jabatan'] ?>','<?= $row['role'] ?>')" 
                                    style="border:none; background:#fff8e1; color:#ffa000; padding:8px; border-radius:8px; cursor:pointer;" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <a href="pegawai.php?hapus=<?= $row['id_pegawai'] ?>" 
                               onclick="return confirm('Hapus data <?= $row['nama_lengkap'] ?>?')"
                               style="background:#ffebee; color:#d32f2f; padding:8px 10px; border-radius:8px; text-decoration:none; margin-left:5px;">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalTambah" class="modal">
    <div class="modal-content">
        <h4 style="margin-top:0; font-weight:700;">Registrasi Pegawai</h4>
        <p style="font-size:12px; color:gray; margin-bottom:20px;">NIP dan Email akan dibuat secara otomatis oleh sistem.</p>
        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" placeholder="Contoh: Budi Santoso" required>
            </div>
            <div class="form-group">
                <label>Password Awal</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Jabatan</label>
                <select name="id_jabatan" class="form-control" required>
                    <?php mysqli_data_seek($list_jabatan, 0);
                    while ($j = mysqli_fetch_assoc($list_jabatan)): ?>
                        <option value="<?= $j['id_jabatan'] ?>"><?= $j['nama_jabatan'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Hak Akses Sistem</label>
                <select name="role" class="form-control" required>
                    <option value="pegawai">Pegawai Biasa</option>
                    <option value="hr">HR (Human Resource)</option>
                </select>
            </div>
            <div class="btn-group">
                <button type="submit" name="tambah" class="btn-main" style="justify-content:center;">Simpan & Buat Akun</button>
                <button type="button" onclick="closeModal('modalTambah')" style="border:none; background:none; color:gray; cursor:pointer;">Batal</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="modal">
    <div class="modal-content">
        <h4 style="margin-top:0; font-weight:700;">Perbarui Data</h4>
        <form method="POST">
            <input type="hidden" name="id_pegawai" id="edit_id">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" id="edit_nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Ganti Password (Biarkan kosong jika tidak diganti)</label>
                <input type="password" name="password" class="form-control" placeholder="********">
            </div>
            <div class="form-group">
                <label>Jabatan</label>
                <select name="id_jabatan" id="edit_jabatan" class="form-control" required>
                    <?php mysqli_data_seek($list_jabatan, 0);
                    while ($j = mysqli_fetch_assoc($list_jabatan)): ?>
                        <option value="<?= $j['id_jabatan'] ?>"><?= $j['nama_jabatan'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Hak Akses</label>
                <select name="role" id="edit_role" class="form-control" required>
                    <option value="pegawai">Pegawai</option>
                    <option value="hr">HR</option>
                </select>
            </div>
            <div class="btn-group">
                <button type="submit" name="edit" class="btn-main" style="justify-content:center; background:#2d3436;">Update Pegawai</button>
                <button type="button" onclick="closeModal('modalEdit')" style="border:none; background:none; color:gray; cursor:pointer;">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showModal(id) { document.getElementById(id).style.display = 'block'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    function openEdit(id, nama, jabatan, role) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_jabatan').value = jabatan;
        document.getElementById('edit_role').value = role;
        showModal('modalEdit');
    }
    // Menutup modal jika klik di luar area modal
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = "none";
        }
    }
</script>

</body>
</html>