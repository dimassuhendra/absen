<?php
include_once "../db_connect.php";

// Cek hak akses HR
if ($_SESSION['role'] != 'hr') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// --- LOGIKA TAMBAH ---
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
        exit();
    }
}

// --- LOGIKA EDIT ---
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
    exit();
}

// Ambil Data Master
$list_dept = mysqli_query($koneksi, "SELECT * FROM departemen ORDER BY nama_departemen ASC");
$all_jabatan = mysqli_query($koneksi, "SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
$jabatans = [];
while($j = mysqli_fetch_assoc($all_jabatan)) {
    $jabatans[] = $j;
}

$data_pegawai = mysqli_query($koneksi, "SELECT p.*, j.nama_jabatan, d.nama_departemen, d.id_departemen 
                FROM pegawai p 
                LEFT JOIN jabatan j ON p.id_jabatan = j.id_jabatan 
                LEFT JOIN departemen d ON j.id_departemen = d.id_departemen
                WHERE p.role != 'admin'
                ORDER BY p.id_pegawai DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pegawai | <?= $set['nama_perusahaan'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root { --primary: <?= $set['warna_header'] ?>; --accent: <?= $set['warna_button'] ?>; --font-accent: <?= $set['warna_font'] ?>; }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; }
        .main-content { margin-left: 280px; padding: 20px 40px; min-height: 100vh; }
        .content-body { margin-top: 30px; }
        .card-table { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); padding: 25px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; color: #636e72; font-size: 11px; text-transform: uppercase; padding: 15px; text-align: left; border-bottom: 2px solid #eee; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 13px; }
        .btn-main { background: var(--accent); color: var(--font-accent); padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); }
        .modal-content { background: white; width: 500px; margin: 50px auto; border-radius: 20px; padding: 30px; animation: slideDown 0.4s; }
        @keyframes slideDown { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 5px; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #dfe6e9; border-radius: 8px; box-sizing: border-box; }
        .nip-code { background: #f1f2f6; padding: 4px 8px; border-radius: 5px; font-family: monospace; font-weight: bold; }
        .btn-action { border:none; padding:8px; border-radius:8px; cursor:pointer; }
    </style>
</head>
<body>

<div class="wrapper">
    <?php include "sidebar.php"; ?>
    <div class="main-content">
        <?php include "../admin/header.php"; ?>
        
        <div class="content-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <h2 style="font-weight: 700; margin: 0;">Data Pegawai</h2>
                    <p style="color: #636e72; font-size: 14px; margin: 5px 0 0;">Daftar seluruh karyawan perusahaan.</p>
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
                            <th>Nama Pegawai</th>
                            <th>Departemen</th>
                            <th>Jabatan</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($data_pegawai)): ?>
                        <tr>
                            <td><span class="nip-code"><?= $row['nip'] ?></span></td>
                            <td>
                                <div style="font-weight: 600;"><?= $row['nama_lengkap'] ?></div>
                                <small style="color: #636e72;"><?= $row['email'] ?></small>
                            </td>
                            <td><i class="fa-solid fa-sitemap text-primary"></i> <?= $row['nama_departemen'] ?? '-' ?></td>
                            <td><strong><?= $row['nama_jabatan'] ?? '-' ?></strong></td>
                            <td align="center">
                                <button onclick="openEdit('<?= $row['id_pegawai'] ?>','<?= $row['nama_lengkap'] ?>','<?= $row['id_departemen'] ?>','<?= $row['id_jabatan'] ?>','<?= $row['role'] ?>')" 
                                        class="btn-action" style="background:#fff8e1; color:#ffa000;">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <a href="pegawai.php?hapus=<?= $row['id_pegawai'] ?>" class="btn-action" style="background:#ffebee; color:#d32f2f; text-decoration:none;"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="modalTambah" class="modal">
    <div class="modal-content">
        <h4 style="margin-top:0;">Tambah Pegawai</h4>
        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Pilih Departemen</label>
                <select id="add_dept" class="form-control" onchange="filterJabatan('add_dept', 'add_jabatan')" required>
                    <option value="">-- Pilih Departemen --</option>
                    <?php mysqli_data_seek($list_dept, 0); while($d = mysqli_fetch_assoc($list_dept)): ?>
                        <option value="<?= $d['id_departemen'] ?>"><?= $d['nama_departemen'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Pilih Posisi (Jabatan)</label>
                <select name="id_jabatan" id="add_jabatan" class="form-control" required disabled>
                    <option value="">-- Pilih Departemen Dulu --</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Role Akses</label>
                <select name="role" class="form-control">
                    <option value="pegawai">Pegawai</option>
                    <option value="hr">HRD</option>
                </select>
            </div>
            <button type="submit" name="tambah" class="btn-main" style="width: 100%;">Simpan</button>
            <button type="button" onclick="closeModal('modalTambah')" style="width:100%; background:none; border:none; margin-top:10px; cursor:pointer;">Batal</button>
        </form>
    </div>
</div>

<div id="modalEdit" class="modal">
    <div class="modal-content">
        <h4>Edit Pegawai</h4>
        <form method="POST">
            <input type="hidden" name="id_pegawai" id="edit_id">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" id="edit_nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Departemen</label>
                <select id="edit_dept" class="form-control" onchange="filterJabatan('edit_dept', 'edit_jabatan')">
                    <?php mysqli_data_seek($list_dept, 0); while($d = mysqli_fetch_assoc($list_dept)): ?>
                        <option value="<?= $d['id_departemen'] ?>"><?= $d['nama_departemen'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Jabatan</label>
                <select name="id_jabatan" id="edit_jabatan" class="form-control" required></select>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" id="edit_role" class="form-control">
                    <option value="pegawai">Pegawai</option>
                    <option value="hr">HRD</option>
                </select>
            </div>
            <button type="submit" name="edit" class="btn-main" style="width: 100%;">Update</button>
            <button type="button" onclick="closeModal('modalEdit')" style="width:100%; background:none; border:none; margin-top:10px; cursor:pointer;">Batal</button>
        </form>
    </div>
</div>

<script>
    // Data jabatan dari PHP ke JS
    const jabatans = <?= json_encode($jabatans) ?>;

    function filterJabatan(deptId, jabId, selectedJabatan = null) {
        const dId = document.getElementById(deptId).value;
        const jSelect = document.getElementById(jabId);
        
        jSelect.innerHTML = '<option value="">-- Pilih Jabatan --</option>';
        jSelect.disabled = dId === "";

        const filtered = jabatans.filter(j => j.id_departemen == dId);
        filtered.forEach(j => {
            const opt = document.createElement('option');
            opt.value = j.id_jabatan;
            opt.text = j.nama_jabatan;
            if(selectedJabatan && j.id_jabatan == selectedJabatan) opt.selected = true;
            jSelect.add(opt);
        });
    }

    function showModal(id) { document.getElementById(id).style.display = 'block'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function openEdit(id, nama, dept, jab, role) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_dept').value = dept;
        document.getElementById('edit_role').value = role;
        filterJabatan('edit_dept', 'edit_jabatan', jab);
        showModal('modalEdit');
    }
</script>
</body>
</html>