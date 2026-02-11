<?php
session_start();

$conn = new mysqli("localhost","root","","ukk");
if($conn->connect_error) die("Koneksi gagal: ".$conn->connect_error);

$error = "";

if(isset($_POST['login_siswa'])){

    $username = $_POST['username']; 
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM siswa WHERE nis=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $data = $result->fetch_assoc();
        
        if(password_verify($password, $data['password'])){
            $_SESSION['status'] = "login";
            $_SESSION['siswa_logged_in'] = true;
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['nis'] = $data['nis'];

            header("Location: home.php");
            exit;
        } else {
            $error = "Password siswa salah!";
        }
    } else {
        $error = "NIS siswa tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Siswa - SMKN 12 MALANG</title>
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
         
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
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
            max-width: 380px;
            border-radius: 30px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px) saturate(160%);
            -webkit-backdrop-filter: blur(20px) saturate(160%);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
            color: white;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-header {
            text-align: center;
            padding: 40px 20px 10px;
            background: transparent;
            border: none;
        }

        .card-header img {
            width: 85px;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 10px rgba(255,255,255,0.2));
        }

        .card-header h4 {
            font-weight: 800;
            letter-spacing: -0.5px;
            font-size: 1.4rem;
            margin-bottom: 5px;
        }

        .card-header p {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .card-body {
            padding: 20px 35px 40px;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 20px;
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
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(0, 255, 136, 0.2);
            color: white;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .btn-success {
            border-radius: 15px;
            padding: 12px;
            font-weight: 700;
            background: var(--accent);
            border: none;
            color: #000;
            transition: all 0.4s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .btn-success:hover {
            background: #00d977;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 255, 136, 0.3);
            color: #000;
        }

        .alert {
            background: rgba(255, 82, 82, 0.2);
            border: 1px solid rgba(255, 82, 82, 0.3);
            color: #ff8a8a;
            border-radius: 12px;
            font-size: 0.8rem;
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .register-box {
            text-align: center;
            margin-top: 25px;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .register-box a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
        }

        .register-box a:hover {
            text-shadow: 0 0 10px var(--accent);
        }
    </style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <img src="assets/img/smk.png" alt="Logo">
        <h4>SMKN 12 MALANG</h4>
        <p>Portal Pengaduan Siswa</p>
    </div>

    <div class="card-body">
        <?php if($error): ?>
            <div class="alert mb-4">
                <i data-lucide="alert-circle" width="18"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group-custom">
                <i data-lucide="hash"></i>
                <input type="text" name="username" class="form-control" placeholder="Masukkan NIS Anda" required autocomplete="off">
            </div>
            
            <div class="input-group-custom">
                <i data-lucide="lock"></i>
                <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
            </div>

            <button type="submit" name="login_siswa" class="btn btn-success w-100">
                Masuk Sekarang
                <i data-lucide="chevron-right" width="18"></i>
            </button>
        </form>

        <div class="register-box">
            <span>Belum punya akun?</span><br>
            <a href="registers.php">Daftar Akun Baru</a>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>