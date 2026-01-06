<?php
include "../db_connect.php";
$set = getSetting($koneksi);

// Query: Ambil pengajuan yang sudah di-ACC Atasan (status_atasan = disetujui)
// namun status_hr masih pending
$query = "SELECT pc.*, p.nama_lengkap, p.nip, j.nama_jabatan, d.nama_departemen 
          FROM pengajuan_cuti pc
          JOIN pegawai p ON pc.id_pegawai = p.id_pegawai
          JOIN jabatan j ON p.id_jabatan = j.id_jabatan
          JOIN departemen d ON j.id_departemen = d.id_departemen
          WHERE pc.status_atasan = 'disetujui' AND pc.status_hr = 'pending'
          ORDER BY pc.tgl_mulai ASC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Persetujuan Final HR | SIMPEG</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        :root { --primary: <?= $set['warna_header'] ?>; --accent: <?= $set['warna_button'] ?>; }
        body { font-family: 'Poppins', sans-serif; background: #f4f7f6; margin: 0; }
        .content { margin-left: 280px; padding: 40px; }
        .card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; background: #f8f9fa; border-bottom: 2px solid #eee; font-size: 13px; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        .badge-atasan { background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 5px; font-size: 11px; font-weight: bold; }
        .btn { padding: 8px 15px; border-radius: 6px; border: none; cursor: pointer; color: white; font-weight: 600; font-size: 12px; }
        .btn-success { background: #27ae60; }
        .btn-danger { background: #e74c3c; }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>
    <div class="content">
        <h2>Persetujuan Final HR</h2>
        <p>Menampilkan pengajuan yang telah disetujui oleh Kepala Divisi.</p>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Divisi</th>
                        <th>Tanggal Cuti</th>
                        <th>Alasan</th>
                        <th>Status Atasan</th>
                        <th>Aksi HR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <strong><?= $row['nama_lengkap'] ?></strong><br>
                            <small><?= $row['nip'] ?></small>
                        </td>
                        <td><?= $row['nama_departemen'] ?></td>
                        <td>
                            <?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> - <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?>
                        </td>
                        <td><?= $row['alasan'] ?></td>
                        <td><span class="badge-atasan"><i class="fa-solid fa-check-double"></i> DISETUJUI</span></td>
                        <td>
                            <form action="proses_cuti_hr.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_cuti" value="<?= $row['id_cuti'] ?>">
                                <input type="hidden" name="id_pegawai" value="<?= $row['id_pegawai'] ?>">
                                <input type="hidden" name="tgl_mulai" value="<?= $row['tgl_mulai'] ?>">
                                <input type="hidden" name="tgl_selesai" value="<?= $row['tgl_selesai'] ?>">
                                <button type="submit" name="aksi" value="setuju" class="btn btn-success" onclick="return confirm('Setujui & potong kuota?')">Setujui</button>
                                <button type="submit" name="aksi" value="tolak" class="btn btn-danger">Tolak</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($result) == 0): ?>
                        <tr><td colspan="6" align="center">Belum ada pengajuan yang masuk dari level Atasan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>