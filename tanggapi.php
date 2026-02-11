<?php
session_start();


if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "ukk");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    header("Location: daftar_pengaduan.php");
    exit;
}

$id = (int)$_GET['id'];


if (isset($_POST['update'])) {
    $id_kategori = $_POST['id_kategori'];
    $lokasi      = $_POST['lokasi'];
    $isi         = $_POST['isi'];
    $status      = $_POST['status'];
    $tanggapan   = $_POST['tanggapan'];

    $stmt = $conn->prepare(
        "UPDATE pengaduan SET id_kategori=?, lokasi=?, isi=?, status=?, tanggapan=? WHERE id_pengaduan=?"
    );
    
    $stmt->bind_param("issssi", $id_kategori, $lokasi, $isi, $status, $tanggapan, $id);

    if ($stmt->execute()) {
        header("Location: daftar_pengaduan.php");
        exit;
    }
    $stmt->close();
}


$data = $conn->query("SELECT * FROM pengaduan WHERE id_pengaduan = $id")->fetch_assoc();
$kategori_list = $conn->query("SELECT * FROM kategori");

if (!$data) {
    die("Pengaduan tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tanggapi Pengaduan | Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>

<style>
:root {
    --primary: #2563eb;
    --bg-page: #f8fafc;
    --text-main: #1e293b;
    --text-muted: #64748b;
}

body {
    background: var(--bg-page);
    background-image: radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.05) 0, transparent 50%), 
                      radial-gradient(at 100% 100%, rgba(37, 99, 235, 0.05) 0, transparent 50%);
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    color: var(--text-main);
}

.card {
    max-width: 800px;
    width: 100%;
    border-radius: 30px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
    overflow: hidden;
}

.card-header {
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    color: var(--text-main);
    padding: 30px;
    text-align: center;
}

.card-header h4 {
    font-weight: 800;
    margin: 0;
    letter-spacing: -1px;
    color: var(--text-main);
}

.card-body { padding: 40px; }

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.info-item {
    background: #f8fafc;
    padding: 15px;
    border-radius: 16px;
    border: 1px solid #f1f5f9;
}

.info-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.7rem;
    text-transform: uppercase;
    font-weight: 800;
    color: var(--text-muted);
    margin-bottom: 5px;
    letter-spacing: 0.5px;
}

.report-content {
    background: #eff6ff;
    border-left: 5px solid var(--primary);
    padding: 25px;
    border-radius: 4px 20px 20px 4px;
    margin-bottom: 35px;
}

.form-label {
    font-weight: 700;
    font-size: 0.75rem;
    color: var(--text-main);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-control, .form-select {
    background: #ffffff;
    border: 2px solid #f1f5f9;
    border-radius: 16px;
    color: var(--text-main);
    padding: 14px 18px;
    transition: all 0.2s;
}

.form-control:focus, .form-select:focus {
    background: #ffffff;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
}

.btn {
    border-radius: 16px;
    padding: 14px 28px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
}

.btn-sharp {
    border-radius: 0px !important;
}

.btn-primary {
    background: var(--primary);
    border: none;
    box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
}

.btn-secondary {
    background: #f1f5f9;
    border: none;
    color: #475569;
}
</style>
</head>

<body>

<div class="card">
    <div class="card-header">
        <h4><i data-lucide="message-square-quote" class="me-2 text-primary"></i>Tanggapi & Edit Laporan</h4>
    </div>
    <div class="card-body">
        <form method="POST">
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label"><i data-lucide="fingerprint" size="14"></i> NIS</div>
                    <div style="font-weight: 600; font-size: 0.95rem; color: var(--text-main);"><?= htmlspecialchars($data['nis'] ?? '-') ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i data-lucide="user" size="14"></i> Nama Pelapor</div>
                    <div style="font-weight: 600; font-size: 0.95rem; color: var(--text-main);"><?= htmlspecialchars($data['nama'] ?? $data['pelapor'] ?? '-') ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label"><i data-lucide="layers" size="14"></i> Kategori</div>
                    <select name="id_kategori" class="form-select border-0 p-0 shadow-none bg-transparent" style="font-weight: 600; font-size: 0.95rem; min-width: 100px;">
                        <?php while($k = $kategori_list->fetch_assoc()): ?>
                            <option value="<?= $k['id_kategori'] ?>" <?= $k['id_kategori'] == $data['id_kategori'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kategori']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="info-item">
                    <div class="info-label"><i data-lucide="map-pin" size="14"></i> Lokasi</div>
                    <select name="lokasi" class="form-select border-0 p-0 shadow-none bg-transparent fw-bold text-primary" style="font-size: 0.95rem;">
                        <option value="labf.1" <?= ($data['lokasi'] == 'labf.1') ? 'selected' : '' ?>>labf.1</option>
                        <option value="lab f.2" <?= ($data['lokasi'] == 'lab f.2') ? 'selected' : '' ?>>lab f.2</option>
                        <option value="lab g1.1" <?= ($data['lokasi'] == 'lab g1.1') ? 'selected' : '' ?>>lab g1.1</option>
                        <option value="lab g1.2" <?= ($data['lokasi'] == 'lab g1.2') ? 'selected' : '' ?>>lab g1.2</option>
                        <option value="Kelas" <?= ($data['lokasi'] == 'Kelas') ? 'selected' : '' ?>>Kelas</option>
                    </select>
                </div>
            </div>

            <div class="report-content">
                <div class="info-label mb-2"><i data-lucide="text-quote" size="14"></i> Detail Laporan</div>
                <textarea name="isi" class="form-control border-0 p-0 shadow-none bg-transparent" 
                          rows="4" style="line-height: 1.6; resize: none;"><?= htmlspecialchars($data['isi']) ?></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label"><i data-lucide="pen-tool" size="14"></i> Tanggapan Admin</label>
                <textarea name="tanggapan" class="form-control" rows="4" 
                    placeholder="Tuliskan respon resmi anda di sini..."><?= htmlspecialchars($data['tanggapan'] ?? '') ?></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label"><i data-lucide="refresh-cw" size="14"></i> Status Pengaduan</label>
                <select name="status" class="form-select" required>
                    <option value="pending" <?= $data['status']=='pending'?'selected':'' ?>>Pending</option>
                    <option value="proses" <?= $data['status']=='proses'?'selected':'' ?>>Proses</option>
                    <option value="selesai" <?= $data['status']=='selesai'?'selected':'' ?>>Selesai</option>
                </select>
            </div>

            <div class="d-flex gap-3 mt-5">
                <button type="submit" name="update" class="btn btn-primary text-white btn-sharp">
                    <i data-lucide="check-circle-2" size="20"></i> Simpan Perubahan
                </button>
                <a href="daftar_pengaduan.php" class="btn btn-secondary">
                    <i data-lucide="chevron-left" size="20"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>
</body>
</html>
<?php $conn->close(); ?>