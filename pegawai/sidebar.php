<?php
// Mendapatkan nama file yang sedang aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />

<style>
    :root {
        --primary-color:
            <?= $set['warna_header'] ?>
        ;
        --accent-color:
            <?= $set['warna_button'] ?>
        ;
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

    /* Header Sidebar */
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
        line-height: 1.2;
    }

    /* Menu Wrapper */
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
        width: 25px;
        text-align: center;
        font-size: 1.1rem;
    }

    /* Active & Hover State */
    .sidebar-menu li a:hover {
        background: #f8f9fa;
        color: var(--primary-color);
        transform: translateX(5px);
    }

    .sidebar-menu li a.active {
        background: var(--primary-color);
        color:
            <?= $set['warna_font'] ?>
            !important;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    /* Logout Style */
    .btn-logout {
        margin-top: 20px;
        border-top: 1px solid #f8f9fa;
        padding-top: 20px !important;
    }

    .btn-logout a {
        color: #ff7675 !important;
    }

    .btn-logout a:hover {
        background: #fff5f5 !important;
    }

    /* Widget Info Pegawai */
    .sidebar-user-widget {
        margin: 15px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 18px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar-small {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--primary-color);
        color:
            <?= $set['warna_font'] ?>
        ;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .user-info-text p {
        margin: 0;
        font-size: 0.85rem;
        font-weight: 600;
        color: #2d3436;
    }

    .user-info-text small {
        font-size: 0.7rem;
        color: #b2bec3;
        display: block;
    }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../assets/img/<?= $set['logo'] ?>" alt="Logo">
        <h4><?= $set['nama_perusahaan'] ?></h4>
    </div>

    <div class="menu-container">
        <ul class="sidebar-menu">
            <li>
                <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-house-chimney"></i> Dashboard / Absen
                </a>
            </li>
            <li>
                <a href="cuti.php" class="<?= ($current_page == 'cuti.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-calendar-day"></i> Cuti & Izin
                </a>
            </li>
            <li>
                <a href="gaji.php" class="<?= ($current_page == 'gaji.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-wallet"></i> Riwayat Gaji
                </a>
            </li>
            <li>
                <a href="profil.php" class="<?= ($current_page == 'profil.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-gear"></i> Pengaturan Profil
                </a>
            </li>

            <li class="btn-logout">
                <a href="../logout.php">
                    <i class="fa-solid fa-door-open"></i> Keluar Sistem
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-user-widget">
        <div class="user-avatar-small">
            <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
        </div>
        <div class="user-info-text">
            <p><?= explode(' ', $_SESSION['nama'])[0] ?></p>
            <small><?= strtoupper($_SESSION['role']) ?></small>
        </div>
    </div>
</div>