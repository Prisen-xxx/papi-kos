
<?php

require_once "config/koneksi.php";

$pesan = "";
$status = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama_lengkap"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $no_hp = trim($_POST["no_hp"] ?? "");
    $jenis_akun = $_POST["jenis_akun"] ?? "";
    $password = $_POST["password"] ?? "";
    $konfirmasi = $_POST["konfirmasi_password"] ?? "";

    if (
        $nama === "" ||
        $email === "" ||
        $no_hp === "" ||
        $jenis_akun === "" ||
        $password === "" ||
        $konfirmasi === ""
    ) {

        $pesan = "Semua kolom wajib diisi.";
        $status = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $pesan = "Format email tidak valid.";
        $status = "error";

    } elseif (!in_array($jenis_akun, ["pencari", "pemilik"], true)) {

        $pesan = "Jenis akun tidak valid.";
        $status = "error";

    } elseif (strlen($password) < 8) {

        $pesan = "Password minimal 8 karakter.";
        $status = "error";

    } elseif ($password !== $konfirmasi) {

        $pesan = "Konfirmasi password tidak sama.";
        $status = "error";

    } else {

        $cekEmail = $koneksi->prepare(
            "SELECT id FROM users WHERE email = :email"
        );

        $cekEmail->execute([
            ":email" => $email
        ]);

        if ($cekEmail->fetch()) {

            $pesan = "Email sudah terdaftar. Gunakan email lain.";
            $status = "error";

        } else {

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $query = $koneksi->prepare(
                "INSERT INTO users
                (nama_lengkap, email, no_hp, jenis_akun, password)
                VALUES
                (:nama, :email, :no_hp, :jenis_akun, :password)"
            );

            $berhasil = $query->execute([
                ":nama" => $nama,
                ":email" => $email,
                ":no_hp" => $no_hp,
                ":jenis_akun" => $jenis_akun,
                ":password" => $passwordHash
            ]);

            if ($berhasil) {

                $pesan = "Pendaftaran berhasil! Akun PAPI KOS berhasil dibuat.";
                $status = "success";

            } else {

                $pesan = "Pendaftaran gagal. Silakan coba lagi.";
                $status = "error";

            }
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

    <title>Daftar Akun - PAPI KOS</title>

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

            <h1>Buat Akun</h1>

            <p>
                Bergabunglah dengan PAPI KOS
                dan temukan kemudahan mencari kos.
            </p>

        </div>

        <?php if ($pesan !== ""): ?>

            <p style="padding: 12px; margin-bottom: 15px;
                border-radius: 6px;
                background: <?php echo $status === "success" ? "#d1fae5" : "#fee2e2"; ?>;
                color: <?php echo $status === "success" ? "#065f46" : "#991b1b"; ?>;">

                <?php echo htmlspecialchars($pesan); ?>

            </p>

        <?php endif; ?>

        <form class="auth-form"
              method="POST"
              action="register.php">

            <div class="form-group">

                <label>Nama Lengkap</label>

                <input
                    type="text"
                    name="nama_lengkap"
                    placeholder="Masukkan nama lengkap"
                    required>

            </div>

            <div class="form-group">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    placeholder="contoh@email.com"
                    required>

            </div>

            <div class="form-group">

                <label>Nomor HP</label>

                <input
                    type="tel"
                    name="no_hp"
                    placeholder="Masukkan nomor HP"
                    required>

            </div>

            <div class="form-group">

                <label>Jenis Akun</label>

                <select name="jenis_akun" required>

                    <option value="">
                        Pilih jenis akun
                    </option>

                    <option value="pencari">
                        Pencari Kos
                    </option>

                    <option value="pemilik">
                        Pemilik Kos
                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Buat password"
                    minlength="8"
                    required>

            </div>

            <div class="form-group">

                <label>Konfirmasi Password</label>

                <input
                    type="password"
                    name="konfirmasi_password"
                    placeholder="Ulangi password"
                    minlength="8"
                    required>

            </div>

            <button type="submit"
                    class="auth-button">

                Daftar Sekarang

            </button>

        </form>

        <div class="auth-footer">

            <p>
                Sudah punya akun?

                <a href="login.php">
                    Login di sini
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
