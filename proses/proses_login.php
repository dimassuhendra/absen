<?php
include "../db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = mysqli_real_escape_string($koneksi, $_POST['identifier']);
    $password   = $_POST['password'];
    $role_input = $_POST['role'];

    // Cari user berdasarkan email ATAU nip dan role yang dipilih
    $query = "SELECT * FROM pegawai WHERE (email='$identifier' OR nip='$identifier') AND role='$role_input' LIMIT 1";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Verifikasi Password (Gunakan password_verify jika sudah di-hash)
        if (password_verify($password, $user['password'])) {
            $_SESSION['id_user']   = $user['id_pegawai'];
            $_SESSION['nama']      = $user['nama_lengkap'];
            $_SESSION['role']      = $user['role'];

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