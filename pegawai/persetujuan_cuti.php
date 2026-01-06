<?php
include "../db_connect.php";

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek hak akses: Harus sudah login
if (!isset($_SESSION['id_pegawai'])) {
    header("Location: ../login.php");
    exit();
}

$id_user_login = $_SESSION['id_pegawai'];
$set = getSetting($koneksi);

// 1. Validasi: Apakah user ini benar-benar Kepala Departemen?
$cek_kepala = mysqli_query($koneksi, "SELECT * FROM departemen WHERE id_kepala = '$id_user_login'");
if (mysqli_num_rows($cek_kepala) == 0) {
    echo "<script>alert('Akses Ditolak! Anda bukan Kepala Departemen.'); window.location='index.php';</script>";
    exit();
}

$dept_pimpinan = mysqli_fetch_assoc($cek_kepala);
$id_dept = $dept_pimpinan['id_departemen'];
$nama_dept = $dept_pimpinan['nama_departemen'];

// 2. LOGIKA PROSES PERSETUJUAN (UPDATE STATUS)
if (isset($_POST['aksi'])) {
    $id_cuti = $_POST['id_cuti'];
    $status_baru = $_POST['status']; // 'disetujui' atau 'ditolak'
    
    // Update status_atasan di tabel pengajuan_cuti
    $update = mysqli_query($koneksi, "UPDATE pengajuan_cuti SET status_atasan = '$status_baru' WHERE id_cuti = '$id_cuti'");
    
    if ($update) {
        header("Location: persetujuan_cuti.php?pesan=berhasil");
    } else {
        header("Location: persetujuan_cuti.php?pesan=gagal");
    }
}

// 3. Ambil daftar pengajuan anggota di departemen yang sama
// Hanya mengambil yang diajukan oleh orang lain (bukan diri sendiri)
$query_pengajuan = "SELECT pc.*, p.nama_lengkap, j.nama_jabatan 
                    FROM pengajuan_cuti pc
                    JOIN pegawai p ON pc.id_pegawai = p.id_pegawai
                    JOIN jabatan j ON p.id_jabatan = j.id_jabatan
                    WHERE j.id_departemen = '$id_dept' 
                    AND pc.id_pegawai != '$id_user_login'
                    ORDER BY pc.tgl_mulai DESC";
$data_pengajuan = mysqli_query($koneksi, $query_pengajuan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Persetujuan Cuti Divisi <?= $nama_dept ?> | <?= $set['nama_perusahaan'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root { --primary: <?= $set['warna_header'] ?>; --accent: <?= $set['warna_button'] ?>; }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; }
        .main-content { margin-left: 300px; padding: 40px; }
        .card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .table-custom { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-custom th { text-align: left; padding: 15px; background: #f8f9fa; color: #636e72; font-size: 13px; border-bottom: 2px solid #eee; }
        .table-custom td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-disetujui { background: #d4edda; color: #155724; }
        .badge-ditolak { background: #f8d7da; color: #721c24; }
        .btn-action { padding: 8px 15px; border-radius: 8px; border: none; cursor: pointer; font-size: 12px; font-weight: 600; transition: 0.2s; }
        .btn-acc { background: #2ecc71; color: white; margin-right: 5px; }
        .btn-reject { background: #e74c3c; color: white; }
        .btn-action:hover { opacity: 0.8; transform: scale(1.05); }
    </style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="main-content">
    <div style="margin-bottom: 30px;">
        <h2 style="font-weight: 700; color: #2d3436; margin: 0;">Persetujuan Cuti Tim</h2>
        <p style="color: #636e72; margin: 5px 0;">Divisi: <strong><?= $nama_dept ?></strong></p>
    </div>

    <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil'): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 14px;">
            <i class="fa-solid fa-circle-check"></i> Status pengajuan berhasil diperbarui.
        </div>
    <?php endif; ?>

    <div class="card">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Pegawai</th>
                    <th>Jenis</th>
                    <th>Tanggal</th>
                    <th>Alasan</th>
                    <th>Status Atasan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($data_pengajuan) == 0): ?>
                <tr>
                    <td colspan="6" align="center" style="color: #ccc; padding: 30px;">Tidak ada pengajuan cuti dari anggota tim.</td>
                </tr>
                <?php endif; ?>

                <?php while ($row = mysqli_fetch_assoc($data_pengajuan)): ?>
                <tr>
                    <td>
                        <strong><?= $row['nama_lengkap'] ?></strong><br>
                        <small style="color: #b2bec3;"><?= $row['nama_jabatan'] ?></small>
                    </td>
                    <td><span style="text-transform: capitalize;"><?= $row['jenis_pengajuan'] ?></span></td>
                    <td>
                        <small><?= date('d M Y', strtotime($row['tgl_mulai'])) ?> s/d <?= date('d M Y', strtotime($row['tgl_selesai'])) ?></small>
                    </td>
                    <td><?= $row['alasan'] ?></td>
                    <td>
                        <span class="badge badge-<?= $row['status_atasan'] ?>">
                            <?= $row['status_atasan'] ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($row['status_atasan'] == 'pending'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id_cuti" value="<?= $row['id_cuti'] ?>">
                                <input type="hidden" name="status" value="disetujui">
                                <button type="submit" name="aksi" class="btn-action btn-acc" onclick="return confirm('Setujui pengajuan ini?')">
                                    <i class="fa-solid fa-check"></i> Acc
                                </button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id_cuti" value="<?= $row['id_cuti'] ?>">
                                <input type="hidden" name="status" value="ditolak">
                                <button type="submit" name="aksi" class="btn-action btn-reject" onclick="return confirm('Tolak pengajuan ini?')">
                                    <i class="fa-solid fa-xmark"></i> Tolak
                                </button>
                            </form>
                        <?php else: ?>
                            <small style="color: #ccc;">Selesai diproses</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>