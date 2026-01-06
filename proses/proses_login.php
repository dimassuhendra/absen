<?php
include "../db_connect.php";

// 1. Tambahkan titik koma (;) di akhir session_start
// 2. Sangat disarankan session_start diletakkan di baris paling atas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = mysqli_real_escape_string($koneksi, $_POST['identifier']);
    $password = $_POST['password'];
    $role_input = $_POST['role'];

    // Cari user berdasarkan email ATAU nip dan role yang dipilih
    // Berdasarkan tabel 'pegawai', kolom yang digunakan adalah 'email', 'nip', dan 'role'
    $query = "SELECT * FROM pegawai WHERE (email='$identifier' OR nip='$identifier') AND role='$role_input' LIMIT 1";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Verifikasi Password
        if (password_verify($password, $user['password'])) {
            
            // Regenerate ID session untuk keamanan
            session_regenerate_id(true);

            // SIMPAN SESSION
            $_SESSION['id_user'] = $user['id_pegawai'];
            $_SESSION['role'] = $user['role'];
            
            // Menggunakan kolom 'nama_lengkap' sesuai struktur tabel 'pegawai'
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

            // Redirect sesuai folder role
            if ($user['role'] == 'admin') {
                header("Location: ../admin/index.php");
            } elseif ($user['role'] == 'hr') {
                header("Location: ../hr/index.php");
            } else {
                header("Location: ../pegawai/index.php");
            }
            exit();
        } else {
            echo "<script>alert('Password Salah!'); window.location='index.php';</script>";
        }
    } else {
        echo "<script>alert('User tidak ditemukan atau Role salah!'); window.location='index.php';</script>";
    }
}
?>