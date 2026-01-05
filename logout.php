<?php
session_start();

// Menghapus semua variabel session
$_SESSION = array();

// Menghapus session dari browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Menghancurkan session
session_destroy();

// Redirect kembali ke halaman login (index.php)
header("Location: index.php?pesan=logout_berhasil");
exit();
?>