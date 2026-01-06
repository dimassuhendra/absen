<?php
include "../db_connect.php";

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}
$set = getSetting($koneksi);

// Proses Update Setting
if (isset($_POST['simpan_setting'])) {
    $nama_per = mysqli_real_escape_string($koneksi, $_POST['nama_perusahaan']);
    $w_header = $_POST['warna_header'];
    $w_button = $_POST['warna_button'];
    $w_font = $_POST['warna_font'];
    $footer_txt = mysqli_real_escape_string($koneksi, $_POST['footer_text']);

    // Logika Upload Logo
    $logo_name = $set['logo'];
    if ($_FILES['logo']['name'] != "") {
        $target_dir = "../assets/img/";
        $logo_name = time() . "_" . $_FILES['logo']['name'];
        move_uploaded_file($_FILES['logo']['tmp_name'], $target_dir . $logo_name);
    }

    $query = "UPDATE setting_web SET 
                nama_perusahaan='$nama_per', 
                logo='$logo_name', 
                warna_header='$w_header', 
                warna_button='$w_button', 
                warna_font='$w_font', 
                footer_text='$footer_txt' 
              WHERE id_setting=1";

    if (mysqli_query($koneksi, $query)) {
        header("Location: setting_web.php?status=success");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Web | <?= $set['nama_perusahaan'] ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />

    <style>
        :root {
            --primary-color:
                <?= $set['warna_header'] ?>
            ;
            --accent-color:
                <?= $set['warna_button'] ?>
            ;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fa;
            display: flex;
        }

        .main-content {
            margin-left: 320px;
            padding: 30px;
            width: 100%;
        }

        .config-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            border: none;
        }

        /* Styling Input */
        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #b2bec3;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .form-control-custom {
            background: #f8f9fa;
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 12px 15px;
            transition: 0.3s;
        }

        .form-control-custom:focus {
            background: white;
            border-color: var(--primary-color);
            box-shadow: none;
        }

        /* Color Picker Styling */
        .color-wrapper {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            border: 1px solid #eee;
        }

        .color-picker {
            width: 45px;
            height: 45px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            overflow: hidden;
            background: none;
        }

        .color-picker::-webkit-color-swatch {
            border: none;
            border-radius: 10px;
        }

        /* Logo Preview */
        .logo-preview-box {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            background: #f8f9fa;
            border: 2px dashed #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            position: relative;
            overflow: hidden;
        }

        .logo-preview-box img {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
        }

        .btn-save {
            background: var(--accent-color);
            color:
                <?= $set['warna_font'] ?>
            ;
            border: none;
            padding: 15px 30px;
            border-radius: 15px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .btn-save:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 25px;
            color: #2d3436;
        }
    </style>
</head>

<body>

    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="mb-4">
            <h4 class="fw-bold mb-0">⚙️ Pengaturan Web</h4>
            <p class="text-muted small">Personalisasi tampilan dan identitas instansi Anda</p>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert alert-success border-0 rounded-4 mb-4">
                <i class="fa-solid fa-circle-check me-2"></i> Perubahan berhasil disimpan dan diterapkan!
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="config-card h-100">
                        <h5 class="section-title"><i class="fa-solid fa-id-card me-2 text-primary"></i>Identitas
                            Instansi</h5>

                        <div class="mb-4">
                            <label class="form-label text-uppercase">Nama Perusahaan / Instansi</label>
                            <input type="text" name="nama_perusahaan" class="form-control form-control-custom"
                                value="<?= $set['nama_perusahaan'] ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-uppercase">Logo Saat Ini</label>
                            <div class="d-flex align-items-center gap-4">
                                <div class="logo-preview-box">
                                    <img src="../assets/img/<?= $set['logo'] ?>" id="previewLogo">
                                </div>
                                <div>
                                    <input type="file" name="logo" id="inputLogo"
                                        class="form-control form-control-custom" accept="image/*">
                                    <small class="text-muted d-block mt-2">Format: PNG, JPG (Transparan
                                        disarankan)</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label text-uppercase">Teks Kaki (Footer)</label>
                            <textarea name="footer_text" rows="4"
                                class="form-control form-control-custom"><?= $set['footer_text'] ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="config-card h-100">
                        <h5 class="section-title"><i class="fa-solid fa-palette me-2 text-primary"></i>Tema Visual</h5>

                        <div class="mb-4">
                            <label class="form-label text-uppercase">Warna Utama (Header & Sidebar)</label>
                            <div class="color-wrapper">
                                <input type="color" name="warna_header" class="color-picker"
                                    value="<?= $set['warna_header'] ?>">
                                <div>
                                    <div class="fw-bold" style="font-size: 0.9rem;">Primary Theme</div>
                                    <small class="text-muted">Mempengaruhi area navigasi utama</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-uppercase">Warna Tombol (Accent)</label>
                            <div class="color-wrapper">
                                <input type="color" name="warna_button" class="color-picker"
                                    value="<?= $set['warna_button'] ?>">
                                <div>
                                    <div class="fw-bold" style="font-size: 0.9rem;">Button Color</div>
                                    <small class="text-muted">Mempengaruhi tombol aksi sistem</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-uppercase">Warna Kontras Teks</label>
                            <div class="color-wrapper">
                                <input type="color" name="warna_font" class="color-picker"
                                    value="<?= $set['warna_font'] ?>">
                                <div>
                                    <div class="fw-bold" style="font-size: 0.9rem;">Font Contrast</div>
                                    <small class="text-muted">Warna teks di atas warna utama</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light border-0 small rounded-4">
                            <i class="fa-solid fa-circle-info me-2 text-primary"></i>
                            Tips: Gunakan warna yang kontras agar teks tetap mudah dibaca.
                        </div>

                        <button type="submit" name="simpan_setting" class="btn-save shadow">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Script sederhana untuk preview gambar sebelum upload
        inputLogo.onchange = evt => {
            const [file] = inputLogo.files
            if (file) {
                previewLogo.src = URL.createObjectURL(file)
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>