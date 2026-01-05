<?php
include "../db_connect.php";

// Cek hak akses HR
if ($_SESSION['role'] != 'hr') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// --- LOGIKA CRUD PEGAWAI ---

// 1. Tambah Pegawai (NIP & Email Otomatis)
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jabatan = $_POST['id_jabatan'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Generate NIP (Format: ddmmyyxxx)
    $tgl_sekarang = date('dmy');
    $res_id = mysqli_query($koneksi, "SELECT id_pegawai FROM pegawai ORDER BY id_pegawai DESC LIMIT 1");
    $last_id = mysqli_fetch_assoc($res_id);
    $next_num = ($last_id) ? $last_id['id_pegawai'] + 1 : 1;
    $nip = $tgl_sekarang . str_pad($next_num, 3, "0", STR_PAD_LEFT);

    // Generate Email (nama@perusahaan.com)
    $domain_raw = str_replace(' ', '', strtolower($set['nama_perusahaan']));
    $nama_clean = str_replace(' ', '', strtolower($nama));
    $email = $nama_clean . "@" . $domain_raw . ".com";

    $query = "INSERT INTO pegawai (nip, nama_lengkap, email, password, id_jabatan, role) 
              VALUES ('$nip', '$nama', '$email', '$password', '$jabatan', '$role')";

    if (mysqli_query($koneksi, $query)) {
        header("Location: pegawai.php?pesan=berhasil");
    }
}

// 2. Edit Pegawai
if (isset($_POST['edit'])) {
    $id = $_POST['id_pegawai'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jabatan = $_POST['id_jabatan'];
    $role = $_POST['role'];

    $query = "UPDATE pegawai SET nama_lengkap='$nama', id_jabatan='$jabatan', role='$role' WHERE id_pegawai='$id'";
    mysqli_query($koneksi, $query);

    // Update password jika diisi
    if (!empty($_POST['password'])) {
        $pass_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE pegawai SET password='$pass_hash' WHERE id_pegawai='$id'");
    }
    header("Location: pegawai.php?pesan=update");
}

// 3. Hapus Pegawai
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM pegawai WHERE id_pegawai='$id'");
    header("Location: pegawai.php");
}

// Ambil data jabatan untuk dropdown
$list_jabatan = mysqli_query($koneksi, "SELECT * FROM jabatan ORDER BY nama_jabatan ASC");

// Ambil data pegawai (Join Jabatan)
$data_pegawai = mysqli_query($koneksi, "SELECT p.*, j.nama_jabatan 
                FROM pegawai p 
                LEFT JOIN jabatan j ON p.id_jabatan = j.id_jabatan 
                WHERE p.role = 'hr' OR p.role = 'pegawai'
                ORDER BY p.id_pegawai DESC"); ?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Pegawai | Panel HR</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color:
                <?= $set['warna_header'] ?>
            ;
            --button-color:
                <?= $set['warna_button'] ?>
            ;
            --font-color:
                <?= $set['warna_font'] ?>
            ;
        }

        .btn-add {
            background: var(--button-color);
            color: var(--font-color);
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .action-btn {
            padding: 5px 10px;
            color: white;
            border-radius: 3px;
            font-size: 11px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background: #f1c40f;
        }

        .btn-delete {
            background: #e74c3c;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 25px;
            width: 450px;
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include "sidebar.php"; ?>
        <div class="main-content">
            <?php include "../admin/header.php"; ?>
            <div class="content-body">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>Manajemen Data Pegawai</h2>
                    <button class="btn-add" onclick="showModal('modalTambah')">+ Tambah Pegawai</button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>NIP</th>
                            <th>Nama Lengkap</th>
                            <th>Email Perusahaan</th>
                            <th>Jabatan</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($data_pegawai)): ?>
                            <tr>
                                <td align="center"><code><?= $row['nip'] ?></code></td>
                                <td><b>
                                        <?= $row['nama_lengkap'] ?>
                                    </b></td>
                                <td>
                                    <?= $row['email'] ?>
                                </td>
                                <td>
                                    <?= $row['nama_jabatan'] ?? '<small style="color:red">Belum Diatur</small>' ?>
                                </td>
                                <td align="center">
                                    <?= strtoupper($row['role']) ?>
                                </td>
                                <td align="center">
                                    <button class="action-btn btn-edit"
                                        onclick="openEdit('<?= $row['id_pegawai'] ?>','<?= $row['nama_lengkap'] ?>','<?= $row['id_jabatan'] ?>','<?= $row['role'] ?>')">Edit</button>
                                    <a href="pegawai.php?hapus=<?= $row['id_pegawai'] ?>" class="action-btn btn-delete"
                                        onclick="return confirm('Hapus pegawai ini?')">Hapus</a>
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
            <h3>Tambah Pegawai Baru</h3>
            <hr>
            <form method="POST">
                <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" required></div>
                <div class="form-group"><label>Password Akun</label><input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Jabatan</label>
                    <select name="id_jabatan" required
                        style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                        <?php mysqli_data_seek($list_jabatan, 0);
                        while ($j = mysqli_fetch_assoc($list_jabatan)): ?>
                            <option value="<?= $j['id_jabatan'] ?>">
                                <?= $j['nama_jabatan'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Hak Akses</label>
                    <select name="role" required
                        style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                        <option value="pegawai">Pegawai</option>
                        <option value="hr">HR (Human Resource)</option>
                    </select>
                </div>
                <br>
                <button type="submit" name="tambah" class="btn-add" style="width: 100%;">Simpan & Generate Akun</button>
                <button type="button" onclick="closeModal('modalTambah')"
                    style="width: 100%; border:none; background:none; cursor:pointer; color:gray;">Batal</button>
            </form>
        </div>
    </div>

    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <h3>Edit Data Pegawai</h3>
            <hr>
            <form method="POST">
                <input type="hidden" name="id_pegawai" id="edit_id">
                <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" id="edit_nama"
                        required></div>
                <div class="form-group"><label>Ganti Password (Kosongkan jika tidak)</label><input type="password"
                        name="password"></div>
                <div class="form-group">
                    <label>Jabatan</label>
                    <select name="id_jabatan" id="edit_jabatan" required
                        style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                        <?php mysqli_data_seek($list_jabatan, 0);
                        while ($j = mysqli_fetch_assoc($list_jabatan)): ?>
                            <option value="<?= $j['id_jabatan'] ?>">
                                <?= $j['nama_jabatan'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Hak Akses</label>
                    <select name="role" id="edit_role" required
                        style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                        <option value="pegawai">Pegawai</option>
                        <option value="hr">HR</option>
                    </select>
                </div>
                <br>
                <button type="submit" name="edit" class="btn-add" style="width: 100%;">Update Data</button>
                <button type="button" onclick="closeModal('modalEdit')"
                    style="width: 100%; border:none; background:none; cursor:pointer; color:gray;">Batal</button>
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
    </script>

</body>

</html>