<?php
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
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Pegawai |
        <?= $set['nama_perusahaan'] ?>
    </title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --button-color: <?= $set['warna_button'] ?>;
            --font-color: <?= $set['warna_font'] ?>;
        }

        .absen-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .clock {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .btn-absen {
            padding: 15px 40px;
            font-size: 1.2rem;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            background: var(--button-color);
            color: var(--font-color);
        }

        .btn-disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include "sidebar.php"; ?>
        <div class="main-content">
            <?php include "../admin/header.php"; ?>
            <div class="content-body">
                <div class="absen-box">
                    <h2>Selamat
                        <?php
                        $hour = date('H');
                        if ($hour < 12)
                            echo "Pagi";
                        else if ($hour < 18)
                            echo "Siang";
                        else
                            echo "Malam";
                        ?>,
                        <?= $_SESSION['nama'] ?>
                    </h2>
                    <div class="clock" id="clock">00:00:00</div>

                    <form method="POST">
                        <?php if (!$data_absen): ?>
                            <button type="submit" name="absen" class="btn-absen">ABSEN MASUK</button>
                        <?php elseif ($data_absen['jam_keluar'] == '00:00:00' || $data_absen['jam_keluar'] == NULL): ?>
                            <p>Anda masuk pada: <b>
                                    <?= $data_absen['jam_masuk'] ?>
                                </b></p>
                            <button type="submit" name="absen" class="btn-absen" style="background:#e67e22;">ABSEN
                                KELUAR</button>
                        <?php else: ?>
                            <p>Absensi hari ini selesai.</p>
                            <button class="btn-absen btn-disabled" disabled>SUDAH ABSEN</button>
                            <p><small>Masuk:
                                    <?= $data_absen['jam_masuk'] ?> | Keluar:
                                    <?= $data_absen['jam_keluar'] ?>
                                </small></p>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        setInterval(() => {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID');
        }, 1000);
    </script>
</body>

</html>