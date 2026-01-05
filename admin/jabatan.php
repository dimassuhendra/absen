<?php
include "../db_connect.php";

// Cek hak akses admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// --- LOGIKA CRUD SEDERHANA ---

// 1. Tambah Jabatan
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_jabatan']);
    $query = "INSERT INTO jabatan (nama_jabatan) VALUES ('$nama')";
    mysqli_query($koneksi, $query);
    header("Location: jabatan.php");
}

// 2. Edit Jabatan
if (isset($_POST['edit'])) {
    $id = $_POST['id_jabatan'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_jabatan']);
    $query = "UPDATE jabatan SET nama_jabatan='$nama' WHERE id_jabatan='$id'";
    mysqli_query($koneksi, $query);
    header("Location: jabatan.php");
}

// 3. Hapus Jabatan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM jabatan WHERE id_jabatan='$id'");
    header("Location: jabatan.php");
}

$data_jabatan = mysqli_query($koneksi, "SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Struktur Jabatan | <?= $set['nama_perusahaan'] ?></title>
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
            font-size: 12px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background: #f1c40f;
            margin-right: 5px;
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
        }

        .modal-content {
            background: white;
            margin: 15% auto;
            padding: 25px;
            width: 350px;
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
                    <h2>Master Struktur Jabatan</h2>
                    <button class="btn-add" onclick="showModal('modalTambah')">+ Tambah Jabatan Baru</button>
                </div>

                <p><i>Halaman ini digunakan untuk mengatur daftar jabatan yang tersedia di perusahaan. Gaji dan
                        tunjangan akan diatur oleh bagian HR.</i></p>

                <table>
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Nama Jabatan</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        while ($row = mysqli_fetch_assoc($data_jabatan)): ?>
                            <tr>
                                <td align="center"><?= $no++; ?></td>
                                <td><strong><?= $row['nama_jabatan']; ?></strong></td>
                                <td align="center">
                                    <button class="action-btn btn-edit"
                                        onclick="openEdit('<?= $row['id_jabatan'] ?>', '<?= $row['nama_jabatan'] ?>')">Edit</button>
                                    <a href="jabatan.php?hapus=<?= $row['id_jabatan']; ?>" class="action-btn btn-delete"
                                        onclick="return confirm('Hapus jabatan ini?')">Hapus</a>
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
            <h3>Tambah Jabatan</h3>
            <hr>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Jabatan</label>
                    <input type="text" name="nama_jabatan" placeholder="Contoh: Manager Operasional" required>
                </div>
                <br>
                <button type="submit" name="tambah" class="btn-add" style="width: 100%;">Simpan Jabatan</button>
                <button type="button" onclick="closeModal('modalTambah')"
                    style="width: 100%; background:none; border:none; color:gray; cursor:pointer;">Batal</button>
            </form>
        </div>
    </div>

    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <h3>Ubah Nama Jabatan</h3>
            <hr>
            <form method="POST">
                <input type="hidden" name="id_jabatan" id="edit_id">
                <div class="form-group">
                    <label>Nama Jabatan</label>
                    <input type="text" name="nama_jabatan" id="edit_nama" required>
                </div>
                <br>
                <button type="submit" name="edit" class="btn-add" style="width: 100%;">Update Nama</button>
                <button type="button" onclick="closeModal('modalEdit')"
                    style="width: 100%; background:none; border:none; color:gray; cursor:pointer;">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function showModal(id) { document.getElementById(id).style.display = 'block'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        function openEdit(id, nama) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            showModal('modalEdit');
        }
    </script>

</body>

</html>