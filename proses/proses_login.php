<?php
include "../db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = mysqli_real_escape_string($koneksi, $_POST['identifier']);
    $password = $_POST['password'];
    $role_input = $_POST['role'];

    // 1. Hapus baris $_SESSION yang ada di sini karena $user belum didefinisikan

    // Cari user berdasarkan email ATAU nip dan role yang dipilih
    $query = "SELECT * FROM pegawai WHERE (email='$identifier' OR nip='$identifier') AND role='$role_input' LIMIT 1";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Verifikasi Password
        if (password_verify($password, $user['password'])) {

            // SIMPAN SESSION DI SINI SETELAH $user BERHASIL DIAMBIL
            $_SESSION['id_user'] = $user['id_pegawai'];
            $_SESSION['role'] = $user['role'];

            // Perhatikan: Gunakan ['nama'] sesuai struktur database Anda
            $_SESSION['nama_lengkap'] = $user['nama'];

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