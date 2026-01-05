<?php
include "db_connect.php";
$set = getSetting($koneksi);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | <?= $set['nama_perusahaan'] ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Pengaturan Warna Dinamis dari Database */
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
            --button-color: <?= $set['warna_button'] ?>;
            --button-hover: #555; /* Bisa ditambah di kolom DB jika mau */
            --font-color: <?= $set['warna_font'] ?>;
        }
        .login-left { background-color: var(--primary-color); }
        .btn-login { background-color: var(--button-color); color: var(--font-color); }
        .btn-login:hover { opacity: 0.8; }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-left">
        <img src="assets/img/<?= $set['logo'] ?>" alt="Logo">
        <h1><?= $set['nama_perusahaan'] ?></h1>
        <p><i>"Efisiensi dalam setiap langkah kerja kita."</i></p>
    </div>

    <div class="login-right">
        <div class="login-box">
            <h2>Masuk ke Sistem</h2>
            
            <form action="proses/proses_login.php" method="POST">
                <div class="role-switch">
                    <input type="radio" name="role" value="pegawai" id="role1" checked hidden>
                    <label for="role1" class="role-btn active" onclick="setActive(this)">Pegawai</label>
                    
                    <input type="radio" name="role" value="hr" id="role2" hidden>
                    <label for="role2" class="role-btn" onclick="setActive(this)">HR</label>
                    
                    <input type="radio" name="role" value="admin" id="role3" hidden>
                    <label for="role3" class="role-btn" onclick="setActive(this)">Admin</label>
                </div>

                <div class="form-group">
                    <label>Email / NIP</label>
                    <input type="text" name="identifier" placeholder="Masukkan Email atau NIP" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="********" required>
                </div>
                
                <button type="submit" class="btn-login">LOGIN</button>
            </form>
        </div>
    </div>
</div>

<script>
    function setActive(el) {
        document.querySelectorAll('.role-btn').forEach(btn => btn.classList.remove('active'));
        el.classList.add('active');
    }
</script>
</body>
</html>