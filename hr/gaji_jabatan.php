<?php
include "../db_connect.php";

// Cek hak akses HR
if ($_SESSION['role'] != 'hr') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// --- LOGIKA UPDATE GAJI JABATAN ---
if (isset($_POST['update_gaji'])) {
    $id_jabatan = $_POST['id_jabatan'];
    $gapok = $_POST['gaji_pokok'];
    $tunjangan = $_POST['tunjangan'];
    $uang_makan = $_POST['uang_makan'];

    $query = "UPDATE jabatan SET 
              gaji_pokok = '$gapok', 
              tunjangan = '$tunjangan', 
              uang_makan = '$uang_makan' 
              WHERE id_jabatan = '$id_jabatan'";

    if (mysqli_query($koneksi, $query)) {
        header("Location: gaji_jabatan.php?pesan=berhasil");
        exit();
    }
}

// Ambil semua data jabatan
$query_jabatan = mysqli_query($koneksi, "SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Gaji Jabatan | <?= $set['nama_perusahaan'] ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --primary: <?= $set['warna_header'] ?>;
            --accent: <?= $set['warna_button'] ?>;
        }

        body { font-family: 'Poppins', sans-serif; background-color: #f0f2f5; margin: 0; }
        .main-content { margin-left: 280px; padding: 40px; min-height: 100vh; }

        .page-header { margin-bottom: 30px; }
        .page-header h2 { margin: 0; font-weight: 700; color: #1e293b; }
        .page-header p { color: #64748b; margin: 5px 0 0; }

        /* Card Container */
        .card-gaji { 
            background: white; 
            border-radius: 16px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); 
            margin-bottom: 25px; 
            overflow: hidden;
            border: 1px solid #e2e8f0;
            transition: transform 0.2s;
        }
        .card-gaji:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }

        .card-title-bar {
            background: #f8fafc;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-title-bar h3 { 
            margin: 0; 
            font-size: 16px; 
            color: var(--primary); 
            display: flex; 
            align-items: center; 
            gap: 10px;
        }

        /* Form Styling */
        .card-body { padding: 25px; }
        .grid-input { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 20px; 
        }

        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group label { 
            font-size: 12px; 
            font-weight: 600; 
            color: #64748b; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }

        .input-wrapper { position: relative; }
        .input-wrapper span { 
            position: absolute; 
            left: 12px; 
            top: 50%; 
            transform: translateY(-50%); 
            font-weight: 600; 
            color: #94a3b8;
            font-size: 14px;
        }

        .input-wrapper input { 
            width: 100%; 
            padding: 12px 12px 12px 40px; 
            border: 1.5px solid #e2e8f0; 
            border-radius: 10px; 
            font-size: 15px; 
            font-family: 'Poppins', sans-serif;
            color: #1e293b;
            font-weight: 600;
            outline: none;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .input-wrapper input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0,0,0,0.05); }

        .btn-save { 
            background: var(--accent); 
            color: white; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        .btn-save:hover { filter: brightness(90%); }

        /* Alert/Status */
        .alert-success { 
            background: #dcfce7; 
            color: #166534; 
            padding: 15px; 
            border-radius: 10px; 
            margin-bottom: 20px; 
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 1024px) { .grid-input { grid-template-columns: 1fr; } }
    </style>
</head>

<body>

    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <?php include "../admin/header.php"; ?>

        <div class="page-header">
            <h2>💰 Standar Gaji & Tunjangan</h2>
            <p>Atur konfigurasi keuangan untuk setiap jabatan pegawai.</p>
        </div>

        <?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil'): ?>
            <div class="alert-success">
                <i class="fa-solid fa-circle-check"></i> Data gaji berhasil diperbarui secara sistem.
            </div>
        <?php endif; ?>

        <?php while ($row = mysqli_fetch_assoc($query_jabatan)): ?>
            <div class="card-gaji">
                <form method="POST">
                    <input type="hidden" name="id_jabatan" value="<?= $row['id_jabatan'] ?>">
                    
                    <div class="card-title-bar">
                        <h3><i class="fa-solid fa-briefcase"></i> <?= $row['nama_jabatan'] ?></h3>
                        <button type="submit" name="update_gaji" class="btn-save">
                            <i class="fa-solid fa-floppy-disk"></i> Update Data
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="grid-input">
                            <div class="input-group">
                                <label>Gaji Pokok</label>
                                <div class="input-wrapper">
                                    <span>Rp</span>
                                    <input type="number" name="gaji_pokok" value="<?= $row['gaji_pokok'] ?>" placeholder="0" required>
                                </div>
                            </div>
                            
                            <div class="input-group">
                                <label>Tunjangan Jabatan</label>
                                <div class="input-wrapper">
                                    <span>Rp</span>
                                    <input type="number" name="tunjangan" value="<?= $row['tunjangan'] ?>" placeholder="0" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label>Uang Makan / Hari</label>
                                <div class="input-wrapper">
                                    <span>Rp</span>
                                    <input type="number" name="uang_makan" value="<?= $row['uang_makan'] ?>" placeholder="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        <?php endwhile; ?>
    </div>

</body>
</html>