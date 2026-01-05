<?php
// Konfigurasi Database
$host     = "localhost";
$user     = "root";
$password = "";
$db_name  = "cms_company";

// Membuat Koneksi
$koneksi = mysqli_connect($host, $user, $password, $db_name);

// Cek Koneksi
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

/** * Fungsi Global untuk mempermudah pengambilan data pengaturan web
 * Admin bisa memanggil ini di header tiap folder
 */
function getSetting($koneksi) {
    $query = "SELECT * FROM setting_web LIMIT 1";
    $result = mysqli_query($koneksi, $query);
    return mysqli_fetch_assoc($result);
}

// Mulai Session secara global jika diperlukan
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>