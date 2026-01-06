<?php
include "../db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cuti = $_POST['id_cuti'];
    $id_pegawai = $_POST['id_pegawai'];
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    $aksi = $_POST['aksi'];

    if ($aksi == 'setuju') {
        // 1. Hitung selisih hari
        $start = new DateTime($tgl_mulai);
        $end = new DateTime($tgl_selesai);
        $durasi = $start->diff($end)->days + 1;

        // 2. Ambil tahun cuti
        $tahun = $start->format('Y');

        // 3. Update status_hr & status global menjadi disetujui
        $update = mysqli_query($koneksi, "UPDATE pengajuan_cuti SET 
                  status_hr = 'disetujui', 
                  status = 'disetujui' 
                  WHERE id_cuti = '$id_cuti'");

        if ($update) {
            // 4. POTONG KUOTA (Hanya dilakukan oleh HR)
            mysqli_query($koneksi, "UPDATE kuota_cuti SET 
                        sisa_jatah = sisa_jatah - $durasi 
                        WHERE id_pegawai = '$id_pegawai' AND tahun = '$tahun'");
            
            header("Location: data_cuti.php?pesan=berhasil_setuju");
        }
    } else {
        // Jika HR menolak
        mysqli_query($koneksi, "UPDATE pengajuan_cuti SET 
                    status_hr = 'ditolak', 
                    status = 'ditolak' 
                    WHERE id_cuti = '$id_cuti'");
        
        header("Location: data_cuti.php?pesan=berhasil_tolak");
    }
}