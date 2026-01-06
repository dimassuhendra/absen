<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

<style>
    :root {
        --side-primary: <?= $set['warna_header'] ?>;
        --side-accent: <?= $set['warna_button'] ?>;
        --side-font: <?= $set['warna_font'] ?>;
    }

    .sidebar {
        width: 280px;
        height: 100vh;
        background: var(--side-primary);
        color: white;
        position: fixed;
        left: 0;
        top: 0;
        overflow-y: auto;
        transition: all 0.3s;
        z-index: 1000;
        box-shadow: 4px 0 10px rgba(0,0,0,0.1);
    }

    .sidebar-header {
        padding: 30px 25px;
        text-align: center;
        background: rgba(0,0,0,0.2);
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .sidebar-header img {
        width: 60px;
        height: auto;
        margin-bottom: 15px;
    }

    .sidebar-header h4 {
        margin: 0;
        font-weight: 600;
        font-size: 16px;
        color: #fff;
    }

    .sidebar-header small {
        color: var(--side-accent);
        font-weight: 500;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: 2px;
    }

    .sidebar-menu {
        list-style: none;
        padding: 20px 0;
        margin: 0;
    }

    .menu-label {
        padding: 20px 25px 10px;
        font-size: 11px;
        font-weight: 700;
        color: rgba(255,255,255,0.4);
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }

    .sidebar-menu li a {
        padding: 12px 25px;
        display: flex;
        align-items: center;
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        transition: all 0.3s;
        border-left: 4px solid transparent;
        font-size: 14px;
    }

    .sidebar-menu li a i {
        width: 25px;
        margin-right: 15px;
        font-size: 18px;
        text-align: center;
    }

    .sidebar-menu li a:hover {
        background: rgba(255,255,255,0.05);
        color: #fff;
        border-left: 4px solid var(--side-accent);
    }

    .sidebar-menu li a.active {
        background: rgba(255, 255, 255, 0.05);
        color: var(--side-accent);
        border-left: 4px solid var(--side-accent);
        font-weight: 600;
    }

    .sidebar-menu li a.logout:hover {
        color: #ff7675;
        border-left: 4px solid #ff7675;
    }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../assets/img/<?= $set['logo'] ?>" alt="Logo">
        <h4><?= $set['nama_perusahaan'] ?></h4>
        <small>Panel HRD</small>
    </div>

    <ul class="sidebar-menu">
        <?php $uri = $_SERVER['REQUEST_URI']; ?>
        
        <li>
            <a href="index.php" class="<?= strpos($uri, 'index.php') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i> Dashboard HR
            </a>
        </li>

        <li class="menu-label">Kepegawaian</li>
        <li>
            <a href="departemen.php" class="<?= strpos($uri, 'departemen.php') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-sitemap"></i> Data Departemen
            </a>
        </li>
        <li>
            <a href="pegawai.php" class="<?= strpos($uri, 'pegawai.php') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-user-group"></i> Data Pegawai
            </a>
        </li>
        <li>
            <a href="absensi.php" class="<?= strpos($uri, 'absensi.php') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-clock-rotate-left"></i> Rekap Absensi
            </a>
        </li>
        <li>
            <a href="konfirmasi_cuti.php" class="<?= strpos($uri, 'konfirmasi_cuti.php') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-check"></i> Persetujuan Cuti
            </a>
        </li>

        <li class="menu-label">Keuangan</li>
        <li>
            <a href="gaji_jabatan.php" class="<?= strpos($uri, 'gaji_jabatan.php') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-layer-group"></i> Set Gaji Jabatan
            </a>
        </li>
        <li>
            <a href="input_gaji.php" class="<?= strpos($uri, 'input_gaji.php') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-money-check-dollar"></i> Proses Gaji
            </a>
        </li>

        <li class="menu-label">Sistem</li>
        <li>
            <a href="profil.php" class="<?= strpos($uri, 'profil.php') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-user-shield"></i> Profil Saya
            </a>
        </li>
        <li>
            <a href="../logout.php" class="logout">
                <i class="fa-solid fa-power-off"></i> Keluar
            </a>
        </li>
    </ul>
</div>