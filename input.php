<?php
session_start();

/* ===== KONEKSI DATABASE ===== */
$conn = new mysqli("localhost", "root", "", "ukk");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

/* ===== AMBIL DATA LOGIN (NIS & NAMA) ===== */
$user_nis = $_SESSION['username'] ?? ''; 
$display_name = $_SESSION['username'] ?? '';

if (isset($_SESSION['username'])) {
    $user_active = $_SESSION['username'];

    $check_siswa = $conn->query("SELECT nama_siswa FROM siswa WHERE nis = '$user_active'");
    if ($check_siswa && $check_siswa->num_rows > 0) {
        $row_s = $check_siswa->fetch_assoc();
        $display_name = $row_s['nama_siswa'];
    } else {
        $check_masyarakat = $conn->query("SELECT nama FROM masyarakat WHERE username = '$user_active'");
        if ($check_masyarakat && $check_masyarakat->num_rows > 0) {
            $row_m = $check_masyarakat->fetch_assoc();
            $display_name = $row_m['nama'];
        }
    }
}

/* ===== PROSES KIRIM PENGADUAN ===== */
$success_msg = '';
if (isset($_POST['kirim'])) {
    $pelapor     = $_POST['pelapor']; 
    $nis         = $_POST['nis']; 
    $id_kategori = $_POST['id_kategori'];
    $isi         = $_POST['isi'];
    $lokasi      = $_POST['lokasi']; 
    $tanggal     = date('Y-m-d H:i:s');
    $foto        = ''; 

    if (isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != "") {
        $target_dir = "assets/uploads/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_name = $_FILES["foto"]["name"];
        $file_ext  = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = time() . '_' . uniqid() . '.' . $file_ext;
        $target_file   = $target_dir . $new_file_name;
        
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            $foto = $new_file_name;
        }
    } 
    elseif (isset($_POST['foto_base64']) && !empty($_POST['foto_base64'])) {
        $data = $_POST['foto_base64'];
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]);
            $data = base64_decode($data);
            $new_file_name = time() . '_' . uniqid() . '.' . $type;
            if (!is_dir("assets/uploads/")) { mkdir("assets/uploads/", 0777, true); }
            file_put_contents("assets/uploads/" . $new_file_name, $data);
            $foto = $new_file_name;
        }
    }

    $stmt = $conn->prepare("INSERT INTO pengaduan (pelapor, nis, id_kategori, isi, lokasi, tanggal, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissss", $pelapor, $nis, $id_kategori, $isi, $lokasi, $tanggal, $foto);

    if ($stmt->execute()) {
        $success_msg = "Pengaduan Anda telah berhasil terkirim!";
    }
}

$kategori = $conn->query("SELECT * FROM kategori");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Pengaduan | SMKN 12 Malang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root { --primary: #2563eb; --primary-hover: #1d4ed8; --bg-page: #f8fafc; }
        body { background: var(--bg-page); font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; overflow-x: hidden; }
        .navbar { background: rgba(255, 255, 255, 0.9) !important; backdrop-filter: blur(10px); padding: 1rem 0; z-index: 1050; }
        .header-bg { background-image: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.9)), url('assets/img/foto2.jpg'); background-size: cover; background-position: center; height: 350px; width: 100%; position: absolute; top: 0; left: 0; z-index: -1; border-radius: 0 0 50px 50px; }
        .card { max-width: 700px; margin: 120px auto 80px; background: #ffffff; border-radius: 24px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.05); padding: 40px; position: relative; z-index: 10; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: white; text-decoration: none; font-weight: 600; padding: 10px 20px; border-radius: 50px; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); transition: 0.3s; margin-bottom: 20px; border: 1px solid rgba(255,255,255,0.2); }
        .btn-submit { background: var(--primary); color: white; border: none; padding: 16px; border-radius: 15px; font-weight: 700; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-submit:hover { background: var(--primary-hover); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2); color: white; }
        .form-control, .form-select { border-radius: 12px; padding: 12px 15px; border: 1px solid #e2e8f0; background: #ffffff; }
        #offlineAlert { display: none; background: #fff7ed; border: 1px solid #ffedd5; color: #c2410c; border-radius: 15px; padding: 15px; margin-bottom: 25px; font-size: 0.9rem; font-weight: 600; }
        .spin { animation: rotation 2s infinite linear; }
        @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
        .fw-600 { font-weight: 600; } .fw-800 { font-weight: 800; }
    </style>
</head>
<body>

<div class="header-bg"></div>

<nav class="navbar navbar-expand-lg fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <img src="assets/img/smk.png" alt="Logo" style="height: 35px;">
            <div class="fw-bold">SMKN <span class="text-primary">12</span></div>
        </a>
    </div>
</nav>

<div class="container mt-5 pt-4">
    <div class="max-width-700 mx-auto" style="max-width: 700px;">
        <a href="index.php" class="btn-back" data-aos="fade-right"><i data-lucide="arrow-left" size="18"></i> Kembali</a>

        <div class="card" data-aos="fade-up">
            <div class="text-center mb-4">
                <h3 class="fw-800">Sampaikan Aspirasi</h3>
                <p class="text-muted">Lengkapi data laporan Anda di bawah ini.</p>
                <div id="offlineAlert" class="shadow-sm">
                    <i data-lucide="wifi-off"></i> Koneksi Terputus. Laporan disimpan lokal.
                </div>
                <div id="syncStatus"></div>
            </div>

            <?php if($success_msg): ?>
                <div class="alert alert-success rounded-4 mb-4"><i data-lucide="check-circle"></i> <?= $success_msg ?></div>
            <?php endif; ?>

            <form id="reportForm" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-600">NIS Pelapor</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i data-lucide="hash" size="16"></i></span>
                            <input type="text" name="nis" id="nis" class="form-control border-start-0" value="<?= htmlspecialchars($user_nis) ?>">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-600">Nama Lengkap</label>
                        <input type="text" name="pelapor" id="pelapor" class="form-control" required value="<?= htmlspecialchars($display_name) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">Kategori & Lokasi</label>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <select name="id_kategori" id="id_kategori" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <?php while($k = $kategori->fetch_assoc()): ?>
                                    <option value="<?= $k['id_kategori'] ?>"><?= $k['nama_kategori'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="lokasi" id="lokasi" class="form-control" placeholder="Lokasi Kejadian" required>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">Deskripsi Laporan</label>
                    <textarea name="isi" id="isi" class="form-control" rows="4" placeholder="Jelaskan secara detail..." required></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-600">Lampiran Foto (Opsional)</label>
                    <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
                    <div id="photoPreview" class="mt-3 text-center" style="display:none">
                        <img id="imgPrev" src="#" class="img-thumbnail shadow-sm rounded-4" style="max-height: 200px;">
                    </div>
                </div>

                <button type="submit" name="kirim" class="btn btn-submit w-100 py-3 shadow-lg">
                    <i data-lucide="send"></i> Kirim Laporan Sekarang
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    lucide.createIcons();
    AOS.init({ duration: 800, once: true });

    const form = document.getElementById('reportForm');
    const offlineAlert = document.getElementById('offlineAlert');
    const syncStatus = document.getElementById('syncStatus');
    let base64Image = "";

    document.getElementById('foto').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                base64Image = e.target.result;
                document.getElementById('imgPrev').src = base64Image;
                document.getElementById('photoPreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    function updateStatus() {
        if (navigator.onLine) {
            offlineAlert.style.display = 'none';
            syncData(); 
        } else {
            offlineAlert.style.display = 'block';
        }
    }
    window.addEventListener('online', updateStatus);
    window.addEventListener('offline', updateStatus);
    updateStatus();

    form.addEventListener('submit', function(e) {
        if (!navigator.onLine) {
            e.preventDefault();
            const report = {
                nis: document.getElementById('nis').value,
                pelapor: document.getElementById('pelapor').value,
                id_kategori: document.getElementById('id_kategori').value,
                lokasi: document.getElementById('lokasi').value,
                isi: document.getElementById('isi').value,
                foto: base64Image,
                timestamp: Date.now()
            };
            let queue = JSON.parse(localStorage.getItem('offline_reports') || '[]');
            queue.push(report);
            localStorage.setItem('offline_reports', JSON.stringify(queue));
            alert('Laporan disimpan secara lokal.');
            form.reset();
            document.getElementById('photoPreview').style.display = 'none';
        }
    });

    async function syncData() {
        let queue = JSON.parse(localStorage.getItem('offline_reports') || '[]');
        if (queue.length === 0) return;
        syncStatus.innerHTML = `<div class="alert alert-info py-2 small"><i data-lucide="refresh-cw" size="14" class="me-2 spin"></i>Sinkronisasi...</div>`;
        lucide.createIcons();
        for (let i = 0; i < queue.length; i++) {
            let data = queue[i];
            let formData = new FormData();
            formData.append('kirim', '1');
            formData.append('nis', data.nis);
            formData.append('pelapor', data.pelapor);
            formData.append('id_kategori', data.id_kategori);
            formData.append('lokasi', data.lokasi);
            formData.append('isi', data.isi);
            if(data.foto) formData.append('foto_base64', data.foto);
            
            try {
                const response = await fetch(window.location.href, { method: 'POST', body: formData });
                if(response.ok) {
                    queue.splice(i, 1);
                    i--;
                    localStorage.setItem('offline_reports', JSON.stringify(queue));
                }
            } catch (err) { break; }
        }
        if (queue.length === 0) {
            syncStatus.innerHTML = `<div class="alert alert-success py-2 small">Berhasil!</div>`;
            setTimeout(() => { syncStatus.innerHTML = ''; location.reload(); }, 1500);
        }
    }
</script>
</body>
</html>