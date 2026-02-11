<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ukk");

$stats = ['total' => 0, 'selesai' => 0, 'proses' => 0];
$pengaduan_list = [];

if (!$conn->connect_error) {
    $stats['total'] = $conn->query("SELECT COUNT(*) as total FROM pengaduan")->fetch_assoc()['total'] ?? 0;
    $stats['proses'] = $conn->query("SELECT COUNT(*) as total FROM pengaduan WHERE status='proses'")->fetch_assoc()['total'] ?? 0;
    $stats['selesai'] = $conn->query("SELECT COUNT(*) as total FROM pengaduan WHERE status='selesai'")->fetch_assoc()['total'] ?? 0;

    $nis_filter = isset($_GET['nis']) ? $conn->real_escape_string($_GET['nis']) : '';
    
    if (!empty($nis_filter)) {
        $sql = "SELECT p.*, k.nama_kategori 
                FROM pengaduan p
                LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
                WHERE p.nis = '$nis_filter' 
                ORDER BY p.tanggal DESC LIMIT 50";
                
        $result_list = $conn->query($sql);
        if($result_list) {
            while($row = $result_list->fetch_assoc()) {
                $pengaduan_list[] = $row;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Aspirasi Siswa | SMKN 12 Malang</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --accent: #10b981;
            --dark: #0f172a;
            --bg-soft: #f8fafc;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #ffffff; 
            color: var(--dark); 
            overflow-x: hidden; 
        }

        /* Modern Navbar */
        .navbar { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); padding: 1.5rem 0; }
        .navbar.scrolled { 
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(12px); 
            padding: 0.8rem 0; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
        }
        .navbar-brand .brand-text { color: white; transition: 0.3s; }
        .navbar.scrolled .brand-text { color: var(--dark); }

        /* Hero with Glassmorphism */
        .hero-section {
            position: relative; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.7)), 
                        url('assets/img/foto2.jpg') center/cover no-repeat fixed;
            color: white;
            border-bottom-left-radius: 4rem;
            border-bottom-right-radius: 4rem;
        }
        .text-gradient { 
            background: linear-gradient(to right, #818cf8, #34d399); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        }

        /* Floating Stat Cards */
        .stat-card { 
            background: white; 
            border-radius: 2rem; 
            padding: 2.5rem 1.5rem; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.05); 
            border: 1px solid #f1f5f9; 
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover { transform: translateY(-10px); box-shadow: 0 30px 60px rgba(79, 70, 229, 0.1); }
        .stat-card::after {
            content: ""; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
            background: var(--primary); opacity: 0; transition: 0.3s;
        }
        .stat-card:hover::after { opacity: 1; }

        /* Search Bar Neo */
        .search-wrapper { 
            background: white; 
            padding: 12px; 
            border-radius: 100px; 
            box-shadow: 0 15px 45px rgba(0,0,0,0.08); 
            display: flex; 
            align-items: center;
            border: 1px solid #e2e8f0;
            max-width: 650px; 
            margin: 0 auto;
        }
        .search-wrapper input { 
            border: none; 
            padding: 0 20px; 
            font-size: 1rem; 
            font-weight: 500;
            width: 100%;
        }
        .search-wrapper input:focus { outline: none; }

        /* Custom Table Status */
        .table-container { 
            background: white; 
            border-radius: 2rem; 
            padding: 1.5rem; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            border: 1px solid #f1f5f9;
        }
        .table thead th { 
            background: transparent; 
            border: none; 
            padding: 1.5rem; 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            color: #94a3b8;
        }
        .table tbody tr { transition: 0.3s; }
        .table tbody td { padding: 1.5rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; }

        /* Badge Pulses */
        .status-badge {
            padding: 8px 16px;
            border-radius: 100px;
            font-weight: 700;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .st-pending { background: #fff1f2; color: #e11d48; }
        .st-proses { background: #eff6ff; color: #2563eb; position: relative; }
        .st-proses::after {
            content: ""; width: 8px; height: 8px; background: #2563eb; 
            border-radius: 50%; display: inline-block; animation: pulse 1.5s infinite;
        }
        .st-selesai { background: #f0fdf4; color: #16a34a; }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }

        .img-preview-sm { 
            width: 60px; height: 60px; border-radius: 1rem; 
            object-fit: cover; box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
        }

        footer { background: var(--dark); border-top-left-radius: 5rem; border-top-right-radius: 5rem; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-3" href="#">
                <img src="assets/img/smk.png" alt="Logo" style="height: 45px;">
                <div class="brand-text">
                    <span class="fw-800 fs-4 d-block lh-1">Lapor12</span>
                    <span class="small opacity-75 fw-500" style="letter-spacing: 2px;">DIGITAL PORTAL</span>
                </div>
            </a>
            <div class="ms-auto">
                <a href="login.php" class="btn btn-primary rounded-pill px-4 fw-bold py-2 shadow-sm">
                    <i data-lucide="user" class="me-2" style="width: 18px;"></i>Admin Portal
                </a>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 text-center text-lg-start" data-aos="fade-right">
                    <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 px-3 py-2 rounded-pill mb-4 border border-white border-opacity-20">
                        <span class="badge bg-accent rounded-pill">New</span>
                        <span class="small fw-600">Sistem Pengaduan Versi 2026</span>
                    </div>
                    <h1 class="display-3 fw-800 mb-4">Suara Kamu,<br><span class="text-gradient">Perubahan Kita</span></h1>
                    <p class="lead mb-5 opacity-75 pe-lg-5">
                        Laporkan kendala fasilitas sekolah atau berikan aspirasi terbaikmu secara digital. Transparan, cepat, dan terpercaya.
                    </p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3">
                        <a href="input.php" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-lg py-3">
                            Mulai Melapor <i data-lucide="plus-circle" class="ms-2"></i>
                        </a>
                        <a href="#daftar" class="btn btn-outline-light btn-lg rounded-pill px-5 fw-bold py-3">
                            Cek Laporan
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block" data-aos="zoom-in">
                    <img src="assets/img/hero-vector.png" alt="" class="img-fluid animate-float">
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" style="margin-top: -80px; position: relative; z-index: 10;">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-card">
                        <div class="mb-3 text-primary"><i data-lucide="database" size="32"></i></div>
                        <h2 class="fw-800 count-up mb-1" data-target="<?= $stats['total'] ?>">0</h2>
                        <p class="text-muted small fw-700">TOTAL ADUAN MASUK</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-card">
                        <div class="mb-3 text-warning"><i data-lucide="refresh-cw" size="32"></i></div>
                        <h2 class="fw-800 count-up mb-1 text-warning" data-target="<?= $stats['proses'] ?>">0</h2>
                        <p class="text-muted small fw-700">SEDANG DITANGANI</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-card">
                        <div class="mb-3 text-accent"><i data-lucide="check-square" size="32"></i></div>
                        <h2 class="fw-800 count-up mb-1 text-accent" data-target="<?= $stats['selesai'] ?>">0</h2>
                        <p class="text-muted small fw-700">TELAH DISELESAIKAN</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="daftar" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="fw-800 mb-2">Lacak Status Laporan</h2>
                <p class="text-muted">Masukkan Nomor Induk Siswa (NIS) Anda untuk melihat progres</p>
            </div>

            <div class="search-wrapper mb-5" data-aos="zoom-in">
                <i data-lucide="hash" class="ms-4 text-primary"></i>
                <form action="index.php#daftar" method="GET" class="w-100 d-flex">
                    <input type="text" name="nis" placeholder="Contoh: 12345" value="<?= htmlspecialchars($nis_filter) ?>" required>
                    <button class="btn btn-primary rounded-pill px-5 fw-bold py-2 me-1">Cari</button>
                </form>
            </div>

            <?php if (!empty($nis_filter)): ?>
                <div class="table-container" data-aos="fade-up">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Lampiran</th>
                                    <th>Detail Aduan</th>
                                    <th>Status</th>
                                    <th>Tanggapan Admin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($pengaduan_list)): ?>
                                    <tr><td colspan="4" class="text-center py-5">Data tidak ditemukan.</td></tr>
                                <?php else: ?>
                                    <?php foreach($pengaduan_list as $p): ?>
                                        <tr>
                                            <td>
                                                <?php if(!empty($p['foto'])): ?>
                                                    <img src="assets/uploads/<?= $p['foto'] ?>" class="img-preview-sm" alt="Bukti">
                                                <?php else: ?>
                                                    <div class="img-preview-sm bg-light d-flex align-items-center justify-content-center text-muted">
                                                        <i data-lucide="camera-off" size="18"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-800 text-dark mb-1"><?= htmlspecialchars($p['nama_kategori'] ?? 'Lainnya') ?></div>
                                                <p class="small text-muted mb-2"><?= date('d M Y', strtotime($p['tanggal'])) ?></p>
                                                <p class="small text-dark mb-0"><?= nl2br(htmlspecialchars(substr($p['isi'], 0, 100))) ?>...</p>
                                            </td>
                                            <td>
                                                <?php 
                                                    $st = strtolower($p['status']);
                                                    $cls = ($st == 'proses') ? 'st-proses' : (($st == 'selesai') ? 'st-selesai' : 'st-pending');
                                                    $label = ($p['status'] == '0') ? 'Pending' : ucfirst($p['status']);
                                                ?>
                                                <span class="status-badge <?= $cls ?>"><?= $label ?></span>
                                            </td>
                                            <td>
                                                <?php if(!empty($p['tanggapan'])): ?>
                                                    <div class="tanggapan-box shadow-sm">
                                                        <div class="fw-bold mb-1 small text-primary">ADMIN RESPONSE:</div>
                                                        <div class="small italic">"<?= htmlspecialchars($p['tanggapan']) ?>"</div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small"><i data-lucide="clock" size="14" class="me-1"></i>Belum ada respon</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted" data-aos="fade-in">
                    <i data-lucide="search-check" size="64" class="mb-3 opacity-20"></i>
                    <p class="fw-500">Hasil pencarian akan muncul di sini.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="text-white py-5">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-4 mb-md-0">
                    <h3 class="fw-800 mb-2">SMKN 12 MALANG</h3>
                    <p class="text-white-50 small mb-0">Membangun Masa Depan Melalui Teknologi & Aspirasi Siswa.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="d-flex justify-content-center justify-content-md-end gap-3 mb-3">
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle p-2"><i data-lucide="instagram" size="18"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle p-2"><i data-lucide="globe" size="18"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle p-2"><i data-lucide="facebook" size="18"></i></a>
                    </div>
                    <p class="small text-white-50">&copy; <?= date('Y') ?> UKK RPL | Digital Innovation Team</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize Animations
        AOS.init({ duration: 800, once: true });
        lucide.createIcons();

        // Navbar Scroll Effect
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('.navbar');
            if (window.scrollY > 50) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        });

        // Count Up Logic
        document.querySelectorAll('.count-up').forEach(c => {
            const target = +c.getAttribute('data-target');
            const update = () => {
                const cur = +c.innerText;
                const inc = Math.max(1, target / 50);
                if(cur < target) {
                    c.innerText = Math.ceil(cur + inc);
                    setTimeout(update, 20);
                } else c.innerText = target;
            };
            update();
        });
    </script>
</body>
</html>