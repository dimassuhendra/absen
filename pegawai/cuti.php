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

// --- HITUNG KUOTA BULAN INI UNTUK JAVASCRIPT & VALIDASI PHP ---
$bulan_ini = date('m');
$tahun_ini = date('Y');

$query_kuota = "SELECT COUNT(*) as total FROM pengajuan_cuti 
                WHERE id_pegawai = '$id_pegawai' 
                AND (jenis_pengajuan = 'Cuti' OR jenis_pengajuan = 'Izin')
                AND MONTH(tgl_mulai) = '$bulan_ini' 
                AND YEAR(tgl_mulai) = '$tahun_ini'
                AND status != 'Ditolak'";
$exec_kuota = mysqli_query($koneksi, $query_kuota);
$data_kuota = mysqli_fetch_assoc($exec_kuota);
$total_pakai = $data_kuota['total'];

// --- LOGIKA PROSES PENGAJUAN (BACKEND) ---
if (isset($_POST['ajukan'])) {
    $jenis = $_POST['jenis_pengajuan'];
    $mulai = $_POST['tgl_mulai'];
    $selesai = $_POST['tgl_selesai'];
    $alasan = mysqli_real_escape_string($koneksi, $_POST['alasan']);

    // Validasi Sisi Server (Keamanan Ganda)
    if (($jenis == 'Cuti' || $jenis == 'Izin') && $total_pakai >= 2) {
        $error_message = "Maaf, kuota Izin & Cuti Anda bulan ini sudah habis.";
    }

    if (empty($error_message)) {
        $query = "INSERT INTO pengajuan_cuti (id_pegawai, jenis_pengajuan, tgl_mulai, tgl_selesai, alasan, status) 
                  VALUES ('$id_pegawai', '$jenis', '$mulai', '$selesai', '$alasan', 'Pending')";

        if (mysqli_query($koneksi, $query)) {
            header("Location: cuti.php?status=sent");
            exit();
        }
    }
}

// Ambil riwayat
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

        .card-custom {
            background: white; border-radius: 20px; border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02); padding: 30px;
            height: 100%;
        }

        .section-title { font-weight: 700; color: #2d3436; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }

        .form-label { font-size: 0.8rem; font-weight: 600; color: #b2bec3; text-transform: uppercase; margin-bottom: 8px; }
        .form-control-custom {
            background: #f8f9fa; border: 2px solid transparent;
            border-radius: 12px; padding: 12px 15px; transition: 0.3s;
            width: 100%;
        }
        .form-control-custom:focus { background: white; border-color: var(--primary-color); outline: none; box-shadow: none; }

        .badge-status { padding: 8px 15px; border-radius: 10px; font-size: 0.75rem; font-weight: 600; }
        .status-pending { background: #fff9e6; color: #f39c12; }
        .status-disetujui { background: #e6f9f0; color: #27ae60; }
        .status-ditolak { background: #feebeb; color: #e74c3c; }

        .btn-submit {
            background: var(--accent-color); color: var(--text-on-accent);
            border: none; padding: 15px; border-radius: 12px; width: 100%;
            font-weight: 600; margin-top: 15px; transition: 0.3s;
        }
        .btn-submit:hover:not(:disabled) { opacity: 0.9; transform: translateY(-2px); }
        .btn-submit:disabled { background: #dfe6e9; color: #b2bec3; cursor: not-allowed; }
    </style>
</head>
<body>

    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="mb-4">
            <h4 class="fw-bold mb-0">📅 Pengajuan Cuti & Izin</h4>
            <p class="text-muted small">Maksimal 2x pengajuan Cuti/Izin per bulan. Sakit tidak terbatas.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card-custom">
                    <h5 class="section-title"><i class="fa-solid fa-pen-to-square text-primary"></i> Buat Pengajuan</h5>
                    <form method="POST" id="formCuti">
                        <div class="mb-3">
                            <label class="form-label">JENIS PENGAJUAN</label>
                            <select name="jenis_pengajuan" id="jenis_pengajuan" class="form-control-custom form-select" required>
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
                            <label class="form-label">ALASAN / KETERANGAN</label>
                            <textarea name="alasan" rows="4" class="form-control-custom" placeholder="Tulis alasan..." required></textarea>
                        </div>
                        <button type="submit" name="ajukan" id="btnSubmit" class="btn-submit">
                            <i class="fa-solid fa-paper-plane me-2"></i> Kirim Pengajuan
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card-custom">
                    <h5 class="section-title"><i class="fa-solid fa-clock-rotate-left text-primary"></i> Riwayat Anda</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Jenis</th>
                                    <th>Tanggal</th>
                                    <th>Alasan</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($riwayat)): ?>
                                <tr>
                                    <td><span class="fw-bold"><?= $row['jenis_pengajuan'] ?></span></td>
                                    <td>
                                        <div class="small fw-semibold"><?= date('d/m/y', strtotime($row['tgl_mulai'])) ?></div>
                                        <div class="small text-muted">s/d <?= date('d/m/y', strtotime($row['tgl_selesai'])) ?></div>
                                    </td>
                                    <td><span class="text-muted small"><?= $row['alasan'] ?></span></td>
                                    <td class="text-center">
                                        <span class="badge-status status-<?= strtolower($row['status']) ?>">
                                            <?= strtoupper($row['status']) ?>
                                        </span>
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
        const jenisInput = document.getElementById('jenis_pengajuan');
        const btnSubmit = document.getElementById('btnSubmit');
        const totalPakai = <?= $total_pakai ?>;

        // Peringatan Real-time saat memilih jenis
        jenisInput.addEventListener('change', function() {
            if ((this.value === 'Cuti' || this.value === 'Izin') && totalPakai >= 2) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kuota Habis',
                    text: 'Anda sudah mengajukan Cuti/Izin sebanyak 2x bulan ini. Hanya pengajuan SAKIT yang diperbolehkan.',
                    confirmButtonColor: '<?= $set['warna_header'] ?>'
                });
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fa-solid fa-ban me-2"></i> Kuota Habis';
            } else {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i> Kirim Pengajuan';
            }
        });

        // Jalankan saat load untuk cek default value (Cuti)
        window.onload = () => { jenisInput.dispatchEvent(new Event('change')); };
    </script>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'sent'): ?>
    <script>
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Pengajuan telah dikirim.', showConfirmButton: false, timer: 2000 });
    </script>
    <?php endif; ?>
</body>
</html>