<?php
// Mendapatkan nama file yang sedang diakses (contoh: index.php)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />

<style>
    :root {
        --primary-color: <?= $set['warna_header'] ?>;
        --accent-color: <?= $set['warna_button'] ?>;
        --sidebar-bg: #ffffff;
    }

    .sidebar {
        width: 280px;
        height: calc(100vh - 40px);
        background: var(--sidebar-bg);
        position: fixed;
        left: 20px;
        top: 20px;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        z-index: 1000;
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .sidebar-header {
        padding: 30px 25px;
        text-align: center;
        border-bottom: 1px solid #f8f9fa;
    }

    .sidebar-header img {
        width: 60px;
        height: 60px;
        object-fit: contain;
        margin-bottom: 15px;
        background: #f8f9fa;
        padding: 10px;
        border-radius: 15px;
    }

    .sidebar-header h4 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary-color);
        margin: 0;
        letter-spacing: 0.5px;
    }

    .menu-container {
        flex-grow: 1;
        padding: 20px 15px;
    }

    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-menu li {
        margin-bottom: 8px;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: #6c757d;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 500;
        font-size: 0.95rem;
        transition: all 0.3s;
    }

    .sidebar-menu a i {
        margin-right: 15px;
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
    }

    /* Hover State */
    .sidebar-menu li a:hover {
        background: #f8f9fa;
        color: var(--primary-color);
        transform: translateX(5px);
    }

    /* Active State Otomatis */
    .sidebar-menu li a.active {
        background: var(--primary-color);
        color: <?= $set['warna_font'] ?> !important;
        /* Menggunakan warna font dari setting */
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .btn-logout {
        margin-top: 20px;
        border-top: 1px solid #f8f9fa;
        padding-top: 20px !important;
    }

    .btn-logout a {
        color: #dc3545 !important;
    }

    .btn-logout a:hover {
        background: #fff5f5 !important;
    }

    .sidebar-footer-widget {
        margin: 15px;
        padding: 20px;
        background: linear-gradient(135deg, var(--primary-color), #2c3e50);
        border-radius: 18px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .sidebar-footer-widget small {
        opacity: 0.7;
        font-size: 0.75rem;
    }

    .sidebar-footer-widget p {
        margin: 0;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .widget-bg-icon {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 3rem;
        opacity: 0.1;
        transform: rotate(-15deg);
    }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../assets/img/<?= $set['logo'] ?>" alt="Logo">
        <h4>
            <?= $set['nama_perusahaan'] ?>
        </h4>
    </div>

    <div class="menu-container">
        <ul class="sidebar-menu">
            <li>
                <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-house-chimney"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="jabatan.php" class="<?= ($current_page == 'jabatan.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-briefcase"></i> Data Jabatan
                </a>
            </li>
            <li>
                <a href="pegawai.php" class="<?= ($current_page == 'pegawai.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-users"></i> Data Pegawai
                </a>
            </li>
            <li>
                <a href="setting_web.php" class="<?= ($current_page == 'setting_web.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-palette"></i> Pengaturan Web
                </a>
            </li>
            <li class="btn-logout">
                <a href="../logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar Sistem
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-footer-widget">
        <i class="fa-solid fa-shield-halved widget-bg-icon"></i>
        <small>Login sebagai:</small>
        <p>
            <?= $_SESSION['role'] == 'admin' ? 'Administrator' : strtoupper($_SESSION['role']) ?>
        </p>
        <hr style="margin: 10px 0; opacity: 0.2; border: 0.5px solid white;">
        <div id="digital-clock" style="font-size: 0.8rem; font-weight: 300;"></div>
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' };
        document.getElementById('digital-clock').innerHTML = now.toLocaleDateString('id-ID', options);
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>