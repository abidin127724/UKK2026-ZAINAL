<?php
session_start();

/* ===== CEK LOGIN ===== */
// Sesuaikan dengan session yang Anda gunakan di daftar_pengaduan.php
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("Location: login.php");
    exit;
}

/* ===== KONEKSI DATABASE ===== */
$conn = new mysqli("localhost","root","","ukk");
if($conn->connect_error){
    die("Koneksi gagal: ".$conn->connect_error);
}

/* ===== AMBIL FILTER ===== */
$kategori = $_GET['kategori'] ?? '';
$search   = $_GET['search'] ?? '';
$filter_tanggal = $_GET['filter_tanggal'] ?? ''; 
$filter_bulan = $_GET['filter_bulan'] ?? '';

$sql = "
    SELECT p.*, k.nama_kategori
    FROM pengaduan p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    WHERE 1
";

if($kategori){
    $sql .= " AND p.id_kategori=".(int)$kategori;
}

if($search){
    $s = $conn->real_escape_string($search);
    $sql .= " AND (p.pelapor LIKE '%$s%' OR p.isi LIKE '%$s%' OR p.nis LIKE '%$s%')";
}

if($filter_tanggal) {
    $sql .= " AND DATE(p.tanggal) = '$filter_tanggal'";
}

if($filter_bulan) {
    $sql .= " AND p.tanggal LIKE '$filter_bulan%'";
}

$sql .= " ORDER BY p.tanggal DESC";
$result = $conn->query($sql);

/* ===== EXPORT EXCEL ===== */
if(isset($_GET['excel'])){
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=laporan_pengaduan.xls");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Pengaduan</title>

<style>
body{
    font-family:Arial, sans-serif;
    font-size:12px;
    padding: 20px;
}
h2{
    text-align:center;
    margin-bottom:5px;
}
.subtitle{
    text-align:center;
    font-size:11px;
    margin-bottom:20px;
}
table{
    width:100%;
    border-collapse:collapse;
}
th, td{
    border:1px solid #000;
    padding:8px;
    vertical-align: middle;
}
th{
    background:#f1f1f1;
}
.center{
    text-align:center;
}
/* Ukuran gambar agar rapi saat dicetak */
.img-report {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
}

/* ===== TOMBOL ===== */
.no-print{
    margin-bottom:20px;
}
.no-print button{
    padding:8px 16px;
    margin-right:5px;
    cursor:pointer;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 4px;
}
.btn-excel {
    background: #10b981 !important;
}

/* ===== HILANG SAAT CETAK ===== */
@media print{
    .no-print{
        display:none;
    }
    body {
        padding: 0;
    }
}
</style>
</head>

<body onload="<?php if(!isset($_GET['excel'])) echo 'window.print()'; ?>">

<div class="no-print">
    <button onclick="window.print()">🖨️ Cetak PDF / Print</button>

    <a href="?excel=1&kategori=<?= urlencode($kategori) ?>&search=<?= urlencode($search) ?>&filter_tanggal=<?= $filter_tanggal ?>&filter_bulan=<?= $filter_bulan ?>">
        <button class="btn-excel">📊 Export Excel</button>
    </a>
    <button onclick="window.close()" style="background:#64748b">Tutup</button>
</div>

<h2>LAPORAN DATA PENGADUAN</h2>
<div class="subtitle">SMKN 12 MALANG</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>NIS</th>
            <th>Pelapor</th>
            <th>Kategori</th>
            <th>Isi Laporan</th>
            <th>Gambar</th>
            <th>Tanggal</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php $no=1; while($p=$result->fetch_assoc()): ?>
        <tr>
            <td class="center"><?= $no++ ?></td>
            <td class="center"><?= htmlspecialchars($p['nis'] ?? '-') ?></td>
            <td><?= htmlspecialchars($p['pelapor']) ?></td>
            <td class="center"><?= htmlspecialchars($p['nama_kategori'] ?? '-') ?></td>
            <td><?= htmlspecialchars($p['isi']) ?></td>
            <td class="center">
                <?php if(!empty($p['foto'])): ?>
                    <img src="assets/uploads/<?= $p['foto'] ?>" class="img-report">
                <?php else: ?>
                    <small style="color: #ccc;">No Image</small>
                <?php endif; ?>
            </td>
            <td class="center"><?= date('d-m-Y', strtotime($p['tanggal'])) ?></td>
            <td class="center">
                <strong><?= ($p['status'] == '0') ? 'Pending' : ucfirst($p['status']) ?></strong>
            </td>
        </tr>
    <?php endwhile; ?>
    <?php if($result->num_rows == 0): ?>
        <tr><td colspan="8" class="center">Tidak ada data untuk dicetak.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<br>
<div style="text-align:right;font-size:11px;">
    Dicetak pada: <?= date('d M Y H:i') ?>
</div>

</body>
</html>
<?php $conn->close(); ?>