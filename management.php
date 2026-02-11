<?php
session_start();

/* ===== PROTEKSI HALAMAN ===== */
if(!isset($_SESSION['status']) || $_SESSION['status'] != "login"){
    header("Location: login.php");
    exit;
}

// Koneksi DB
$conn = new mysqli("localhost","root","","ukk");
if($conn->connect_error) die("Koneksi gagal: ".$conn->connect_error);

/* ===== LOGOUT ===== */
if(isset($_GET['logout'])){
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

/* ===== PROSES TAMBAH ADMIN ===== */
if(isset($_POST['add_admin'])){
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $guru     = $conn->real_escape_string($_POST['guru']);
    
    $conn->query("INSERT INTO admin (username, password, guru) VALUES ('$username', '$password', '$guru')");
    header("Location: management.php");
    exit;
}

/* ===== PROSES EDIT SISWA ===== */
if(isset($_POST['edit_siswa'])){
    $nis_lama = $_POST['nis_lama'];
    $nis_baru = $_POST['nis'];
    $nama     = $_POST['nama'];

    // Update di tabel siswa
    $q = $conn->query("UPDATE siswa SET nis='$nis_baru', nama_siswa='$nama' WHERE nis='$nis_lama'");
    
    // Jika gagal di tabel siswa, coba di tabel masyarakat
    if(!$q){
        $conn->query("UPDATE masyarakat SET nik='$nis_baru', nama='$nama' WHERE nik='$nis_lama'");
    }
    header("Location: management.php?msg=updated");
    exit;
}

/* ===== PROSES TAMBAH SISWA ===== */
if(isset($_POST['add_siswa'])){
    $nis      = $conn->real_escape_string($_POST['nis']);
    $nama     = $conn->real_escape_string($_POST['nama']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = "INSERT INTO siswa (nis, nama_siswa, password) VALUES ('$nis', '$nama', '$password')";
    if(!$conn->query($query)){
        $query_alt = "INSERT INTO masyarakat (nik, nama, username, password, telp) VALUES ('$nis', '$nama', '$nis', '$password', '-')";
        $conn->query($query_alt);
    }
    header("Location: management.php?status=sukses");
    exit;
}

/* ===== PROSES DELETE ===== */
if(isset($_GET['delete_admin'])){
    $id = $_GET['delete_admin'];
    $conn->query("DELETE FROM admin WHERE id=$id");
    header("Location: management.php"); exit;
}

if(isset($_GET['delete_siswa'])){
    $nis = $_GET['delete_siswa'];
    $conn->query("DELETE FROM siswa WHERE nis='$nis'");
    $conn->query("DELETE FROM masyarakat WHERE nik='$nis'");
    header("Location: management.php"); exit;
}

// Ambil total pengaduan untuk notifikasi
$total_notif = 0;
$q_notif = $conn->query("SELECT COUNT(*) as total FROM pengaduan");
if($q_notif) $total_notif = $q_notif->fetch_assoc()['total'];

// Ambil data admin
$result_admin = $conn->query("SELECT * FROM admin");

// Ambil data siswa
$res_siswa = $conn->query("SELECT * FROM siswa"); 
if(!$res_siswa){
    $res_siswa = $conn->query("SELECT nik as nis, nama as nama_siswa FROM masyarakat");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Management Sistem | SMKN 12 Malang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background:#f4f7fe; font-family:'Poppins',sans-serif; padding-top:90px; color:#334155; transition: 0.3s; }
        .navbar { background:#ffffff; box-shadow:0 2px 15px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight:700; color:#1e293b !important; }
        .card { border:none; border-radius:15px; box-shadow:0 4px 12px rgba(0,0,0,0.03); margin-bottom: 20px; transition: 0.3s; }
        
        /* ===== CSS MODE MALAM ===== */
        body.dark-mode { background: #0f172a; color: #cbd5e1; }
        body.dark-mode .navbar { background: #1e293b; border-bottom: 1px solid #334155; }
        body.dark-mode .navbar-brand, body.dark-mode .nav-link { color: #f1f5f9 !important; }
        body.dark-mode .card { background: #1e293b; color: #f1f5f9; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        body.dark-mode .table { color: #cbd5e1; }
        body.dark-mode .form-control { background: #334155; border-color: #475569; color: white; }
        body.dark-mode .modal-content { background: #1e293b; color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container px-4">
        <a class="navbar-brand d-flex align-items-center" href="dashboard_admin.php">
            <img src="assets/img/smk.png" width="40" class="me-2 rounded">
            SMKN 12 MALANG
        </a>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item me-1">
                    <button id="darkModeBtn" class="btn btn-sm btn-outline-secondary rounded-circle">🌙</button>
                </li>
                <li class="nav-item me-3 position-relative">
                    <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none; font-size: 0.6rem;">!</span>
                    <a class="nav-link p-0" href="#" id="notifIcon">🔔</a>
                </li>

                <li class="nav-item"><a class="nav-link px-3" href="dasbhoard_admin.php">Dasbhoard</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="daftar_pengaduan.php">Daftar Pengaduan</a></li>
                <li class="nav-item"><a class="nav-link px-3 active text-primary fw-bold" href="management.php">Management</a></li>
                <li class="nav-item ms-lg-3">
                    <a href="?logout" onclick="return confirm('Yakin ingin keluar?')" class="btn btn-danger rounded-pill px-4 shadow-sm">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-lg-4">
            <div class="card p-3">
                <h6 class="fw-bold text-primary mb-3">Tambah Akun Siswa</h6>
                <form method="post">
                    <input type="text" name="nis" class="form-control mb-2" placeholder="NIS" required>
                    <input type="text" name="nama" class="form-control mb-2" placeholder="Nama Lengkap" required>
                    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
                    <button type="submit" name="add_siswa" class="btn btn-primary w-100">Simpan Siswa</button>
                </form>
            </div>
            
            <div class="card p-3">
                <h6 class="fw-bold text-secondary mb-3">Tambah Admin</h6>
                <form method="post">
                    <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
                    <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
                    <input type="text" name="guru" class="form-control mb-3" placeholder="Nama Guru" required>
                    <button type="submit" name="add_admin" class="btn btn-secondary w-100">Simpan Admin</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card p-3">
                <h6 class="fw-bold mb-3">Daftar Siswa Aktif</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($res_siswa && $res_siswa->num_rows > 0): ?>
                                <?php while($s = $res_siswa->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $s['nis'] ?></td>
                                    <td><?= $s['nama_siswa'] ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning" onclick="editSiswa('<?= $s['nis'] ?>', '<?= $s['nama_siswa'] ?>')">Edit</button>
                                        <a href="?delete_siswa=<?= $s['nis'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus siswa?')">Hapus</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted">Belum ada data siswa.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card p-3">
                <h6 class="fw-bold mb-3">Daftar Admin</h6>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($a = $result_admin->fetch_assoc()): ?>
                        <tr>
                            <td><?= $a['username'] ?></td>
                            <td class="text-center">
                                <a href="?delete_admin=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus admin?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius: 15px;">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Edit Data Siswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post">
        <div class="modal-body">
            <input type="hidden" name="nis_lama" id="edit_nis_lama">
            <div class="mb-3">
                <label class="form-label small fw-bold">NIS</label>
                <input type="text" name="nis" id="edit_nis" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Nama Lengkap</label>
                <input type="text" name="nama" id="edit_nama" class="form-control" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="edit_siswa" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// --- FUNGSI EDIT ---
function editSiswa(nis, nama) {
    document.getElementById('edit_nis_lama').value = nis;
    document.getElementById('edit_nis').value = nis;
    document.getElementById('edit_nama').value = nama;
    var myModal = new bootstrap.Modal(document.getElementById('modalEdit'));
    myModal.show();
}

// --- LOGIKA MODE MALAM ---
const btn = document.getElementById('darkModeBtn');
if(localStorage.getItem('theme') === 'dark'){
    document.body.classList.add('dark-mode');
    btn.innerHTML = '☀️';
}
btn.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    if(document.body.classList.contains('dark-mode')){
        localStorage.setItem('theme', 'dark');
        btn.innerHTML = '☀️';
    } else {
        localStorage.setItem('theme', 'light');
        btn.innerHTML = '🌙';
    }
});

// --- LOGIKA NOTIFIKASI ---
const currentTotal = <?= $total_notif ?>;
const lastTotal = localStorage.getItem('last_seen_total') || 0;
const badge = document.getElementById('notifBadge');

if (currentTotal > lastTotal) {
    badge.style.display = 'inline-block';
}

document.getElementById('notifIcon').addEventListener('click', function(e) {
    e.preventDefault();
    if(currentTotal > lastTotal) {
        alert("Ada " + (currentTotal - lastTotal) + " pengaduan baru di sistem!");
        localStorage.setItem('last_seen_total', currentTotal);
        badge.style.display = 'none';
    } else {
        alert("Tidak ada pengaduan baru.");
    }
});
</script>
</body>
</html>