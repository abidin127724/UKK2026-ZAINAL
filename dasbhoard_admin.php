<?php
session_start();

// Proteksi halaman: Jika belum login, tendang ke login.php
if(!isset($_SESSION['status']) || $_SESSION['status'] != "login"){
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost","root","","ukk");
if($conn->connect_error) die("Koneksi gagal: ".$conn->connect_error);

/* ===== LOGOUT: OTOMATIS KE INDEX.PHP ===== */
if(isset($_GET['logout'])){
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

/* ===== AMBIL DATA STATISTIK ===== */
$stats = $conn->query("SELECT status, COUNT(*) as count FROM pengaduan GROUP BY status");
$stat_data = ['Pending' => 0, 'Diproses' => 0, 'Selesai' => 0];

while($row = $stats->fetch_assoc()){
    $db_status = strtolower($row['status']);
    if($db_status == 'pending') $stat_data['Pending'] = $row['count'];
    elseif($db_status == 'proses' || $db_status == 'diproses') $stat_data['Diproses'] = $row['count'];
    elseif($db_status == 'selesai') $stat_data['Selesai'] = $row['count'];
}
$total = array_sum($stat_data);

/* ===== AMBIL DATA OVERVIEW TAMBAHAN ===== */
$kategori_fav = $conn->query("SELECT k.nama_kategori, COUNT(*) as jml FROM pengaduan p JOIN kategori k ON p.id_kategori = k.id_kategori GROUP BY p.id_kategori ORDER BY jml DESC LIMIT 1")->fetch_assoc();
$laporan_hari_ini = $conn->query("SELECT COUNT(*) as jml FROM pengaduan WHERE DATE(tanggal) = CURDATE()")->fetch_assoc();

/* ===== FILTER STATUS DAN SEARCH ===== */
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$query_filtered = "
    SELECT p.*, k.nama_kategori 
    FROM pengaduan p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    WHERE 1
";

if($status_filter != ''){
    $query_filtered .= " AND p.status='".$conn->real_escape_string($status_filter)."'";
}

if(!empty($search)){
    $search_safe = $conn->real_escape_string($search);
    $query_filtered .= " AND (p.pelapor LIKE '%$search_safe%' OR k.nama_kategori LIKE '%$search_safe%' OR p.isi LIKE '%$search_safe%' OR p.lokasi LIKE '%$search_safe%')";
}

$query_filtered .= " ORDER BY p.tanggal DESC";
$filtered_result = $conn->query($query_filtered);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | SMKN 12 Malang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { background:#f4f7fe; font-family:'Poppins',sans-serif; padding-top:90px; color:#334155; transition: 0.3s; }
        .navbar { background:#ffffff; box-shadow:0 2px 15px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight:700; color:#1e293b !important; }
        
        .card { border:none; border-radius:15px; box-shadow:0 4px 12px rgba(0,0,0,0.03); transition:0.3s; }
        .card:hover { transform:translateY(-5px); }
        
        .stat-total { border-left: 5px solid #6366f1; }
        .stat-pending { border-left: 5px solid #f59e0b; }
        .stat-proses { border-left: 5px solid #3b82f6; }
        .stat-selesai { border-left: 5px solid #10b981; }

        .welcome-text { font-weight:700; color:#1e293b; letter-spacing:-1px; }
        .table thead { background:#f8fafc; color:#64748b; text-transform:uppercase; font-size:0.8rem; letter-spacing:1px; }
        .badge-status { border-radius:8px; padding:5px 12px; font-size:0.8rem; font-weight:600; }
        
        /* Overview Section Styles */
        .overview-box { background: #fff; border-radius: 15px; padding: 25px; border: 1px solid rgba(0,0,0,0.05); position: relative; overflow: hidden; }
        .icon-circle { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .clock-text { font-size: 1.8rem; font-weight: 700; color: #1e293b; margin: 0; }

        /* Style untuk Gambar di Tabel agar Berfungsi Baik */
        .img-preview { 
            width: 60px; 
            height: 45px; 
            object-fit: cover; 
            border-radius: 6px; 
            border: 1px solid #ddd;
            transition: 0.2s; 
        }
        .img-preview:hover { transform: scale(1.2); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }

        /* ===== CSS MODE MALAM ===== */
        body.dark-mode { background: #0f172a; color: #cbd5e1; }
        body.dark-mode .navbar { background: #1e293b; border-bottom: 1px solid #334155; }
        body.dark-mode .navbar-brand, body.dark-mode .nav-link { color: #f1f5f9 !important; }
        body.dark-mode .card, body.dark-mode .overview-box { background: #1e293b; color: #f1f5f9; box-shadow: 0 4px 12px rgba(0,0,0,0.2); border-color: #334155; }
        body.dark-mode .welcome-text, body.dark-mode .clock-text { color: #f8fafc; }
        body.dark-mode .table { color: #cbd5e1; }
        body.dark-mode .table thead { background: #334155; color: #94a3b8; }
        body.dark-mode .bg-white { background-color: #1e293b !important; }
        body.dark-mode .form-control, body.dark-mode .form-select { background: #334155; border-color: #475569; color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container px-4">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="assets/img/smk.png" width="40" class="me-2 rounded">
            SMKN 12 MALANG
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item me-2">
                    <button id="darkModeBtn" class="btn btn-sm btn-outline-secondary rounded-circle">🌙</button>
                </li>
                <li class="nav-item me-3 position-relative">
                    <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none; font-size: 0.6rem;">Baru</span>
                    <a class="nav-link p-0" href="#" title="Notifikasi" id="notifIcon">🔔</a>
                </li>
                <li class="nav-item"><a class="nav-link px-3 active text-primary fw-bold" href="dasbhoard_admin.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="daftar_pengaduan.php">Daftar Pengaduan</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="management.php">Management</a></li>
                <li class="nav-item ms-lg-3">
                    <a href="?logout" onclick="return confirm('Yakin ingin keluar?')" class="btn btn-danger rounded-pill px-4 shadow-sm">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="welcome-text">Halo, <?= htmlspecialchars($_SESSION['username']) ?> 👋</h1>
            <p class="text-muted">Pantau dan kelola laporan aspirasi siswa hari ini.</p>
        </div>
        <div class="col-md-6">
            <form method="get" class="d-flex gap-2">
                <select name="status" class="form-select shadow-sm w-auto">
                    <option value="">Semua Status</option>
                    <option value="Pending" <?= $status_filter=='Pending'?'selected':'' ?>>Pending</option>
                    <option value="proses" <?= $status_filter=='proses'?'selected':'' ?>>proses</option>
                    <option value="Selesai" <?= $status_filter=='Selesai'?'selected':'' ?>>Selesai</option>
                </select>
                <input type="text" name="search" class="form-control shadow-sm" placeholder="Cari laporan..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary px-4 shadow-sm">Cari</button>
            </form>
        </div>
    </div>

    <div class="row mb-4 g-4">
        <div class="col-md-3">
            <div class="card stat-total p-3">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Total Laporan</small>
                    <h2 class="fw-bold m-0"><?= $total ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-pending p-3">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Pending</small>
                    <h2 class="fw-bold m-0 text-warning"><?= $stat_data['Pending'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-proses p-3">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Diproses</small>
                    <h2 class="fw-bold m-0 text-primary"><?= $stat_data['Diproses'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-selesai p-3">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Selesai</small>
                    <h2 class="fw-bold m-0 text-success"><?= $stat_data['Selesai'] ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5 g-4">
        <div class="col-md-8">
            <div class="overview-box shadow-sm h-100">
                <div class="row align-items-center">
                    <div class="col-md-7 border-end">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-circle bg-primary text-white shadow-sm me-3">📊</div>
                            <h5 class="m-0 fw-bold">Ringkasan Aktivitas</h5>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <p class="text-muted mb-1 small">Laporan Hari Ini</p>
                                <h3 class="fw-bold text-primary mb-0"><?= $laporan_hari_ini['jml'] ?></h3>
                            </div>
                            <div class="col-6">
                                <p class="text-muted mb-1 small">Top Kategori</p>
                                <h6 class="fw-bold mb-0 text-truncate"><?= $kategori_fav['nama_kategori'] ?? '-' ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 ps-md-4 mt-3 mt-md-0">
                        <p class="text-muted mb-0 small">Waktu Real-time</p>
                        <h1 id="liveClock" class="clock-text">00:00:00</h1>
                        <p id="liveDate" class="text-primary small fw-medium mb-0">Memuat tanggal...</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="overview-box shadow-sm h-100 text-center d-flex flex-column justify-content-center">
                <p class="text-muted mb-2 small">Sistem Database</p>
                <div class="d-inline-block bg-success-subtle px-3 py-1 rounded-pill mb-3 mx-auto">
                    <span class="text-success small fw-bold">● Online & Secure</span>
                </div>
                <h6 class="fw-bold small mb-1">Admin Aktif:</h6>
                <p class="text-primary m-0 fw-medium"><?= htmlspecialchars($_SESSION['username']) ?></p>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold">Daftar Pengaduan Terbaru</h5>
            <span class="badge bg-light text-muted fw-normal">Update otomatis setiap refresh</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Pelapor</th>
                        <th>Kategori</th>
                        <th>Lokasi</th> 
                        <th>Isi Laporan</th>
                        <th>Gambar</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($filtered_result && $filtered_result->num_rows > 0): $no = 1; ?>
                        <?php while($row = $filtered_result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= $no++ ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($row['pelapor']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= $row['nama_kategori'] ?></span></td>
                            <td class="text-muted"><small><?= htmlspecialchars($row['lokasi'] ?? '-') ?></small></td> 
                            <td class="text-truncate" style="max-width: 180px;"><?= htmlspecialchars($row['isi']) ?></td>
                            
                            <td>
                                <?php if(!empty($row['foto'])): ?>
                                    <a href="assets/img/<?= $row['foto'] ?>" target="_blank" title="Klik untuk memperbesar">
                                        <img src="assets/img/<?= $row['foto'] ?>" class="img-preview" alt="Lampiran">
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small italic">Tidak ada foto</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php 
                                $st_raw = strtolower($row['status']);
                                if($st_raw == 'pending') { $cls = 'bg-warning text-dark'; $txt = 'Pending'; }
                                elseif($st_raw == 'proses' || $st_raw == 'diproses') { $cls = 'bg-primary'; $txt = 'Diproses'; }
                                elseif($st_raw == 'selesai') { $cls = 'bg-success'; $txt = 'Selesai'; }
                                else { $cls = 'bg-secondary'; $txt = ucfirst($st_raw); }
                                ?>
                                <span class="badge badge-status <?= $cls ?>"><?= $txt ?></span>
                            </td>
                            <td class="small text-muted"><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">Belum ada data laporan masuk.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // --- JAM REAL-TIME ---
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const clockEl = document.getElementById('liveClock');
        if(clockEl) clockEl.textContent = `${h}:${m}:${s}`;
        
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateEl = document.getElementById('liveDate');
        if(dateEl) dateEl.textContent = now.toLocaleDateString('id-ID', options);
    }
    setInterval(updateClock, 1000);
    updateClock();

    // --- MODE MALAM ---
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

    // --- NOTIFIKASI ---
    const currentTotal = <?= $total ?>;
    const lastTotal = localStorage.getItem('last_seen_total') || 0;
    const badge = document.getElementById('notifBadge');
    if (currentTotal > lastTotal) badge.style.display = 'inline-block';

    document.getElementById('notifIcon').addEventListener('click', function() {
        if(currentTotal > lastTotal) {
            alert("Ada " + (currentTotal - lastTotal) + " pengaduan baru masuk!");
            localStorage.setItem('last_seen_total', currentTotal);
            badge.style.display = 'none';
        } else {
            alert("Tidak ada pengaduan baru.");
        }
    });
</script>
</body>
</html>
<?php $conn->close(); ?>