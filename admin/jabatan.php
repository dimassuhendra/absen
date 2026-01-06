<?php
include "../db_connect.php";

// Cek hak akses admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// --- LOGIKA CRUD (Tetap Sama) ---
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_jabatan']);
    mysqli_query($koneksi, "INSERT INTO jabatan (nama_jabatan) VALUES ('$nama')");
    header("Location: jabatan.php");
}

if (isset($_POST['edit'])) {
    $id = $_POST['id_jabatan'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_jabatan']);
    mysqli_query($koneksi, "UPDATE jabatan SET nama_jabatan='$nama' WHERE id_jabatan='$id'");
    header("Location: jabatan.php");
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struktur Jabatan |
        <?= $set['nama_perusahaan'] ?>
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />

    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --accent-color: <?= $set['warna_button'] ?>;
            --font-color: <?= $set['warna_font'] ?>;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fa;
            display: flex;
        }

        .main-content {
            margin-left: 320px;
            padding: 30px;
            width: 100%;
        }

        /* Card Styling */
        .data-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            border: none;
        }

        /* Table Styling */
        .table {
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .table thead th {
            border: none;
            color: #b2bec3;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
            padding: 15px 20px;
        }

        .table tbody tr {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            transition: all 0.2s;
        }

        .table tbody tr:hover {
            transform: scale(1.01);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .table tbody td {
            padding: 20px;
            border: none;
            vertical-align: middle;
        }

        .table tbody td:first-child {
            border-radius: 12px 0 0 12px;
        }

        .table tbody td:last-child {
            border-radius: 0 12px 12px 0;
        }

        /* Button Custom */
        .btn-custom-add {
            background-color: var(--accent-color);
            color: var(--font-color);
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-custom-add:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
            color: var(--font-color);
        }

        .action-icon-btn {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-edit-alt {
            background: #fff9db;
            color: #f1c40f;
        }

        .btn-delete-alt {
            background: #fff5f5;
            color: #ff7675;
        }

        .btn-edit-alt:hover {
            background: #f1c40f;
            color: white;
        }

        .btn-delete-alt:hover {
            background: #ff7675;
            color: white;
        }

        /* Modal Styling */
        .modal-content {
            border: none;
            border-radius: 20px;
            padding: 15px;
        }

        .modal-header {
            border: none;
        }

        .modal-footer {
            border: none;
        }

        .form-control-custom {
            background: #f8f9fa;
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 12px 15px;
            transition: 0.3s;
        }

        .form-control-custom:focus {
            background: white;
            border-color: var(--primary-color);
            box-shadow: none;
        }
    </style>
</head>

<body>

    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Master Struktur Jabatan</h4>
                <p class="text-muted small">Kelola hirarki dan posisi pegawai perusahaan</p>
            </div>
            <button class="btn btn-custom-add" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fa-solid fa-plus me-2"></i> Tambah Jabatan
            </button>
        </div>

        <div class="data-card">
            <div class="alert alert-info border-0 rounded-4 mb-4" style="background-color: #e3f2fd; color: #0d47a1;">
                <div class="d-flex">
                    <i class="fa-solid fa-circle-info mt-1 me-3 fs-5"></i>
                    <small>Daftar jabatan di bawah ini digunakan untuk mengelompokkan pegawai. Perubahan nama jabatan
                        akan otomatis terupdate pada profil pegawai yang bersangkutan.</small>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="80">ID</th>
                            <th>Nama Jabatan</th>
                            <th width="200" class="text-center">Aksi Pengelolaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        while ($row = mysqli_fetch_assoc($data_jabatan)): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted">
                                    <?= $no++; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light p-2 me-3"
                                            style="width: 40px; height: 40px; display:flex; align-items:center; justify-content:center;">
                                            <i class="fa-solid fa-briefcase text-secondary"></i>
                                        </div>
                                        <span class="fw-semibold" style="color: #2d3436;">
                                            <?= $row['nama_jabatan']; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="javascript:void(0)"
                                        onclick="openEdit('<?= $row['id_jabatan'] ?>', '<?= $row['nama_jabatan'] ?>')"
                                        class="action-icon-btn btn-edit-alt me-2" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="jabatan.php?hapus=<?= $row['id_jabatan']; ?>"
                                        class="action-icon-btn btn-delete-alt"
                                        onclick="return confirm('Hapus jabatan ini? Pegawai dengan jabatan ini akan kehilangan status jabatannya.')"
                                        title="Hapus">
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

    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header">
                    <h5 class="fw-bold">Tambah Jabatan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <label class="form-label small fw-bold text-muted">NAMA JABATAN</label>
                        <input type="text" name="nama_jabatan" class="form-control form-control-custom"
                            placeholder="Contoh: Senior Developer" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="tambah" class="btn btn-custom-add w-100">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header">
                    <h5 class="fw-bold">Ubah Nama Jabatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="id_jabatan" id="edit_id">
                    <div class="modal-body">
                        <label class="form-label small fw-bold text-muted">NAMA JABATAN BARU</label>
                        <input type="text" name="nama_jabatan" id="edit_nama" class="form-control form-control-custom"
                            required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="edit" class="btn btn-primary w-100 rounded-4 py-2"
                            style="background: var(--primary-color); border:none;">Update Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openEdit(id, nama) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            var myModal = new bootstrap.Modal(document.getElementById('modalEdit'));
            myModal.show();
        }
    </script>

</body>

</html>