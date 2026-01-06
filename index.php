<?php
include "db_connect.php";
$set = getSetting($koneksi);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Login |
        <?= $set['nama_perusahaan'] ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <style>
        :root {
            --primary-bg: <?= $set['warna_header'] ?>;
            --accent-color: <?= $set['warna_button'] ?>;
            --font-color: <?= $set['warna_font'] ?>;
            --dark-text: #343a40;
            --sub-text: #6c757d;
            --font-family-main: "Poppins", sans-serif;
        }

        body {
            font-family: var(--font-family-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f4f7fa;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.08'%3E%3Cpath d='M92.4 44.4C91 44.5 90 45.5 90 46.9c.1 1.4 1.1 2.4 2.5 2.4s2.4-1.1 2.4-2.5c0-1.4-1-2.4-2.5-2.4zM10 50c0-1.4-1.1-2.5-2.5-2.5S5 48.6 5 50s1.1 2.5 2.5 2.5S10 51.4 10 50zm90 0c0-1.4-1.1-2.5-2.5-2.5S95 48.6 95 50s1.1 2.5 2.5 2.5S100 51.4 100 50zM7.5 47.5c-1.4 0-2.5 1.1-2.5 2.5s1.1 2.5 2.5 2.5 2.5-1.1 2.5-2.5-1.1-2.5-2.5-2.5zM2.4 44.4C1 44.5 0 45.5 0 46.9c.1 1.4 1.1 2.4 2.5 2.4s2.4-1.1 2.4-2.5c0-1.4-1-2.4-2.5-2.4z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .login-container {
            width: 95%;
            max-width: 900px;
            margin: 20px auto;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            border-radius: 24px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: white;
            transition: all 0.3s ease-in-out;
        }

        /* --- Sisi Kiri: Visual Area --- */
        .visual-area {
            background: linear-gradient(135deg, var(--primary-bg), #2c3e50);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
            color: white;
            text-align: center;
        }

        .tech-silhouette {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.1;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1000 1000'%3E%3Cpath fill='white' d='M100,200 L150,200 L150,150 L250,150 L250,250 L200,250 L200,350 L300,350 L300,300 L400,300 L400,450 L350,450 L350,550 L500,550 L500,500 L600,500 L600,650 L550,650 L550,750 L700,750 L700,700 L800,700 L800,850' /%3E%3Ccircle fill='white' cx='100' cy='200' r='10'/%3E%3Ccircle fill='white' cx='250' cy='250' r='10'/%3E%3Ccircle fill='white' cx='400' cy='450' r='10'/%3E%3Ccircle fill='white' cx='600' cy='650' r='10'/%3E%3C/svg%3E");
            background-size: cover;
            pointer-events: none;
        }

        .visual-area img.logo-main {
            width: 100px;
            margin-bottom: 20px;
            z-index: 2;
            background: white;
            padding: 10px;
            border-radius: 15px;
        }

        /* --- Sisi Kanan: Form Area --- */
        .login-form-area {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px;
            background-color: white;
        }

        .logo-header {
            margin-bottom: 25px;
            text-align: center;
        }

        /* Logo yang muncul hanya di Mobile */
        .mobile-logo-header {
            display: none;
            text-align: center;
            margin-bottom: 20px;
        }

        .mobile-logo-header img {
            width: 60px;
            margin-bottom: 10px;
        }

        /* Role Switcher */
        .role-selector {
            display: flex;
            background: #f0f2f5;
            padding: 5px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .role-item {
            flex: 1;
        }

        .role-item input {
            display: none;
        }

        .role-item label {
            display: block;
            text-align: center;
            padding: 10px 5px;
            cursor: pointer;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: var(--sub-text);
            transition: all 0.3s;
        }

        .role-item input:checked+label {
            background: white;
            color: var(--primary-bg);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 15px;
        }

        .input-group-custom i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--sub-text);
        }

        .input-group-custom input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1.5px solid #ececec;
            border-radius: 12px;
            outline: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .input-group-custom input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.03);
        }

        .btn-login {
            background-color: var(--accent-color);
            color: var(--font-color);
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            margin-top: 10px;
            transition: 0.3s;
        }

        /* --- RESPONSIVE BREAKPOINTS --- */

        /* Tablet (max-width: 992px) */
        @media (max-width: 992px) {
            .login-container {
                max-width: 800px;
            }

            .login-form-area {
                padding: 30px;
            }
        }

        /* Mobile (max-width: 768px) */
        @media (max-width: 768px) {
            body {
                align-items: flex-start;
                /* Supaya bisa di-scroll jika keyboard muncul */
                padding-top: 20px;
            }

            .login-container {
                grid-template-columns: 1fr;
                /* Jadi satu kolom stack ke bawah */
                max-width: 400px;
                border-radius: 20px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            }

            .visual-area {
                display: none;
                /* Sembunyikan area biru di HP agar fokus ke form */
            }

            .mobile-logo-header {
                display: block;
                /* Munculkan logo kecil di atas form */
            }

            .login-form-area {
                padding: 30px 20px;
            }

            .role-item label {
                font-size: 12px;
            }
        }

        /* Small Screen / Android Kecil (max-width: 360px) */
        @media (max-width: 360px) {
            .login-container {
                width: 98%;
                margin: 10px auto;
            }

            .login-form-area {
                padding: 20px 15px;
            }

            .role-selector {
                margin-bottom: 15px;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="visual-area">
            <div class="tech-silhouette"></div>
            <img src="assets/img/<?= $set['logo'] ?>" alt="Logo" class="logo-main shadow" />
            <h2 class="fw-bold">
                <?= $set['nama_perusahaan'] ?>
            </h2>
            <p class="opacity-75 mt-2">SIM Kepegawaian</p>
            <div class="mt-4 border-top border-white border-opacity-25 pt-4 w-75">
                <small class="fst-italic text-white text-opacity-75">"Efisiensi dalam setiap langkah kerja
                    kita."</small>
            </div>
        </div>

        <div class="login-form-area">
            <div class="mobile-logo-header">
                <img src="assets/img/<?= $set['logo'] ?>" alt="Logo" />
                <h5 class="fw-bold mb-0">
                    <?= $set['nama_perusahaan'] ?>
                </h5>
            </div>

            <div class="logo-header">
                <h3 class="fw-bold text-dark">Selamat Datang</h3>
                <p class="text-muted small">Login untuk akses dashboard</p>
            </div>

            <form action="proses/proses_login.php" method="POST">
                <div class="role-selector">
                    <div class="role-item">
                        <input type="radio" name="role" value="pegawai" id="r_pegawai" checked>
                        <label for="r_pegawai">Pegawai</label>
                    </div>
                    <div class="role-item">
                        <input type="radio" name="role" value="hr" id="r_hr">
                        <label for="r_hr">HRD</label>
                    </div>
                    <div class="role-item">
                        <input type="radio" name="role" value="admin" id="r_admin">
                        <label for="r_admin">Admin</label>
                    </div>
                </div>

                <div class="input-group-custom">
                    <i class="fas fa-user-circle"></i>
                    <input type="text" name="identifier" placeholder="Email atau NIP" required autocomplete="off">
                </div>

                <div class="input-group-custom">
                    <i class="fas fa-key"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="btn-login">
                    LOGIN SEKARANG <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </form>

            <div class="text-center mt-4 text-muted" style="font-size: 11px;">
                &copy; 2026
                <?= $set['nama_perusahaan'] ?> <br>
                Powered by CMS Company
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>