<?php
include "../db_connect.php";

// Cek hak akses admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// --- LOGIKA CRUD PEGAWAI ---

// 1. Tambah Pegawai
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jabatan = $_POST['id_jabatan'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // --- GENERATE NIP OTOMATIS (Format: ddmmyyxxx -> 050126001) ---
    $tgl_sekarang = date('dmy'); // Menghasilkan 050126

    // Ambil ID terakhir untuk menentukan nomor urut
    $res_id = mysqli_query($koneksi, "SELECT id_pegawai FROM pegawai ORDER BY id_pegawai DESC LIMIT 1");
    $last_id = mysqli_fetch_assoc($res_id);
    $next_num = ($last_id) ? $last_id['id_pegawai'] + 1 : 1;

    // Gabungkan tanggal dengan nomor urut 3 digit (001, 002, dst)
    $nip = $tgl_sekarang . str_pad($next_num, 3, "0", STR_PAD_LEFT);

    // --- GENERATE EMAIL OTOMATIS ---
    $domain_raw = str_replace(' ', '', strtolower($set['nama_perusahaan']));
    $nama_clean = str_replace(' ', '', strtolower($nama));
    $email = $nama_clean . "@" . $domain_raw . ".com";

    $query = "INSERT INTO pegawai (nip, nama_lengkap, email, password, id_jabatan, role) 
              VALUES ('$nip', '$nama', '$email', '$password', '$jabatan', '$role')";

    if (mysqli_query($koneksi, $query)) {
        header("Location: pegawai.php?pesan=berhasil");
    }
}

// 2. Edit Pegawai (Termasuk Email & Password)
if (isset($_POST['edit'])) {
    $id = $_POST['id_pegawai'];
    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $jabatan = $_POST['id_jabatan'];
    $role = $_POST['role'];

    // Update data dasar
    $query = "UPDATE pegawai SET nip='$nip', nama_lengkap='$nama', email='$email', id_jabatan='$jabatan', role='$role' WHERE id_pegawai='$id'";
    mysqli_query($koneksi, $query);

    // Jika password diisi, maka update password
    if (!empty($_POST['password'])) {
        $password_baru = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE pegawai SET password='$password_baru' WHERE id_pegawai='$id'");
    }

    header("Location: pegawai.php?pesan=update_berhasil");
}

// 3. Hapus Pegawai
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM pegawai WHERE id_pegawai='$id'");
    header("Location: pegawai.php");
}

// Ambil data Jabatan untuk dropdown
$list_jabatan = mysqli_query($koneksi, "SELECT * FROM jabatan ORDER BY nama_jabatan ASC");

// Ambil data Pegawai dengan Join ke Jabatan
$query_pegawai = "SELECT pegawai.*, jabatan.nama_jabatan 
                  FROM pegawai 
                  LEFT JOIN jabatan ON pegawai.id_jabatan = jabatan.id_jabatan 
                  ORDER BY pegawai.id_pegawai DESC";
$data_pegawai = mysqli_query($koneksi, $query_pegawai);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Pegawai |
        <?= $set['nama_perusahaan'] ?>
    </title>
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

        .badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            color: white;
        }

        .bg-admin {
            background: #8e44ad;
        }

        .bg-hr {
            background: #2980b9;
        }

        .bg-pegawai {
            background: #27ae60;
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
            <?php include "header.php"; ?>

            <div class="content-body">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>Manajemen Akun Pegawai</h2>
                    <button class="btn-add" onclick="showModal('modalTambah')">+ Tambah Akun</button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>NIP</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Jabatan</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($data_pegawai)): ?>
                            <tr>
                                <td>
                                    <?= $row['nip']; ?>
                                </td>
                                <td><b>
                                        <?= $row['nama_lengkap']; ?>
                                    </b></td>
                                <td>
                                    <?= $row['email']; ?>
                                </td>
                                <td>
                                    <?= $row['nama_jabatan'] ?? '<small style="color:red">Belum diatur</small>'; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $row['role']; ?>">
                                        <?= strtoupper($row['role']); ?>
                                    </span>
                                </td>
                                <td align="center">
                                    <button class="action-btn btn-edit"
                                        onclick="openEdit('<?= $row['id_pegawai'] ?>','<?= $row['nip'] ?>','<?= $row['nama_lengkap'] ?>','<?= $row['email'] ?>','<?= $row['id_jabatan'] ?>','<?= $row['role'] ?>')">Edit</button>
                                    <a href="pegawai.php?hapus=<?= $row['id_pegawai']; ?>" class="action-btn btn-delete"
                                        onclick="return confirm('Hapus akun ini?')">Hapus</a>
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
            <p><small><i>NIP dan Email akan dibuat otomatis oleh sistem.</i></small></p>
            <hr>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Lengkap Pegawai</label>
                    <input type="text" name="nama" placeholder="Contoh: Budi Santoso" required>
                </div>
                <div class="form-group">
                    <label>Password Awal</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Jabatan</label>
                    <select name="id_jabatan" required
                        style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                        <?php mysqli_data_seek($list_jabatan, 0);
                        while ($j = mysqli_fetch_assoc($list_jabatan)): ?>
                            <option value="<?= $j['id_jabatan'] ?>"><?= $j['nama_jabatan'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Role Access</label>
                    <select name="role" required
                        style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                        <option value="pegawai">Pegawai</option>
                        <option value="hr">HR</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <br>
                <button type="submit" name="tambah" class="btn-add" style="width: 100%;">Generate Akun & Simpan</button>
                <button type="button" onclick="closeModal('modalTambah')"
                    style="width: 100%; border:none; background:none; cursor:pointer; color:gray;">Batal</button>
            </form>
        </div>
    </div>

    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <h3>Edit Akun Pegawai</h3>
            <hr>
            <form method="POST">
                <input type="hidden" name="id_pegawai" id="edit_id">
                <div class="form-group"><label>NIP</label><input type="text" name="nip" id="edit_nip" required></div>
                <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" id="edit_nama"
                        required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" id="edit_email" required>
                </div>
                <div class="form-group"><label>Password Baru (Kosongkan jika tidak diubah)</label><input type="password"
                        name="password" placeholder="******"></div>
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
                    <label>Role Access</label>
                    <select name="role" id="edit_role" required
                        style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;">
                        <option value="pegawai">Pegawai</option>
                        <option value="hr">HR</option>
                        <option value="admin">Admin</option>
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
        function openEdit(id, nip, nama, email, jabatan, role) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nip').value = nip;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_jabatan').value = jabatan;
            document.getElementById('edit_role').value = role;
            showModal('modalEdit');
        }
    </script>

</body>

</html>