<?php
session_start();

$conn = new mysqli("localhost", "root", "", "ukk");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$error = "";

if (isset($_POST['login_admin'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admin WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $data = $result->fetch_assoc();

        if (password_verify($password, $data['password'])) {
            $_SESSION['status'] = "login";
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['username'] = $data['username'];

            header("Location: dasbhoard_admin.php");
            exit;
        } else {
            $error = "Password admin salah!";
        }
    } else {
        $error = "Username admin tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SMKN 12 MALANG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        :root {
            --primary: #4361ee;
            --primary-hover: #3f37c9;
            --glass-bg: rgba(255, 255, 255, 0.12);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-main: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.7);
        }

        body {
            background: linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.75)), 
                        url('assets/img/foto2.jpg');
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
            color: var(--text-main);
        }

        .card {
            width: 100%;
            /* UKURAN DIKECILKAN DI SINI */
            max-width: 360px; 
            border-radius: 30px;
            background: var(--glass-bg);
            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            animation: fadeInScale 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.92) translateY(30px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .card-header {
            text-align: center;
            /* PADDING DISESUAIKAN */
            padding: 40px 25px 15px;
            background: transparent;
            border: none;
        }

        .card-header img {
            width: 80px; /* Ukuran logo sedikit dikecilkan agar seimbang */
            height: 80px;
            object-fit: contain;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 15px rgba(255,255,255,0.2));
        }

        .card-header h4 {
            color: var(--text-main);
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
            font-size: 1.3rem; /* Font size dikecilkan sedikit */
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .card-header p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 5px;
            font-weight: 500;
        }

        .card-body {
            /* PADDING DISESUAIKAN */
            padding: 0 30px 40px;
        }

        .login-title {
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 25px;
            text-align: center;
            display: block;
            font-size: 0.9rem;
            letter-spacing: 1px;
            opacity: 0.9;
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
            width: 16px;
        }

        .form-control {
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 12px 15px 12px 42px;
            font-size: 0.9rem;
            color: #ffffff;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.25);
            color: #ffffff;
        }

        .btn-admin {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 12px;
            font-weight: 700;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }

        .btn-admin:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            color: white;
        }

        .alert {
            border-radius: 15px;
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 12px;
            font-size: 0.8rem;
            margin-bottom: 20px;
        }

        .footer-text {
            text-align: center;
            margin-top: 25px;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.4);
        }
    </style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <img src="assets/img/smk.png" alt="Logo SMKN 12">
        <h4>SMKN 12 MALANG</h4>
        <p>Sistem Pengaduan Siswa</p>
    </div>

    <div class="card-body">
        <span class="login-title">LOGIN ADMIN</span>

        <?php if($error): ?>
            <div class="alert">
                <i data-lucide="alert-circle" width="16"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group-custom">
                <i data-lucide="user"></i>
                <input type="text" name="username" class="form-control" placeholder="Username" required autocomplete="off">
            </div>
            
            <div class="input-group-custom">
                <i data-lucide="lock"></i>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <button type="submit" name="login_admin" class="btn btn-admin w-100">
                Masuk
                <i data-lucide="log-in" width="18"></i>
            </button>
        </form>

        <p class="footer-text">
            &copy; <?= date('Y') ?> SMKN 12 Malang.
        </p>
    </div>
</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>