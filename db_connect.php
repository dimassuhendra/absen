<?php
// 1. Atur Zona Waktu PHP secara Global
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Database
$host = "localhost";
$user = "root";
$password = "";
$db_name = "cms_company";

// 2. Membuat Koneksi
$koneksi = mysqli_connect($host, $user, $password, $db_name);

// 3. Cek Koneksi (Pastikan koneksi berhasil sebelum menjalankan query jam)
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// 4. Sinkronisasi Zona Waktu MySQL dengan PHP
// Ini memastikan fungsi NOW() atau jam yang masuk ke tabel absensi adalah WIB
mysqli_query($koneksi, "SET time_zone = '+07:00'");

/** * Fungsi Global untuk mempermudah pengambilan data pengaturan web
 */
function getSetting($koneksi)
{
    $query = "SELECT * FROM setting_web LIMIT 1";
    $result = mysqli_query($koneksi, $query);
    return mysqli_fetch_assoc($result);
}

// 5. Mulai Session secara global
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>