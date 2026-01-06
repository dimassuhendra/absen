<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mendapatkan nama file yang sedang aktif
$current_page = basename($_SERVER['PHP_SELF']);

// Ambil ID Pegawai dari session dengan pengamanan agar tidak error Undefined
$id_user_login = isset($_SESSION['id_pegawai']) ? $_SESSION['id_pegawai'] : 0;

// Logika Cek Kepala Divisi
$is_kepala = false;
$notif_count = 0;

if ($id_user_login != 0) {
    // Sesuaikan nama kolom menjadi id_kepala_dept sesuai database
    $cek_kepala = mysqli_query($koneksi, "SELECT * FROM departemen WHERE id_kepala = '$id_user_login'");
    
    if (mysqli_num_rows($cek_kepala) > 0) {
        $is_kepala = true;
        $dept_data = mysqli_fetch_assoc($cek_kepala);
        $id_dept_pimpinan = $dept_data['id_departemen'];

        // Cek pengajuan yang butuh ACC atasan
        $q_notif = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengajuan_cuti pc 
                   JOIN pegawai p ON pc.id_pegawai = p.id_pegawai 
                   JOIN jabatan j ON p.id_jabatan = j.id_jabatan 
                   WHERE j.id_departemen = '$id_dept_pimpinan' AND pc.status_atasan = 'pending'");
        $notif_data = mysqli_fetch_assoc($q_notif);
        $notif_count = $notif_data['total'];
    }
}
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
        line-height: 1.2;
    }

    .menu-container {
        flex-grow: 1;
        padding: 20px 15px;
        overflow-y: auto;
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

    .sidebar-menu li a:hover {
        background: #f8f9fa;
        color: var(--primary-color);
        transform: translateX(5px);
    }

    .sidebar-menu li a.active {
        background: var(--primary-color);
        color: <?= $set['warna_font'] ?> !important;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .menu-divider {
        margin-top: 15px;
        margin-bottom: 5px;
        padding-left: 20px;
    }

    .menu-divider small {
        color: #b2bec3;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .badge-notif {
        background: #ff7675;
        color: white;
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 10px;
        margin-left: auto;
        font-weight: 700;
    }

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
        color: <?= $set['warna_font'] ?>;
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

            <?php if ($is_kepala): ?>
            <li class="menu-divider">
                <small>Manajemen Tim</small>
            </li>
            <li>
                <a href="persetujuan_cuti.php" class="<?= ($current_page == 'persetujuan_cuti.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-clipboard-check"></i> Persetujuan Cuti
                    <?php if ($notif_count > 0): ?>
                        <span class="badge-notif"><?= $notif_count ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endif; ?>

            <li class="menu-divider">
                <small>Menu Pribadi</small>
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
            <?= strtoupper(substr($_SESSION['nama_lengkap'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="user-info-text">
            <p><?= explode(' ', $_SESSION['nama_lengkap'] ?? 'User')[0] ?></p>
            <small><?= strtoupper($_SESSION['role'] ?? 'PEGAWAI') ?></small>
        </div>
    </div>
</div>