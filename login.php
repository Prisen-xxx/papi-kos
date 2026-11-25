
<?php

session_start();

require_once "config/koneksi.php";

$pesan = "";
$status = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $pesan = "Email dan password wajib diisi.";
        $status = "error";

    } else {

        $query = $koneksi->prepare(
            "SELECT * FROM users WHERE email = :email LIMIT 1"
        );

        $query->execute([
            ":email" => $email
        ]);

        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["nama_lengkap"] = $user["nama_lengkap"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["jenis_akun"] = $user["jenis_akun"];

            // Pengalihan berdasarkan jenis akun

            if ($user["jenis_akun"] === "pencari") {

                header("Location: daftar-kos.php");
                exit;

            } elseif ($user["jenis_akun"] === "pemilik") {

                header("Location: dashboard-pemilik.php");
                exit;

            } else {

                session_unset();
                session_destroy();

                $pesan = "Jenis akun tidak dikenali.";
                $status = "error";

            }

        } else {

            $pesan = "Email atau password salah.";
            $status = "error";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - PAPI KOS</title>

    <link rel="stylesheet"
          href="css/style.css">

</head>

<body>

<header class="navbar">

    <div class="logo">
        <h2>PAPI KOS</h2>
    </div>

    <nav>

        <a href="index.php">Beranda</a>

        <a href="daftar-kos.php">Daftar Kos</a>

        <a href="login.php">Login</a>

        <a href="register.php">Daftar</a>

    </nav>

</header>

<section class="auth-section">

    <div class="auth-container">

        <div class="auth-header">

            <h1>Selamat Datang!</h1>

            <p>
                Silakan login untuk melanjutkan
                ke akun PAPI KOS kamu.
            </p>

        </div>

        <?php if ($pesan !== ""): ?>

            <p style="padding: 12px; margin-bottom: 15px;
                border-radius: 6px;
                background: #fee2e2;
                color: #991b1b;">

                <?php echo htmlspecialchars($pesan); ?>

            </p>

        <?php endif; ?>

        <form class="auth-form"
              method="POST"
              action="login.php">

            <div class="form-group">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    placeholder="Masukkan email"
                    required>

            </div>

            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    required>

            </div>

            <div class="login-options">

                <label>
                    <input type="checkbox" name="ingat_saya">
                    Ingat saya
                </label>

                <a href="#">
                    Lupa password?
                </a>

            </div>

            <button type="submit"
                    class="auth-button">

                Login Sekarang

            </button>

        </form>

        <div class="auth-footer">

            <p>
                Belum punya akun?

                <a href="register.php">
                    Daftar sekarang
                </a>

            </p>

        </div>

    </div>

</section>

<footer>

    <p>
        © 2026 Papi Kos - Sistem Informasi Kos Kefamenanu
    </p>

</footer>

</body>

</html>
