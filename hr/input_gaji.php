<?php
include "../db_connect.php";

// Cek hak akses HR
if ($_SESSION['role'] != 'hr') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// Variabel untuk menangkap ID yang baru saja diinput agar bisa dibuka di JS
$id_cetak_baru = 0;

if (isset($_POST['proses_gaji'])) {
    $id_pegawai = $_POST['id_pegawai'];
    $bulan_tahun = $_POST['bulan_tahun'];
    $gaji_pokok = $_POST['gaji_pokok'];
    $tunjangan = $_POST['tunjangan'];
    $bonus = $_POST['bonus'];
    $potongan = $_POST['potongan'];

    $total_diterima = ($gaji_pokok + $tunjangan + $bonus) - $potongan;
    $tgl_full = $bulan_tahun . "-01";

    $cek = mysqli_query($koneksi, "SELECT id_gaji FROM gaji WHERE id_pegawai = '$id_pegawai' AND bulan_tahun = '$tgl_full'");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Gaji pegawai ini untuk periode tersebut sudah ada!'); window.location='input_gaji.php';</script>";
        exit();
    } else {
        $query = "INSERT INTO gaji (id_pegawai, bulan_tahun, gaji_pokok, tunjangan, bonus, potongan, total_diterima) 
                  VALUES ('$id_pegawai', '$tgl_full', '$gaji_pokok', '$tunjangan', '$bonus', '$potongan', '$total_diterima')";

        if (mysqli_query($koneksi, $query)) {
            $id_cetak_baru = mysqli_insert_id($koneksi);
            // Kita tidak melakukan header location di sini agar script di bawah bisa terbaca
        } else {
            echo "Eror: " . mysqli_error($koneksi);
        }
    }
}

// Ambil data pegawai (Tetap sama)
$query_p = "SELECT p.id_pegawai, p.nama_lengkap, p.nip, j.nama_jabatan, j.gaji_pokok, j.tunjangan 
            FROM pegawai p 
            JOIN jabatan j ON p.id_jabatan = j.id_jabatan 
            WHERE p.role != 'admin' 
            ORDER BY p.nama_lengkap ASC";
$data_pegawai = mysqli_query($koneksi, $query_p);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Proses Gaji |
        <?= $set['nama_perusahaan'] ?>
    </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <style>
        :root {
            --primary:
                <?= $set['warna_header'] ?>
            ;
            --accent:
                <?= $set['warna_button'] ?>
            ;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
        }

        .main-content {
            margin-left: 280px;
            padding: 40px;
        }

        .form-gaji {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: auto;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #2d3436;
        }

        .custom-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #dfe6e9;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .readonly-input {
            background: #f1f2f6;
            color: #636e72;
            font-weight: bold;
        }

        .total-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 2px dashed #dfe6e9;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: 0.3s;
        }

        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <?php include "sidebar.php"; ?>
        <div class="main-content">
            <div class="form-gaji">
                <h2 style="margin-top:0;"><i class="fa-solid fa-money-check-dollar"></i> Input Gaji Pegawai</h2>
                <p class="text-muted">Pastikan data tunjangan dan potongan sudah sesuai sebelum menerbitkan slip.</p>
                <hr><br>

                <form method="POST" id="formGaji">
                    <div class="grid-2">
                        <div class="input-group">
                            <label>Pilih Pegawai</label>
                            <select name="id_pegawai" id="id_pegawai" class="custom-input" required
                                onchange="isiOtomatis()">
                                <option value="">-- Pilih Pegawai --</option>
                                <?php while ($p = mysqli_fetch_assoc($data_pegawai)): ?>
                                    <option value="<?= $p['id_pegawai'] ?>" data-gapok="<?= $p['gaji_pokok'] ?>"
                                        data-tunjangan="<?= $p['tunjangan'] ?>">
                                        <?= $p['nama_lengkap'] ?>
                                        (
                                        <?= $p['nama_jabatan'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Periode Bulan</label>
                            <input type="month" name="bulan_tahun" class="custom-input" value="<?= date('Y-m') ?>"
                                required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="input-group">
                            <label>Gaji Pokok (Otomatis)</label>
                            <input type="number" name="gaji_pokok" id="gaji_pokok" class="custom-input readonly-input"
                                readonly>
                        </div>
                        <div class="input-group">
                            <label>Tunjangan (Otomatis)</label>
                            <input type="number" name="tunjangan" id="tunjangan" class="custom-input readonly-input"
                                readonly>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="input-group">
                            <label>Bonus / Lembur (Rp)</label>
                            <input type="number" name="bonus" id="bonus" class="custom-input" value="0"
                                oninput="hitungTotal()">
                        </div>
                        <div class="input-group">
                            <label>Potongan / Pajak (Rp)</label>
                            <input type="number" name="potongan" id="potongan" class="custom-input" value="0"
                                oninput="hitungTotal()">
                        </div>
                    </div>

                    <div class="total-box">
                        <span style="font-weight:600; color:#636e72;">Total Bersih (Take Home Pay):</span>
                        <span id="displayTotal" style="font-size:24px; font-weight:800; color:var(--primary);">Rp
                            0</span>
                    </div>

                    <button type="submit" name="proses_gaji" class="btn-submit">
                        <i class="fa-solid fa-paper-plane"></i> TERBITKAN SLIP SEKARANG
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Logika Pengisian Otomatis
        function isiOtomatis() {
            var select = document.getElementById("id_pegawai");
            var option = select.options[select.selectedIndex];
            var gapok = parseInt(option.getAttribute("data-gapok")) || 0;
            var tunjangan = parseInt(option.getAttribute("data-tunjangan")) || 0;

            document.getElementById("gaji_pokok").value = gapok;
            document.getElementById("tunjangan").value = tunjangan;
            hitungTotal();
        }

        // Logika Hitung Real-time
        function hitungTotal() {
            var gapok = parseInt(document.getElementById("gaji_pokok").value) || 0;
            var tunjangan = parseInt(document.getElementById("tunjangan").value) || 0;
            var bonus = parseInt(document.getElementById("bonus").value) || 0;
            var potongan = parseInt(document.getElementById("potongan").value) || 0;
            var total = (gapok + tunjangan + bonus) - potongan;
            document.getElementById("displayTotal").innerText = "Rp " + total.toLocaleString('id-ID');
        }

        // Logika Buka Tab Cetak Setelah Berhasil Simpan
        <?php if ($id_cetak_baru > 0): ?>
            // Gunakan konfirmasi agar browser tidak memblokir pop-up
            if (confirm("Gaji berhasil disimpan! Ingin mencetak slip sekarang?")) {
                // Buka di tab baru
                var win = window.open('cetak_slip.php?id=<?= $id_cetak_baru ?>', '_blank');
                if (win) {
                    win.focus();
                } else {
                    alert('Pop-up diblokir oleh browser! Harap izinkan pop-up untuk situs ini.');
                }
            }
            // Bersihkan URL agar tidak terjadi input ganda saat refresh
            window.location.href = 'input_gaji.php';
        <?php endif; ?>
    </script>
</body>

</html>