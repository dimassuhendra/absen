<div class="sidebar">
    <div class="sidebar-header">
        <img src="../assets/img/<?= $set['logo'] ?>" alt="Logo">
        <h4 style="font-size: 14px;">
            <?= $set['nama_perusahaan'] ?>
        </h4>
        <small>(Panel HR)</small>
    </div>
    <ul class="sidebar-menu">
        <li><a href="index.php">📊 Dashboard HR</a></li>

        <li class="menu-label" style="padding: 10px 25px; font-size: 11px; opacity: 0.6;">KEPEGAWAIAN</li>
        <li><a href="pegawai.php">👥 Data Pegawai</a></li>
        <li><a href="absensi.php">🕒 Rekap Absensi</a></li>
        <li><a href="konfirmasi_cuti.php">📅 Persetujuan Cuti</a></li>

        <li class="menu-label" style="padding: 10px 25px; font-size: 11px; opacity: 0.6;">KEUANGAN</li>
        <li><a href="gaji_jabatan.php">💰 Set Gaji Jabatan</a></li>
        <li><a href="input_gaji.php">💸 Proses Gaji Bulanan</a></li>

        <li style="margin-top: 30px;"><a href="profil.php">👤 Profil Saya</a></li>
        <li><a href="../logout.php" style="color: #ff7675;">🚪 Keluar</a></li>
    </ul>
</div>