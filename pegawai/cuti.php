<?php
include "../db_connect.php";

// Cek hak akses pegawai
if ($_SESSION['role'] != 'pegawai') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);
$id_pegawai = $_SESSION['id_user'];
$error_message = ""; 

// --- FITUR BATALKAN PENGAJUAN ---
if (isset($_GET['cancel'])) {
    $id_batal = $_GET['cancel'];
    // Keamanan: Pastikan hanya bisa membatalkan milik sendiri dan masih status pending atasan
    $query_batal = "DELETE FROM pengajuan_cuti WHERE id_cuti = '$id_batal' AND id_pegawai = '$id_pegawai' AND status_atasan = 'pending'";
    if (mysqli_query($koneksi, $query_batal)) {
        header("Location: cuti.php?status=cancelled");
        exit();
    }
}

// --- HITUNG KUOTA TAHUNAN (12 HARI) ---
$tahun_ini = date('Y');
$query_kuota = "SELECT sisa_jatah FROM kuota_cuti WHERE id_pegawai = '$id_pegawai' AND tahun = '$tahun_ini'";
$exec_kuota = mysqli_query($koneksi, $query_kuota);
$data_kuota = mysqli_fetch_assoc($exec_kuota);
$sisa_cuti = $data_kuota ? $data_kuota['sisa_jatah'] : 12;

// --- LOGIKA PROSES PENGAJUAN ---
if (isset($_POST['ajukan'])) {
    $jenis = $_POST['jenis_pengajuan'];
    $mulai = $_POST['tgl_mulai'];
    $selesai = $_POST['tgl_selesai'];
    $alasan = mysqli_real_escape_string($koneksi, $_POST['alasan']);

    // Hitung durasi hari
    $durasi = (strtotime($selesai) - strtotime($mulai)) / (60 * 60 * 24) + 1;

    if ($jenis == 'Cuti' && $durasi > $sisa_cuti) {
        $error_message = "Sisa jatah cuti Anda tidak cukup ($sisa_cuti hari).";
    }

    if (empty($error_message)) {
        $query = "INSERT INTO pengajuan_cuti (id_pegawai, jenis_pengajuan, tgl_mulai, tgl_selesai, alasan, status_atasan, status) 
                  VALUES ('$id_pegawai', '$jenis', '$mulai', '$selesai', '$alasan', 'pending', 'pending')";

        if (mysqli_query($koneksi, $query)) {
            header("Location: cuti.php?status=sent");
            exit();
        }
    }
}

$riwayat = mysqli_query($koneksi, "SELECT * FROM pengajuan_cuti WHERE id_pegawai = '$id_pegawai' ORDER BY id_cuti DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuti & Izin | <?= $set['nama_perusahaan'] ?></title>
    
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
        .card-custom { background: white; border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.02); padding: 30px; }
        .section-title { font-weight: 700; color: #2d3436; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .form-label { font-size: 0.8rem; font-weight: 600; color: #b2bec3; text-transform: uppercase; }
        .form-control-custom { background: #f8f9fa; border: 2px solid transparent; border-radius: 12px; padding: 12px; transition: 0.3s; width: 100%; }
        .form-control-custom:focus { background: white; border-color: var(--primary-color); outline: none; }

        .badge-status { padding: 6px 12px; border-radius: 8px; font-size: 0.65rem; font-weight: 700; display: inline-block; }
        .status-pending { background: #fff9e6; color: #f39c12; border: 1px solid #f39c12; } /* Menunggu Atasan */
        .status-pending-hr { background: #eef2ff; color: #4f46e5; border: 1px solid #4f46e5; } /* Menunggu HR */
        .status-disetujui { background: #e6f9f0; color: #27ae60; border: 1px solid #27ae60; }
        .status-ditolak { background: #feebeb; color: #e74c3c; border: 1px solid #e74c3c; }

        .btn-submit { background: var(--accent-color); color: var(--text-on-accent); border: none; padding: 15px; border-radius: 12px; width: 100%; font-weight: 600; transition: 0.3s; }
        .btn-cancel { color: #e74c3c; border: none; background: none; font-size: 0.8rem; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .btn-cancel:hover { color: #c0392b; }
    </style>
</head>
<body>

    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="mb-4 d-flex justify-content-between align-items-end">
            <div>
                <h4 class="fw-bold mb-0">📅 Pengajuan Cuti & Izin</h4>
                <p class="text-muted small mb-0">Kelola jatah cuti tahunan Anda di sini.</p>
            </div>
            <div class="text-end">
                <span class="text-muted small d-block">Sisa Jatah Cuti Tahun <?= $tahun_ini ?></span>
                <h3 class="fw-bold text-primary mb-0"><?= $sisa_cuti ?> <small style="font-size: 14px">Hari</small></h3>
            </div>
        </div>

        <?php if($error_message): ?>
            <div class="alert alert-danger rounded-4 border-0 shadow-sm"><?= $error_message ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card-custom">
                    <h5 class="section-title"><i class="fa-solid fa-pen-to-square text-primary"></i> Buat Pengajuan</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">JENIS PENGAJUAN</label>
                            <select name="jenis_pengajuan" class="form-control-custom form-select" required>
                                <option value="Cuti">Cuti Tahunan</option>
                                <option value="Izin">Izin (Keperluan Mendesak)</option>
                                <option value="Sakit">Sakit</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">MULAI</label>
                                <input type="date" name="tgl_mulai" class="form-control-custom" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">SELESAI</label>
                                <input type="date" name="tgl_selesai" class="form-control-custom" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ALASAN</label>
                            <textarea name="alasan" rows="3" class="form-control-custom" placeholder="Tulis alasan..." required></textarea>
                        </div>
                        <button type="submit" name="ajukan" class="btn-submit">
                            <i class="fa-solid fa-paper-plane me-2"></i> Kirim Pengajuan
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card-custom">
                    <h5 class="section-title"><i class="fa-solid fa-clock-rotate-left text-primary"></i> Riwayat & Status</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="text-muted small">
                                    <th>JENIS & TANGGAL</th>
                                    <th>ALASAN</th>
                                    <th class="text-center">STATUS SAAT INI</th>
                                    <th class="text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($riwayat)): 
                                    $label = ""; $kelas = ""; $bisa_batal = false;

                                    if ($row['status_atasan'] == 'pending') {
                                        $label = "PENDING ATASAN"; $kelas = "status-pending"; $bisa_batal = true;
                                    } elseif ($row['status_atasan'] == 'disetujui' && $row['status'] == 'pending') {
                                        $label = "PENDING HR"; $kelas = "status-pending-hr";
                                    } elseif ($row['status_atasan'] == 'disetujui' && $row['status'] == 'disetujui') {
                                        $label = "DISETUJUI"; $kelas = "status-disetujui";
                                    } else {
                                        $label = "DITOLAK"; $kelas = "status-ditolak";
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= $row['jenis_pengajuan'] ?></div>
                                        <small class="text-muted"><?= date('d/m/y', strtotime($row['tgl_mulai'])) ?> - <?= date('d/m/y', strtotime($row['tgl_selesai'])) ?></small>
                                    </td>
                                    <td><span class="text-muted small"><?= $row['alasan'] ?></span></td>
                                    <td class="text-center">
                                        <span class="badge-status <?= $kelas ?>"><?= $label ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if($bisa_batal): ?>
                                            <a href="javascript:void(0)" onclick="confirmCancel(<?= $row['id_cuti'] ?>)" class="btn-cancel">
                                                <i class="fa-solid fa-trash-can"></i> Batal
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <script>
        function confirmCancel(id) {
            Swal.fire({
                title: 'Batalkan Pengajuan?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#b2bec3',
                confirmButtonText: 'Ya, Batalkan!',
                cancelButtonText: 'Tutup'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'cuti.php?cancel=' + id;
                }
            })
        }
    </script>

    <?php if(isset($_GET['status'])): ?>
    <script>
        const status = '<?= $_GET['status'] ?>';
        if(status === 'sent') Swal.fire('Berhasil!', 'Pengajuan terkirim ke Atasan.', 'success');
        if(status === 'cancelled') Swal.fire('Dibatalkan!', 'Pengajuan telah dihapus.', 'info');
    </script>
    <?php endif; ?>
</body>
</html>