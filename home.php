<?php
/* ===== BAGIAN PHP (TIDAK DIUBAH) ===== 
*/
session_start();

if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:login.php?pesan=belum_login");
    exit;
}

 $conn = new mysqli("localhost","root","","ukk");
if($conn->connect_error){
    die("Koneksi gagal: ".$conn->connect_error);
}

if(isset($_GET['action']) && $_GET['action'] == 'logout'){
    session_unset();
    session_destroy();
    header("Location: index.php"); 
    exit;
}

 $pelapor = $_SESSION['username'] ?? 'Siswa'; 

 $stat_total = $conn->query("SELECT COUNT(*) as total FROM pengaduan")->fetch_assoc()['total'];
 $stat_proses = $conn->query("SELECT COUNT(*) as total FROM pengaduan WHERE status='proses'")->fetch_assoc()['total'];
 $stat_selesai = $conn->query("SELECT COUNT(*) as total FROM pengaduan WHERE status='selesai'")->fetch_assoc()['total'];

 $search = $_GET['search'] ?? '';
 $filter_kategori = $_GET['kategori'] ?? '';

 $sql = "
    SELECT p.*, k.nama_kategori
    FROM pengaduan p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    WHERE 1
";

if(!empty($search)){
    $search_esc = $conn->real_escape_string($search);
    $sql .= " AND (p.pelapor LIKE '%$search_esc%' OR p.isi LIKE '%$search_esc%' OR p.lokasi LIKE '%$search_esc%' OR k.nama_kategori LIKE '%$search_esc%')";
}
if(!empty($filter_kategori)){
    $filter_kategori_esc = $conn->real_escape_string($filter_kategori);
    $sql .= " AND p.id_kategori = '$filter_kategori_esc'";
}
 $sql .= " ORDER BY p.tanggal DESC";
 $result = $conn->query($sql);

 $kategori_list = $conn->query("SELECT * FROM kategori");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pengaduan | SMKN 12 Malang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.6);
            --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }

        body { background-color: var(--bg-body); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--text-main); overflow-x: hidden; }

        /* ... Style Navbar, Hero, Stats Tetap Sama ... */
        .navbar { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-bottom: 1px solid var(--glass-border); padding: 1rem 0; z-index: 1050; transition: all 0.3s ease; }
        .navbar-brand { font-weight: 800; color: var(--text-main); letter-spacing: -0.5px; }
        .navbar-brand span { color: var(--primary); }
        .nav-link { font-weight: 600; color: var(--text-muted) !important; border-radius: 8px; padding: 8px 16px !important; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(79, 70, 229, 0.1); color: var(--primary) !important; }
        .btn-logout { background: #fee2e2; color: #ef4444; border-radius: 12px; padding: 8px 20px; font-weight: 700; font-size: 0.85rem; transition: 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-logout:hover { background: #ef4444; color: white; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); }

        .hero-section { position: relative; background-image: linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.85)), url('assets/img/foto2.jpg'); background-size: cover; background-position: center; background-attachment: fixed; padding: 160px 0 120px 0; color: white; text-align: center; }
        .hero-title { font-weight: 800; letter-spacing: -1px; line-height: 1.2; background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 1.5rem; }
        .hero-section p.lead { color: rgba(255, 255, 255, 0.8); max-width: 700px; margin: 0 auto 2.5rem auto; font-size: 1.25rem; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); padding: 8px 16px; border-radius: 50px; color: white; font-size: 0.85rem; margin-bottom: 20px; }

        .stats-container { margin-top: -60px; position: relative; z-index: 10; }
        .stat-card { background: var(--surface); border-radius: 20px; padding: 25px; border: 1px solid rgba(226, 232, 240, 0.8); box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); height: 100%; }
        .stat-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px -5px rgba(79, 70, 229, 0.15); border-color: var(--primary); }
        .stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }

        .main-content-card { background: var(--glass-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 30px; box-shadow: var(--glass-shadow); padding: 40px; margin-top: 60px; margin-bottom: 60px; }
        .filter-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; margin-bottom: 30px; }
        
        /* STYLE TAMBAHAN UNTUK GAMBAR */
        .img-thumbnail-custom {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .img-thumbnail-custom:hover {
            transform: scale(1.1);
            z-index: 5;
        }

        .table-custom { border-collapse: separate; border-spacing: 0 12px; width: 100%; }
        .table-custom thead th { border: none; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; padding: 15px 20px; font-weight: 700; background: transparent; }
        .table-custom tbody tr { background: white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02); transition: all 0.2s ease; }
        .table-custom tbody td { padding: 20px; vertical-align: middle; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; }
        .table-custom tbody tr td:first-child { border-left: 1px solid #f1f5f9; border-radius: 16px 0 0 16px; }
        .table-custom tbody tr td:last-child { border-right: 1px solid #f1f5f9; border-radius: 0 16px 16px 0; }
        .table-custom tbody tr:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.08); }

        .user-avatar { width: 40px; height: 40px; background: #e0e7ff; color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; }
        .badge-modern { padding: 8px 16px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
        .badge-menunggu { background: #fff1f2; color: #e11d48; border: 1px solid #ffe4e6; }
        .badge-proses { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
        .badge-selesai { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
        .tanggapan-area { background: #f8fafc; border-radius: 10px; padding: 12px; font-size: 0.85rem; color: var(--text-muted); border-left: 3px solid var(--primary); }
        footer { background: white; padding: 40px 0; border-top: 1px solid #e2e8f0; margin-top: auto; position: relative; z-index: 20;}
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="home.php">
            <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; font-weight: 800;">12</div>
            <div>
                <div class="fw-bold lh-1">SMKN</div>
                <div class="fw-bold text-primary" style="font-size: 0.7em; letter-spacing: 1px;">MALANG</div>
            </div>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <i data-lucide="menu"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-2 align-items-center">
                <li class="nav-item"><a class="nav-link active" href="home.php"><i data-lucide="layout-dashboard" size="16" class="me-1"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="input.php"><i data-lucide="plus-circle" size="16" class="me-1"></i> Lapor</a></li>
                <li class="nav-item ms-lg-3">
                    <a class="btn-logout" href="?action=logout" onclick="return confirm('Keluar dari akun?')">
                        <i data-lucide="log-out" size="16"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<header class="hero-section">
    <div class="container" data-aos="fade-up" data-aos-duration="1000">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="hero-badge">
                    <span class="badge bg-primary rounded-pill p-0" style="width: 8px; height: 8px; box-shadow: 0 0 10px var(--primary);"></span>
                    <span class="fw-bold">Sistem Pengaduan Terpadu</span>
                </div>
                <h1 class="hero-title display-3 mb-3">
                    Halo, <span class="text-white fw-normal"><?= htmlspecialchars($pelapor) ?>!</span> 👋
                </h1>
                <p class="lead">
                    Laporkan kerusakan fasilitas atau masalah akademik di SMKN 12 Malang dengan cepat dan transparan. Suara Anda sangat berarti bagi kami.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                    <a href="input.php" class="btn btn-primary btn-lg px-5 gap-2 d-flex align-items-center shadow-lg">
                        <i data-lucide="pencil-line" size="20"></i> Buat Laporan
                    </a>
                    <a href="#data-section" class="btn btn-outline-light btn-lg px-5 gap-2 d-flex align-items-center fw-bold">
                        <i data-lucide="list" size="20"></i> Lihat Data
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container stats-container">
    <div class="row g-3">
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Total Laporan</h6>
                        <h2 class="fw-bold mb-0 mt-2"><?= $stat_total ?></h2>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i data-lucide="file-stack"></i>
                    </div>
                </div>
                <div class="mt-3 d-flex align-items-center gap-1 text-success small fw-bold">
                    <i data-lucide="trending-up" size="14"></i> Data Terkini
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Dalam Proses</h6>
                        <h2 class="fw-bold mb-0 mt-2"><?= $stat_proses ?></h2>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i data-lucide="timer"></i>
                    </div>
                </div>
                <div class="mt-3 d-flex align-items-center gap-1 text-warning small fw-bold">
                    <i data-lucide="clock" size="14"></i> Sedang Ditangani
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Selesai</h6>
                        <h2 class="fw-bold mb-0 mt-2"><?= $stat_selesai ?></h2>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i data-lucide="check-circle-2"></i>
                    </div>
                </div>
                <div class="mt-3 d-flex align-items-center gap-1 text-success small fw-bold">
                    <i data-lucide="check" size="14"></i> Tuntas
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container main-container" id="data-section">
    <div class="main-content-card" data-aos="fade-up">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Daftar Pengaduan</h3>
                <p class="text-muted small m-0">Riwayat laporan dan status penanganan.</p>
            </div>
        </div>

        <div class="filter-box shadow-sm">
            <form class="row g-3" method="GET">
                <div class="col-md-5">
                    <div class="position-relative">
                        <div class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                            <i data-lucide="search" size="18"></i>
                        </div>
                        <input type="text" name="search" class="form-control ps-5" placeholder="Cari nama, isi laporan..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        <?php $kategori_list->data_seek(0); while($k = $kategori_list->fetch_assoc()): ?>
                            <option value="<?= $k['id_kategori'] ?>" <?= $filter_kategori == $k['id_kategori'] ? 'selected' : '' ?>><?= $k['nama_kategori'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i data-lucide="filter" size="16" class="me-1"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th style="width: 10%;">Bukti</th> <th style="width: 30%;">Detail Laporan</th>
                        <th style="width: 15%;">Lokasi</th>
                        <th style="width: 10%;">Waktu</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 20%;">Tanggapan Admin</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($p = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if(!empty($p['foto'])): ?>
                                <a href="assets/uploads/<?= $p['foto'] ?>" target="_blank">
                                    <img src="assets/uploads/<?= $p['foto'] ?>" class="img-thumbnail-custom" alt="Bukti">
                                </a>
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted border" style="width:80px; height:60px; font-size: 10px;">
                                    No Image
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-start gap-3">
                                <div class="user-avatar">
                                    <?= strtoupper(substr(htmlspecialchars($p['pelapor']), 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-1"><?= htmlspecialchars($p['pelapor']) ?></div>
                                    <div class="badge bg-light text-secondary fw-normal mb-2 border" style="font-size: 0.7rem;">
                                        <?= htmlspecialchars($p['nama_kategori'] ?? 'Umum') ?>
                                    </div>
                                    <p class="small text-muted mb-0 text-truncate" style="max-width: 200px; line-height: 1.4;">
                                        <?= htmlspecialchars($p['isi']) ?>
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2 text-dark fw-medium small">
                                <i data-lucide="map-pin" size="14" class="text-danger"></i>
                                <?= htmlspecialchars($p['lokasi'] ?? '-') ?>
                            </div>
                        </td>
                        <td>
                            <div class="small fw-bold text-dark"><?= date('d M', strtotime($p['tanggal'])) ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;"><?= date('H:i', strtotime($p['tanggal'])) ?></div>
                        </td>
                        <td>
                            <?php 
                                $st = strtolower($p['status']);
                                $cls = 'badge-menunggu'; $ic = 'clock';
                                if($st == 'proses'){ $cls = 'badge-proses'; $ic = 'loader-2'; }
                                if($st == 'selesai'){ $cls = 'badge-selesai'; $ic = 'check'; }
                            ?>
                            <span class="badge-modern <?= $cls ?>">
                                <i data-lucide="<?= $ic ?>" size="12"></i> <?= ucfirst($p['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if(!empty($p['tanggapan'])): ?>
                                <div class="tanggapan-area">
                                    <div class="fw-bold text-primary mb-1" style="font-size: 0.75rem;">ADMIN RESPONSE</div>
                                    <?= nl2br(htmlspecialchars($p['tanggapan'])) ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small d-flex align-items-center gap-1">
                                    <i data-lucide="hourglass" size="12"></i> Belum ada
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <div class="bg-light p-4 rounded-circle mb-3">
                                <i data-lucide="inbox" size="48" class="text-muted opacity-50"></i>
                            </div>
                            <div class="fw-bold text-muted">Data tidak ditemukan</div>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer>
    <div class="container text-center">
        <p class="text-muted small mb-0">
            &copy; <?= date('Y') ?> SMKN 12 Malang. Designed with <i data-lucide="heart" size="12" class="text-danger fill-current"></i> for Students.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true, offset: 50, easing: 'ease-out-cubic' });
    lucide.createIcons();

    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 20) {
            navbar.classList.add('shadow-sm');
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
        } else {
            navbar.classList.remove('shadow-sm');
            navbar.style.background = 'rgba(255, 255, 255, 0.8)';
        }
    });

    document.querySelectorAll('.stat-card h2').forEach(el => {
        const target = +el.innerText;
        if(target === 0) return;
        let count = 0;
        const speed = Math.ceil(target / 30);
        const updateCount = () => {
            if(count < target) {
                count += speed;
                el.innerText = count > target ? target : count;
                requestAnimationFrame(updateCount);
            } else {
                el.innerText = target;
            }
        };
        updateCount();
    });
</script>
</body>
</html>
<?php $conn->close(); ?>