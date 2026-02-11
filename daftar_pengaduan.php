<?php
session_start();

/* ===== CEK LOGIN ===== */
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("Location: login.php");
    exit;
}  

/* ===== KONEKSI DATABASE ===== */
$conn = new mysqli("localhost", "root", "", "ukk");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

/* ===== LOGOUT: OTOMATIS KE INDEX.PHP ===== */
if(isset($_GET['logout'])){
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

/* ===== AMBIL DATA FILTER ===== */
$kategori_filter = $_GET['kategori'] ?? '';
$search = $_GET['search'] ?? '';
$filter_tanggal = $_GET['filter_tanggal'] ?? ''; 
$filter_bulan = $_GET['filter_bulan'] ?? '';     
$kategori_result = $conn->query("SELECT * FROM kategori");

/* ===== QUERY DATA PENGADUAN ===== */
$sql = "
    SELECT p.*, k.nama_kategori
    FROM pengaduan p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    WHERE 1
";

if($kategori_filter) {
    $sql .= " AND p.id_kategori=" . intval($kategori_filter);
}

if($search) {
    $search_esc = $conn->real_escape_string($search);
    $sql .= " AND (p.pelapor LIKE '%$search_esc%' OR p.isi LIKE '%$search_esc%' OR p.lokasi LIKE '%$search_esc%' OR p.nis LIKE '%$search_esc%')";
}

if($filter_tanggal) {
    $sql .= " AND DATE(p.tanggal) = '$filter_tanggal'";
}

if($filter_bulan) {
    $sql .= " AND p.tanggal LIKE '$filter_bulan%'";
}

$sql .= " ORDER BY p.tanggal DESC";
$result = $conn->query($sql);

$total_query = $conn->query("SELECT COUNT(*) as total FROM pengaduan");
$total_data = $total_query->fetch_assoc();
$total_laporan = $total_data['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengaduan | SMKN 12 Malang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { background:#f4f7fe; font-family:'Poppins',sans-serif; padding-top:90px; color:#334155; transition: 0.3s; }
        .navbar { background:#ffffff; box-shadow:0 2px 15px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight:700; color:#1e293b !important; }
        .card { border:none; border-radius:15px; box-shadow:0 4px 12px rgba(0,0,0,0.03); transition: 0.3s; }
        .filter-section { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; }
        .form-label-custom { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 5px; display: block; }
        .form-control, .form-select { border-radius: 10px; padding: 10px 15px; border: 1px solid #e2e8f0; font-size: 0.9rem; }
        .btn-filter { border-radius: 10px; padding: 10px; font-weight: 600; transition: 0.3s; }
        .btn-reset { font-size: 0.85rem; color: #ef4444; text-decoration: none; }
        .table thead { background:#f8fafc; color:#64748b; text-transform:uppercase; font-size:0.8rem; letter-spacing:1px; }
        .badge-status { padding:6px 14px; border-radius:8px; font-size:12px; font-weight:600; }
        .pending { background:#fff7ed; color:#f59e0b; }
        .diproses { background:#eef2ff; color:#4f46e5; }
        .selesai { background:#ecfdf5; color:#10b981; }
        .img-thumbnail-custom { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; cursor: pointer; transition: 0.3s; }
        
        body.dark-mode { background: #0f172a; color: #cbd5e1; }
        body.dark-mode .navbar { background: #1e293b; border-bottom: 1px solid #334155; }
        body.dark-mode .navbar-brand, body.dark-mode .nav-link { color: #f1f5f9 !important; }
        body.dark-mode .card, body.dark-mode .filter-section { background: #1e293b; color: #f1f5f9; border-color: #334155; }
        body.dark-mode .table { color: #cbd5e1; }
        body.dark-mode .table thead { background: #334155; color: #94a3b8; }
        body.dark-mode .form-control, body.dark-mode .form-select { background: #334155; border-color: #475569; color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container px-4">
        <a class="navbar-brand d-flex align-items-center" href="dashboard_admin.php">
            <img src="assets/img/smk.png" width="40" class="me-2 rounded"> SMKN 12 MALANG
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item me-1"><button id="darkModeBtn" class="btn btn-sm btn-outline-secondary rounded-circle">🌙</button></li>
                <li class="nav-item me-3 position-relative">
                    <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none; font-size: 0.6rem;">!</span>
                    <a class="nav-link p-0" href="#" id="notifIcon">🔔</a>
                </li>
                <li class="nav-item"><a class="nav-link px-3" href="dasbhoard_admin.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link px-3 active text-primary fw-bold" href="daftar_pengaduan.php">Daftar Pengaduan</a></li>
                     <li class="nav-item"><a class="nav-link px-3" href="management.php">Management</a></li>
                <li class="nav-item ms-lg-3"><a href="?logout" onclick="return confirm('Keluar?')" class="btn btn-danger rounded-pill px-4 shadow-sm">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold mb-0">Manajemen Pengaduan</h2>
            <p class="text-muted small">Kelola dan pantau semua laporan yang masuk</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="cetak.php?kategori=<?= urlencode($kategori_filter) ?>&search=<?= urlencode($search) ?>&filter_tanggal=<?= $filter_tanggal ?>&filter_bulan=<?= $filter_bulan ?>" 
               target="_blank" class="btn btn-danger px-4 shadow-sm rounded-pill fw-bold">🖨️ Cetak PDF</a>
        </div>
    </div>

    <div class="filter-section p-4 mb-4 shadow-sm">
        <form method="get" class="row g-3">
            <div class="col-md-4">
                <label class="form-label-custom">Cari Laporan</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Nama, NIS, isi, atau lokasi...">
            </div>
            <div class="col-md-2"><label class="form-label-custom">Tanggal</label><input type="date" name="filter_tanggal" value="<?= $filter_tanggal ?>" class="form-control"></div>
            <div class="col-md-2"><label class="form-label-custom">Bulan</label><input type="month" name="filter_bulan" value="<?= $filter_bulan ?>" class="form-control"></div>
            <div class="col-md-2">
                <label class="form-label-custom">Kategori</label>
                <select name="kategori" class="form-select">
                    <option value="">Semua</option>
                    <?php $kategori_result->data_seek(0); while($k = $kategori_result->fetch_assoc()): ?>
                        <option value="<?= $k['id_kategori'] ?>" <?= ($kategori_filter==$k['id_kategori'] ? 'selected' : '') ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary btn-filter w-100">Cari</button></div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">GAMBAR</th> 
                        <th>PELAPOR</th>
                        <th>NIS</th> <th>KATEGORI</th>
                        <th>LOKASI</th> 
                        <th>ISI LAPORAN</th>
                        <th>TANGGAL</th>
                        <th>STATUS</th>
                        <th class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($p = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4">
                                <?php if(!empty($p['foto'])): ?>
                                    <a href="assets/uploads/<?= $p['foto'] ?>" target="_blank"><img src="assets/uploads/<?= $p['foto'] ?>" class="img-thumbnail-custom"></a>
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center border rounded" style="width:60px; height:60px; font-size:0.7rem; color:#cbd5e1;">No Img</div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold"><?= htmlspecialchars($p['pelapor']) ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($p['nis'] ?? '-') ?></td> <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['nama_kategori'] ?? '-') ?></span></td>
                            <td class="text-primary fw-medium small"><?= htmlspecialchars($p['lokasi'] ?? '-') ?></td> 
                            <td class="text-muted" style="max-width: 200px;"><?= htmlspecialchars($p['isi']) ?></td>
                            <td class="small">
                                <?php 
                                    $ts = strtotime($p['tanggal']);
                                    $bln = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
                                    echo date('d', $ts) . " " . $bln[date('m', $ts)] . " " . date('Y', $ts);
                                ?>
                            </td>
                            <td>
                                <?php $st = strtolower($p['status']); ?>
                                <span class="badge-status <?= ($st == '0' ? 'pending' : $st) ?>">
                                    <?= ($p['status'] == '0') ? 'Pending' : ucfirst($p['status']) ?>
                                </span>
                            </td>
                            <td class="text-center btn-aksi">
                                <a href="tanggapi.php?id=<?= $p['id_pengaduan'] ?>" class="btn btn-sm btn-primary px-3 shadow-sm">Tanggapi</a>
                                <a href="hapus.php?id=<?= $p['id_pengaduan'] ?>" class="btn btn-sm btn-outline-danger px-3 ms-1" onclick="return confirm('Hapus?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center py-5 text-muted">Data tidak ditemukan.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Dark Mode Logic
    const btn = document.getElementById('darkModeBtn');
    if(localStorage.getItem('theme') === 'dark'){ document.body.classList.add('dark-mode'); btn.innerHTML = '☀️'; }
    btn.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        btn.innerHTML = isDark ? '☀️' : '🌙';
    });

    // Notif Logic
    const currentTotal = <?= $total_laporan ?>;
    const lastTotal = localStorage.getItem('last_seen_total') || 0;
    const badge = document.getElementById('notifBadge');
    if (currentTotal > lastTotal) badge.style.display = 'inline-block';
    document.getElementById('notifIcon').addEventListener('click', (e) => {
        e.preventDefault();
        if(currentTotal > lastTotal) {
            alert("Ada " + (currentTotal - lastTotal) + " laporan baru!");
            localStorage.setItem('last_seen_total', currentTotal);
            badge.style.display = 'none';
        } else { alert("Tidak ada laporan baru."); }
    });
</script>
</body>
</html>
<?php $conn->close(); ?>