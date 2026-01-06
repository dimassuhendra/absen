<?php
include "../db_connect.php";

// Cek hak akses admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// --- LOGIKA CRUD PEGAWAI (Tetap Sama) ---
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

if (isset($_POST['edit'])) {
    $id = $_POST['id_pegawai'];
    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $jabatan = $_POST['id_jabatan'];
    $role = $_POST['role'];
    mysqli_query($koneksi, "UPDATE pegawai SET nip='$nip', nama_lengkap='$nama', email='$email', id_jabatan='$jabatan', role='$role' WHERE id_pegawai='$id'");
    if (!empty($_POST['password'])) {
        $password_baru = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE pegawai SET password='$password_baru' WHERE id_pegawai='$id'");
    }
    header("Location: pegawai.php?pesan=update_berhasil");
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM pegawai WHERE id_pegawai='$id'");
    header("Location: pegawai.php");
}

$list_jabatan = mysqli_query($koneksi, "SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
$query_pegawai = "SELECT pegawai.*, jabatan.nama_jabatan FROM pegawai LEFT JOIN jabatan ON pegawai.id_jabatan = jabatan.id_jabatan ORDER BY pegawai.id_pegawai DESC";
$data_pegawai = mysqli_query($koneksi, $query_pegawai);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pegawai |
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

        /* Card & Table Custom */
        .data-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            border: none;
        }

        .table thead th {
            background: transparent;
            color: #b2bec3;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            border: none;
            padding: 15px 20px;
        }

        .table tbody tr {
            border-bottom: 1px solid #f8f9fa;
            transition: 0.2s;
        }

        .table tbody tr:hover {
            background-color: #fcfdfe;
        }

        .table tbody td {
            padding: 18px 20px;
            vertical-align: middle;
            border: none;
        }

        /* Avatar & Info */
        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #e3f2fd;
            color: #1e88e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Badge Role */
        .badge-role {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
        }

        .bg-admin {
            background: #eee0ff;
            color: #8e44ad;
        }

        .bg-hr {
            background: #e0f2ff;
            color: #2980b9;
        }

        .bg-pegawai {
            background: #e2f9eb;
            color: #27ae60;
        }

        /* Button Custom */
        .btn-custom-add {
            background-color: var(--accent-color);
            color: var(--font-color);
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-edit-alt {
            background: #fff9db;
            color: #f1c40f;
            border: none;
        }

        .btn-delete-alt {
            background: #fff5f5;
            color: #ff7675;
        }

        /* Modal & Form */
        .modal-content {
            border-radius: 24px;
            border: none;
            padding: 10px;
        }

        .form-control-custom {
            background: #f8f9fa;
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 10px 15px;
            font-size: 0.9rem;
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
                <h4 class="fw-bold mb-0">Manajemen Akun Pegawai</h4>
                <p class="text-muted small">Kelola aksesibilitas dan kredensial karyawan</p>
            </div>
            <button class="btn btn-custom-add shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fa-solid fa-user-plus me-2"></i> Tambah Pegawai
            </button>
        </div>

        <div class="data-card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pegawai</th>
                            <th>NIP</th>
                            <th>Jabatan</th>
                            <th>Role Akses</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($data_pegawai)):
                            $initials = strtoupper(substr($row['nama_lengkap'], 0, 2));
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3">
                                            <?= $initials ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                                <?= $row['nama_lengkap']; ?>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.8rem;">
                                                <?= $row['email']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border fw-normal p-2 rounded-3">
                                        <?= $row['nip']; ?>
                                    </span></td>
                                <td>
                                    <span class="text-secondary" style="font-size: 0.9rem;">
                                        <?= $row['nama_jabatan'] ?? '<span class="text-danger">Non-Aktif</span>'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-role bg-<?= $row['role']; ?>">
                                        <i class="fa-solid fa-circle me-1" style="font-size: 0.4rem;"></i>
                                        <?= strtoupper($row['role']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="action-btn btn-edit-alt me-1"
                                        onclick="openEdit('<?= $row['id_pegawai'] ?>','<?= $row['nip'] ?>','<?= $row['nama_lengkap'] ?>','<?= $row['email'] ?>','<?= $row['id_jabatan'] ?>','<?= $row['role'] ?>')">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <a href="pegawai.php?hapus=<?= $row['id_pegawai']; ?>" class="action-btn btn-delete-alt"
                                        onclick="return confirm('Hapus akun ini?')">
                                        <i class="fa-solid fa-trash"></i>
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
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="fw-bold">Tambah Akun Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="alert alert-warning border-0 small py-2 rounded-3">
                            <i class="fa-solid fa-magic-wand-sparkles me-2"></i> NIP & Email dibuat otomatis oleh
                            sistem.
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">NAMA LENGKAP</label>
                            <input type="text" name="nama" class="form-control form-control-custom"
                                placeholder="Nama tanpa gelar" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">PASSWORD AWAL</label>
                            <input type="password" name="password" class="form-control form-control-custom"
                                placeholder="Min. 6 Karakter" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">JABATAN</label>
                                <select name="id_jabatan" class="form-select form-control-custom" required>
                                    <?php mysqli_data_seek($list_jabatan, 0);
                                    while ($j = mysqli_fetch_assoc($list_jabatan)): ?>
                                        <option value="<?= $j['id_jabatan'] ?>">
                                            <?= $j['nama_jabatan'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">ROLE AKSES</label>
                                <select name="role" class="form-select form-control-custom" required>
                                    <option value="pegawai">Pegawai</option>
                                    <option value="hr">HR (Human Resource)</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" name="tambah" class="btn btn-custom-add w-100 py-2">Daftarkan
                            Pegawai</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="fw-bold">Update Data Pegawai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="id_pegawai" id="edit_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">NIP</label>
                                <input type="text" name="nip" id="edit_nip" class="form-control form-control-custom"
                                    readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">ROLE</label>
                                <select name="role" id="edit_role" class="form-select form-control-custom">
                                    <option value="pegawai">Pegawai</option>
                                    <option value="hr">HR</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">NAMA LENGKAP</label>
                            <input type="text" name="nama" id="edit_nama" class="form-control form-control-custom"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">EMAIL KANTOR</label>
                            <input type="email" name="email" id="edit_email" class="form-control form-control-custom"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">JABATAN</label>
                            <select name="id_jabatan" id="edit_jabatan" class="form-select form-control-custom">
                                <?php mysqli_data_seek($list_jabatan, 0);
                                while ($j = mysqli_fetch_assoc($list_jabatan)): ?>
                                    <option value="<?= $j['id_jabatan'] ?>">
                                        <?= $j['nama_jabatan'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted text-danger">GANTI PASSWORD
                                (OPSIONAL)</label>
                            <input type="password" name="password" class="form-control form-control-custom"
                                placeholder="Isi hanya jika ingin ganti password">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" name="edit" class="btn btn-primary w-100 py-2 rounded-4"
                            style="background:var(--primary-color); border:none;">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openEdit(id, nip, nama, email, jabatan, role) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nip').value = nip;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_jabatan').value = jabatan;
            document.getElementById('edit_role').value = role;
            new bootstrap.Modal(document.getElementById('modalEdit')).show();
        }
    </script>
</body>

</html>