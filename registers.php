<?php
session_start();
$conn = new mysqli("localhost","root","","ukk");
if($conn->connect_error) die("Koneksi gagal: ".$conn->connect_error);

$error = ""; $sukses = "";

if(isset($_POST['daftar'])){
    $nis  = $_POST['nis'];
    $nama = $_POST['nama'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Cek NIS
    $cek = $conn->prepare("SELECT nis FROM siswa WHERE nis=?");
    if(!$cek) { die("Tabel 'siswa' tidak ditemukan! Buat tabel 'siswa' dulu di database."); }
    
    $cek->bind_param("s", $nis);
    $cek->execute();
    $cek->store_result();

    if($cek->num_rows > 0){
        $error = "NIS sudah terdaftar!";
    } else {
        // Simpan ke tabel siswa
        $stmt = $conn->prepare("INSERT INTO siswa (nis, nama_siswa, password) VALUES (?, ?, ?)");
        if(!$stmt) { die("Kolom 'nama_siswa' salah! Cek kolom di database."); }
        
        $stmt->bind_param("sss", $nis, $nama, $pass);
        if($stmt->execute()){
            $sukses = "Pendaftaran berhasil! Silakan login.";
        } else {
            $error = "Gagal mendaftar!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Siswa - SMKN 12 MALANG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --accent: #00ff88; 
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            background: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.75)), 
                        url('assets/img/2.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 20px;
        }

        .card {
            width: 100%;
            max-width: 400px;
            border-radius: 30px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px) saturate(160%);
            -webkit-backdrop-filter: blur(20px) saturate(160%);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            animation: fadeIn 0.8s ease-out;
            color: white;
            padding: 2.5rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .card-header {
            text-align: center;
            background: transparent;
            border: none;
            padding: 0 0 1.5rem 0;
        }

        .card-header img {
            width: 70px;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 10px rgba(255,255,255,0.2));
        }

        .card-header h4 {
            font-weight: 800;
            font-size: 1.3rem;
            margin: 0;
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
            color: rgba(255, 255, 255, 0.5);
            width: 18px;
        }

        .form-control {
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 15px 12px 45px;
            color: white;
            transition: 0.3s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(0, 255, 136, 0.15);
            color: white;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .btn-success {
            border-radius: 15px;
            padding: 14px;
            font-weight: 700;
            background: var(--accent);
            border: none;
            color: #000;
            transition: all 0.3s;
            margin-top: 10px;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        .btn-success:hover {
            background: #00d977;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 255, 136, 0.2);
            color: #000;
        }

        .alert {
            border-radius: 12px;
            font-size: 0.85rem;
            padding: 10px 15px;
            border: none;
        }

        .btn-link {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            transition: 0.3s;
        }

        .btn-link:hover {
            color: var(--accent);
        }
    </style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <img src="assets/img/smk.png" alt="Logo">
        <h4>SMKN 12 MALANG</h4>
        <p class="text-white-50 small">Registrasi Akun Baru siswa</p>
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger text-center"><?= $error ?></div>
    <?php endif; ?>

    <?php if($sukses): ?>
        <div class="alert alert-success text-center"><?= $sukses ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group-custom">
            <i data-lucide="hash"></i>
            <input type="text" name="nis" class="form-control" placeholder="Masukkan NIS" required>
        </div>

        <div class="input-group-custom">
            <i data-lucide="user"></i>
            <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
        </div>

        <div class="input-group-custom">
            <i data-lucide="lock"></i>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>

        <button type="submit" name="daftar" class="btn btn-success w-100">Daftar Akun</button>
    </form>

    <a href="login_siswa.php" class="btn btn-link w-100 mt-3 text-decoration-none">
        <i data-lucide="arrow-left" style="width: 14px; vertical-align: middle;"></i> Kembali ke Login
    </a>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html>