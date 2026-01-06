<?php
include "../db_connect.php";

// Cek hak akses (HR atau Pegawai boleh akses)
if ($_SESSION['role'] != 'hr' && $_SESSION['role'] != 'pegawai') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// Ambil ID Gaji dari URL
if (!isset($_GET['id'])) {
    echo "ID Gaji tidak ditemukan.";
    exit();
}

$id_gaji = mysqli_real_escape_string($koneksi, $_GET['id']);

// Query ambil data gaji spesifik
$query_gaji = "SELECT g.*, p.nama_lengkap, j.nama_jabatan 
               FROM gaji g
               JOIN pegawai p ON g.id_pegawai = p.id_pegawai
               JOIN jabatan j ON p.id_jabatan = j.id_jabatan
               WHERE g.id_gaji = '$id_gaji'";

$res_gaji = mysqli_query($koneksi, $query_gaji);
$data = mysqli_fetch_assoc($res_gaji);

if (!$data) {
    echo "Data gaji tidak valid.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Slip Gaji -
        <?= $data['nama_lengkap'] ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <style>
        :root {
            --primary-color: <?= $set['warna_header'] ?>;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fa;
            padding: 20px;
        }

        .salary-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 850px;
            margin: auto;
            position: relative;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        }

        .salary-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: var(--primary-color);
        }

        .slip-header {
            border-bottom: 2px solid #f1f2f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary-color);
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 25px 0 15px 0;
            text-transform: uppercase;
        }

        .row-detail {
            display: flex;
            justify-content: space-between;
            padding: 12px 15px;
            border-bottom: 1px solid #f8f9fa;
        }

        .total-box {
            background: var(--primary-color);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
                padding: 0;
            }

            .salary-card {
                box-shadow: none;
                border: 1px solid #eee;
                width: 100%;
                max-width: 100%;
            }

            .total-box {
                background: #333 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <div class="text-center mb-4 no-print">
        <button onclick="window.print()" class="btn btn-success px-4">
            <i class="fa-solid fa-print me-2"></i> Cetak Sekarang
        </button>
        <button onclick="window.history.back()" class="btn btn-secondary px-4">Kembali</button>
    </div>

    <div class="salary-card">
        <div class="slip-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1">SLIP GAJI</h3>
                <p class="text-muted mb-0">ID Transaksi: #PAY-
                    <?= $data['id_gaji'] . date('my') ?>
                </p>
            </div>
            <div class="text-end">
                <img src="../assets/img/<?= $set['logo'] ?>" width="50" class="mb-2">
                <h6 class="fw-bold mb-0">
                    <?= $set['nama_perusahaan'] ?>
                </h6>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-6">
                <table class="table table-borderless sm">
                    <tr>
                        <td class="text-muted small px-0" width="120">Nama Pegawai</td>
                        <td class="fw-bold px-0">:
                            <?= $data['nama_lengkap'] ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted small px-0">Jabatan</td>
                        <td class="fw-bold px-0">:
                            <?= $data['nama_jabatan'] ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-6">
                <table class="table table-borderless sm">
                    <tr>
                        <td class="text-muted small px-0" width="120">Periode Gaji</td>
                        <td class="fw-bold px-0">:
                            <?= date('F Y', strtotime($data['bulan_tahun'])) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted small px-0">Status</td>
                        <td class="px-0">: <span class="badge bg-success-subtle text-success px-3">Lunas</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section-title">Penerimaan (Earnings)</div>
        <div class="row-detail"><span>Gaji Pokok</span><span class="fw-semibold">Rp
                <?= number_format($data['gaji_pokok'], 0, ',', '.') ?>
            </span></div>
        <div class="row-detail"><span>Tunjangan</span><span class="fw-semibold">Rp
                <?= number_format($data['tunjangan'], 0, ',', '.') ?>
            </span></div>
        <div class="row-detail"><span>Bonus</span><span class="fw-semibold">Rp
                <?= number_format($data['bonus'], 0, ',', '.') ?>
            </span></div>

        <div class="section-title text-danger" style="background: #fff5f5;">Potongan (Deductions)</div>
        <div class="row-detail border-bottom-0"><span>Potongan / Denda</span><span class="fw-semibold text-danger">- Rp
                <?= number_format($data['potongan'], 0, ',', '.') ?>
            </span></div>

        <div class="total-box">
            <div>
                <div class="small opacity-75">GAJI BERSIH (TAKE HOME PAY)</div>
                <h2 class="fw-bold mb-0">Rp
                    <?= number_format(($data['gaji_pokok'] + $data['tunjangan'] + $data['bonus']) - $data['potongan'], 0, ',', '.') ?>
                </h2>
            </div>
            <i class="fa-solid fa-wallet fs-1 opacity-25"></i>
        </div>

        <div class="mt-5 pt-4 d-flex justify-content-between align-items-end">
            <div class="text-muted small">
                *Dicetak pada
                <?= date('d M Y, H:i') ?> WIB<br>
                *Dokumen digital sah tanpa tanda tangan basah.
            </div>
            <div class="text-center" style="width: 200px;">
                <p class="mb-5 small">Finance Manager,</p>
                <h6 class="fw-bold mb-0" style="border-bottom: 1px solid #333; padding-bottom: 5px;">DIVISI KEUANGAN
                </h6>
                <p class="small text-muted">
                    <?= $set['nama_perusahaan'] ?>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Otomatis trigger print saat halaman dimuat (opsional)
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>