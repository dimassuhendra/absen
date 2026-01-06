<?php
include_once "../db_connect.php";

// Cek hak akses HR
if ($_SESSION['role'] != 'hr') {
    header("Location: ../index.php");
    exit();
}

$set = getSetting($koneksi);

// --- LOGIKA TAMBAH DIVISI ---
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_departemen']);
    mysqli_query($koneksi, "INSERT INTO departemen (nama_departemen) VALUES ('$nama')");
    header("Location: departemen.php?pesan=berhasil");
}

// --- LOGIKA TAMBAH POSISI/JABATAN BARU ---
if (isset($_POST['tambah_jabatan'])) {
    $id_dept = $_POST['id_departemen'];
    $nama_jabatan = mysqli_real_escape_string($koneksi, $_POST['nama_jabatan']);
    mysqli_query($koneksi, "INSERT INTO jabatan (nama_jabatan, id_departemen) VALUES ('$nama_jabatan', '$id_dept')");
    header("Location: departemen.php?pesan=jabatan_ditambah");
}

// --- LOGIKA EDIT DIVISI ---
if (isset($_POST['edit'])) {
    $id = $_POST['id_departemen'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_departemen']);
    $kepala = $_POST['id_kepala'] == "" ? "NULL" : $_POST['id_kepala'];
    mysqli_query($koneksi, "UPDATE departemen SET nama_departemen='$nama', id_kepala=$kepala WHERE id_departemen='$id'");
    header("Location: departemen.php?pesan=update");
}

// --- LOGIKA TAMBAH ANGGOTA ---
if (isset($_POST['tambah_anggota'])) {
    $id_pegawai = $_POST['id_pegawai'];
    $id_jabatan_baru = $_POST['id_jabatan'];
    mysqli_query($koneksi, "UPDATE pegawai SET id_jabatan='$id_jabatan_baru' WHERE id_pegawai='$id_pegawai'");
    header("Location: departemen.php?pesan=anggota_ditambah");
}

// Ambil data departemen
$query_dept = "SELECT d.*, p.nama_lengkap as nama_kepala,
               (SELECT COUNT(p2.id_pegawai) FROM pegawai p2 JOIN jabatan j ON p2.id_jabatan = j.id_jabatan WHERE j.id_departemen = d.id_departemen) as total_anggota
               FROM departemen d LEFT JOIN pegawai p ON d.id_kepala = p.id_pegawai ORDER BY d.nama_departemen ASC";
$data_dept = mysqli_query($koneksi, $query_dept);

// Data untuk Dropdown & Modal Anggota
$list_pegawai = mysqli_query($koneksi, "SELECT * FROM pegawai WHERE role != 'admin' ORDER BY nama_lengkap ASC");
$all_jabatan = mysqli_query($koneksi, "SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
$jabatans = [];
while($j = mysqli_fetch_assoc($all_jabatan)) { $jabatans[] = $j; }

// Ambil SEMUA pegawai untuk difilter via JS di modal daftar anggota
$semua_pegawai = mysqli_query($koneksi, "SELECT p.nama_lengkap, p.nip, j.nama_jabatan, j.id_departemen, p.id_pegawai 
                                         FROM pegawai p 
                                         LEFT JOIN jabatan j ON p.id_jabatan = j.id_jabatan");
$data_pegawai_js = [];
while($pg = mysqli_fetch_assoc($semua_pegawai)) { $data_pegawai_js[] = $pg; }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Departemen | <?= $set['nama_perusahaan'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root { --primary: <?= $set['warna_header'] ?>; --accent: <?= $set['warna_button'] ?>; --font-accent: <?= $set['warna_font'] ?>; }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; }
        .main-content { margin-left: 280px; padding: 20px 40px; }
        .content-body { margin-top: 30px; }
        .dept-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
        .dept-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-top: 5px solid var(--primary); transition: 0.3s; }
        .dept-card:hover { transform: translateY(-5px); }
        .dept-name { font-size: 18px; font-weight: 700; display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .member-badge { background: #e8f0fe; color: #1a73e8; padding: 3px 12px; border-radius: 20px; font-size: 12px; cursor: pointer; }
        .info-item { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; font-size: 13px; color: #636e72; }
        .btn-main { background: var(--accent); color: var(--font-accent); padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .card-footer { margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee; display: flex; flex-wrap: wrap; gap: 5px; }
        .btn-sm { flex: 1; min-width: 40px; padding: 8px; border-radius: 6px; font-size: 11px; border: none; cursor: pointer; text-align: center; font-weight: 600; }
        
        /* Modal Style */
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .modal-content { background: white; width: 500px; margin: 50px auto; border-radius: 15px; padding: 25px; animation: slideDown 0.3s; position: relative; }
        @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .form-group { margin-bottom: 15px; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        
        /* Table inside Modal */
        .table-mini { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 10px; }
        .table-mini th { text-align: left; padding: 10px; background: #f8f9fa; border-bottom: 2px solid #eee; }
        .table-mini td { padding: 10px; border-bottom: 1px solid #eee; }
        .badge-kepala { background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
    </style>
</head>
<body>

<div class="wrapper">
    <?php include "sidebar.php"; ?>
    <div class="main-content">
        <?php include "../admin/header.php"; ?>

        <div class="content-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <h2 style="font-weight: 700; margin: 0;">Struktur Departemen</h2>
                    <p style="color: #636e72; font-size: 14px; margin: 5px 0 0;">Klik jumlah anggota untuk melihat daftar nama.</p>
                </div>
                <button class="btn-main" onclick="showModal('modalTambah')"><i class="fa-solid fa-plus"></i> Divisi Baru</button>
            </div>

            <div class="dept-grid">
                <?php while ($row = mysqli_fetch_assoc($data_dept)): ?>
                <div class="dept-card">
                    <div class="dept-name">
                        <?= $row['nama_departemen'] ?>
                        <span class="member-badge" onclick="viewMembers('<?= $row['id_departemen'] ?>', '<?= $row['nama_departemen'] ?>', '<?= $row['id_kepala'] ?>')">
                            <i class="fa-solid fa-users"></i> <?= $row['total_anggota'] ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <i class="fa-solid fa-user-tie" style="color:var(--primary)"></i>
                        <span>Kepala: <strong><?= $row['nama_kepala'] ?? '<em style="color:#ccc">Belum ditunjuk</em>' ?></strong></span>
                    </div>

                    <div class="card-footer">
                        <button onclick="openAddJabatan('<?= $row['id_departemen'] ?>', '<?= $row['nama_departemen'] ?>')" class="btn-sm" style="background:#f3e5f5; color:#7b1fa2;" title="Tambah Posisi"><i class="fa-solid fa-briefcase"></i> Posisi</button>
                        <button onclick="openAddMember('<?= $row['id_departemen'] ?>', '<?= $row['nama_departemen'] ?>')" class="btn-sm" style="background:#e3f2fd; color:#1976d2;" title="Tambah Anggota"><i class="fa-solid fa-user-plus"></i> Anggota</button>
                        <button onclick="openEdit('<?= $row['id_departemen'] ?>', '<?= $row['nama_departemen'] ?>', '<?= $row['id_kepala'] ?>')" class="btn-sm" style="background:#fff8e1; color:#ffa000;"><i class="fa-solid fa-pen"></i></button>
                        <a href="departemen.php?hapus=<?= $row['id_departemen'] ?>" onclick="return confirm('Hapus divisi ini?')" class="btn-sm" style="background:#ffebee; color:#d32f2f;"><i class="fa-solid fa-trash"></i></a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<div id="modalListAnggota" class="modal">
    <div class="modal-content" style="width: 600px;">
        <h4 id="title_list_dept" style="margin:0;">Daftar Anggota</h4>
        <p style="font-size: 12px; color: #666; margin-bottom: 15px;">Daftar seluruh personil dalam divisi ini.</p>
        
        <div style="max-height: 400px; overflow-y: auto;">
            <table class="table-mini">
                <thead>
                    <tr>
                        <th>NIP</th>
                        <th>Nama Pegawai</th>
                        <th>Jabatan</th>
                    </tr>
                </thead>
                <tbody id="body_list_anggota">
                    </tbody>
            </table>
        </div>
        <button type="button" onclick="closeModal('modalListAnggota')" class="btn-main" style="width:100%; margin-top:20px; background:#eee; color:#333;">Tutup</button>
    </div>
</div>

<div id="modalAddJabatan" class="modal">
    <div class="modal-content">
        <h4>Tambah Posisi</h4>
        <form method="POST">
            <input type="hidden" name="id_departemen" id="id_dept_jabatan">
            <div class="form-group"><label>Nama Posisi</label><input type="text" name="nama_jabatan" class="form-control" placeholder="Contoh: Barista" required></div>
            <button type="submit" name="tambah_jabatan" class="btn-main" style="width:100%">Simpan Posisi</button>
            <button type="button" onclick="closeModal('modalAddJabatan')" style="width:100%; background:none; border:none; margin-top:10px; cursor:pointer;">Batal</button>
        </form>
    </div>
</div>

<div id="modalAddMember" class="modal">
    <div class="modal-content">
        <h4>Tambah Anggota</h4>
        <form method="POST">
            <input type="hidden" name="id_departemen" id="add_member_dept_id">
            <div class="form-group">
                <label>Pilih Pegawai</label>
                <select name="id_pegawai" class="form-control" required>
                    <?php mysqli_data_seek($list_pegawai, 0); while($p = mysqli_fetch_assoc($list_pegawai)): ?>
                        <option value="<?= $p['id_pegawai'] ?>"><?= $p['nama_lengkap'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Pilih Jabatan</label>
                <select name="id_jabatan" id="dropdown_jabatan_dept" class="form-control" required></select>
            </div>
            <button type="submit" name="tambah_anggota" class="btn-main" style="width:100%">Update Anggota</button>
            <button type="button" onclick="closeModal('modalAddMember')" style="width:100%; background:none; border:none; margin-top:10px; cursor:pointer;">Batal</button>
        </form>
    </div>
</div>

<div id="modalEdit" class="modal">
    <div class="modal-content">
        <h4>Edit Departemen</h4>
        <form method="POST">
            <input type="hidden" name="id_departemen" id="edit_id">
            <div class="form-group"><label>Nama Departemen</label><input type="text" name="nama_departemen" id="edit_nama" class="form-control" required></div>
            <div class="form-group">
                <label>Kepala Departemen</label>
                <select name="id_kepala" id="edit_kepala" class="form-control">
                    <option value="">-- Tanpa Kepala --</option>
                    <?php mysqli_data_seek($list_pegawai, 0); while($p = mysqli_fetch_assoc($list_pegawai)): ?>
                        <option value="<?= $p['id_pegawai'] ?>"><?= $p['nama_lengkap'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="edit" class="btn-main" style="width:100%">Update</button>
            <button type="button" onclick="closeModal('modalEdit')" style="width:100%; background:none; border:none; margin-top:10px; cursor:pointer;">Batal</button>
        </form>
    </div>
</div>

<div id="modalTambah" class="modal">
    <div class="modal-content">
        <h4>Tambah Departemen</h4>
        <form method="POST">
            <div class="form-group"><label>Nama Departemen</label><input type="text" name="nama_departemen" class="form-control" required></div>
            <button type="submit" name="tambah" class="btn-main" style="width:100%">Simpan</button>
            <button type="button" onclick="closeModal('modalTambah')" style="width:100%; background:none; border:none; margin-top:10px; cursor:pointer;">Batal</button>
        </form>
    </div>
</div>

<script>
    const jabatans = <?= json_encode($jabatans) ?>;
    const pegawai = <?= json_encode($data_pegawai_js) ?>;

    function showModal(id) { document.getElementById(id).style.display = 'block'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    // FITUR LIHAT ANGGOTA
    function viewMembers(idDept, namaDept, idKepala) {
        document.getElementById('title_list_dept').innerText = "Anggota " + namaDept;
        const body = document.getElementById('body_list_anggota');
        body.innerHTML = '';

        const members = pegawai.filter(p => p.id_departemen == idDept);

        if(members.length === 0) {
            body.innerHTML = '<tr><td colspan="3" align="center">Belum ada anggota di divisi ini.</td></tr>';
        } else {
            members.forEach(m => {
                let isKepala = (m.id_pegawai == idKepala) ? ' <span class="badge-kepala">KEPALA</span>' : '';
                body.innerHTML += `
                    <tr>
                        <td><code>${m.nip}</code></td>
                        <td>${m.nama_lengkap}${isKepala}</td>
                        <td>${m.nama_jabatan}</td>
                    </tr>
                `;
            });
        }
        showModal('modalListAnggota');
    }

    function openAddJabatan(id, nama) {
        document.getElementById('id_dept_jabatan').value = id;
        showModal('modalAddJabatan');
    }

    function openAddMember(id, nama) {
        document.getElementById('add_member_dept_id').value = id;
        const drop = document.getElementById('dropdown_jabatan_dept');
        drop.innerHTML = '';
        const filtered = jabatans.filter(j => j.id_departemen == id);
        filtered.forEach(j => {
            let opt = document.createElement('option');
            opt.value = j.id_jabatan; opt.text = j.nama_jabatan; drop.add(opt);
        });
        showModal('modalAddMember');
    }

    function openEdit(id, nama, kepala) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_kepala').value = kepala;
        showModal('modalEdit');
    }

    window.onclick = function(e) { if (e.target.className === 'modal') { closeModal(e.target.id); } }
</script>

</body>
</html>