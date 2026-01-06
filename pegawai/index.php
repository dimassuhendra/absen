<?php
// Set timezone di paling atas agar semua fungsi date() otomatis menggunakan WIB
date_default_timezone_set('Asia/Jakarta');
include "../db_connect.php";

if ($_SESSION['role'] != 'pegawai') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);
$id_pegawai = $_SESSION['id_user'];
$tgl_hari_ini = date('Y-m-d');

// Cek status absen hari ini
$cek_absen = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_pegawai = '$id_pegawai' AND tanggal = '$tgl_hari_ini'");
$data_absen = mysqli_fetch_assoc($cek_absen);

// Proses Absen
if (isset($_POST['absen'])) {
    $jam_sekarang = date('H:i:s');
    if (!$data_absen) {
        // Absen Masuk
        mysqli_query($koneksi, "INSERT INTO absensi (id_pegawai, tanggal, jam_masuk, keterangan) VALUES ('$id_pegawai', '$tgl_hari_ini', '$jam_sekarang', 'Hadir')");
    } else if ($data_absen['jam_keluar'] == '00:00:00' || $data_absen['jam_keluar'] == NULL) {
        // Absen Keluar
        mysqli_query($koneksi, "UPDATE absensi SET jam_keluar = '$jam_sekarang' WHERE id_pegawai = '$id_pegawai' AND tanggal = '$tgl_hari_ini'");
    }
    header("Location: index.php?status=success");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?= $set['nama_perusahaan'] ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --accent-color: <?= $set['warna_button'] ?>;
            --text-on-accent: <?= $set['warna_font'] ?>;
        }

        body { font-family: 'Poppins', sans-serif; background-color: #f4f7fa; }
        .main-content { margin-left: 320px; padding: 40px; min-height: 100vh; transition: 0.3s; }

        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color), #2c3e50);
            border-radius: 30px; padding: 40px; color: white;
            position: relative; overflow: hidden; margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .welcome-card .decoration {
            position: absolute; right: -50px; bottom: -50px;
            font-size: 15rem; opacity: 0.1; transform: rotate(-15deg);
        }

        /* Attendance Box */
        .absen-container {
            background: white; border-radius: 25px; padding: 40px;
            text-align: center; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        }
        .digital-clock { font-size: 4rem; font-weight: 700; color: #2d3436; margin-bottom: 5px; }
        .date-display { color: #b2bec3; font-weight: 500; margin-bottom: 35px; }

        /* Button Absen */
        .btn-absen-main {
            width: 220px; height: 220px; border-radius: 50%; border: 12px solid #f8f9fa;
            background: var(--accent-color); color: var(--text-on-accent);
            font-size: 1.3rem; font-weight: 700; cursor: pointer; transition: 0.4s;
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; margin: 0 auto 20px; text-decoration: none;
        }
        .btn-absen-main:hover { transform: scale(1.05); filter: brightness(1.1); color: var(--text-on-accent); }
        .btn-absen-main i { font-size: 3rem; margin-bottom: 10px; }
        
        .btn-done { background: #00b894; border-color: #e8f8f5; cursor: default; }
        .btn-done:hover { transform: none; }

        /* Status Pilles */
        .info-row { display: flex; justify-content: center; gap: 15px; margin-top: 25px; }
        .info-pill {
            background: #f8f9fa; padding: 10px 20px; border-radius: 12px;
            font-size: 0.9rem; color: #636e72; display: flex; align-items: center; gap: 8px;
        }
        .info-pill b { color: #2d3436; }
    </style>
</head>

<body>

    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="welcome-card">
            <i class="fa-solid fa-cloud-sun decoration"></i>
            <p class="mb-1">
                <?php
                $hour = date('H');
                if ($hour < 12) echo "Selamat Pagi 🌅";
                else if ($hour < 18) echo "Selamat Siang ☀️";
                else echo "Selamat Malam 🌙";
                ?>
            </p>
            <h1>Halo, <?= explode(' ', $_SESSION['nama_lengkap'])[0] ?>!</h1>
            <p>Semangat bekerja! Pastikan absensi Anda tercatat hari ini.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="absen-container">
                    <div class="digital-clock" id="live-clock">00:00:00</div>
                    <div class="date-display" id="live-date">Memuat tanggal...</div>

                    <form method="POST">
                        <?php if (!$data_absen): ?>
                            <button type="submit" name="absen" class="btn-absen-main">
                                <i class="fa-solid fa-fingerprint"></i>
                                <span>ABSEN MASUK</span>
                            </button>
                            <p class="text-muted small">Silakan klik tombol untuk mencatat kehadiran</p>

                        <?php elseif ($data_absen['jam_keluar'] == '00:00:00' || $data_absen['jam_keluar'] == NULL): ?>
                            <button type="submit" name="absen" class="btn-absen-main" style="background: #e67e22;">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <span>ABSEN PULANG</span>
                            </button>
                            <div class="info-row">
                                <div class="info-pill">
                                    <i class="fa-solid fa-clock text-primary"></i>
                                    <span>Masuk: <b><?= date('H:i', strtotime($data_absen['jam_masuk'])) ?> WIB</b></span>
                                </div>
                            </div>

                        <?php else: ?>
                            <div class="btn-absen-main btn-done">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>SELESAI</span>
                            </div>
                            <div class="info-row">
                                <div class="info-pill">
                                    <i class="fa-solid fa-arrow-right-to-bracket text-success"></i>
                                    <span>Masuk: <b><?= date('H:i', strtotime($data_absen['jam_masuk'])) ?></b></span>
                                </div>
                                <div class="info-pill">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-danger"></i>
                                    <span>Pulang: <b><?= date('H:i', strtotime($data_absen['jam_keluar'])) ?></b></span>
                                </div>
                            </div>
                            <p class="text-success mt-3 fw-bold small">Absensi Anda hari ini telah lengkap!</p>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('live-clock').innerText = `${hours}:${minutes}:${seconds}`;

            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('live-date').innerText = now.toLocaleDateString('id-ID', options);
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>