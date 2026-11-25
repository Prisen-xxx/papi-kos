
<?php

session_start();

require_once "config/koneksi.php";

// Pastikan hanya pemilik kos yang bisa menghapus
if (!isset($_SESSION["user_id"]) || $_SESSION["jenis_akun"] !== "pemilik") {
    header("Location: login.php");
    exit;
}

// Ambil ID kos
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    die("ID kos tidak valid.");
}

// Cari kos milik pengguna yang sedang login
$query = $koneksi->prepare("
    SELECT * FROM kos
    WHERE id = :id
    AND pemilik_id = :pemilik_id
");

$query->execute([
    ":id" => $id,
    ":pemilik_id" => $_SESSION["user_id"]
]);

$kos = $query->fetch(PDO::FETCH_ASSOC);

if (!$kos) {
    die("Data kos tidak ditemukan atau bukan milik akun Anda.");
}

// Proses penghapusan hanya melalui POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {

        $koneksi->beginTransaction();

        $hapus = $koneksi->prepare("
            DELETE FROM kos
            WHERE id = :id
            AND pemilik_id = :pemilik_id
        ");

        $hapus->execute([
            ":id" => $id,
            ":pemilik_id" => $_SESSION["user_id"]
        ]);

        $koneksi->commit();

        // Hapus foto bagian luar
        if (!empty($kos["foto"])) {

            $lokasi_foto = "uploads/" . basename($kos["foto"]);

            if (is_file($lokasi_foto)) {
                unlink($lokasi_foto);
            }
        }

        // Hapus foto bagian dalam kamar
        if (!empty($kos["foto_kamar"])) {

            $lokasi_foto_kamar = "uploads/" . basename($kos["foto_kamar"]);

            if (is_file($lokasi_foto_kamar)) {
                unlink($lokasi_foto_kamar);
            }
        }

        header("Location: kelola-kos.php?pesan=hapus_berhasil");
        exit;

    } catch (PDOException $e) {

        if ($koneksi->inTransaction()) {
            $koneksi->rollBack();
        }

        die("Gagal menghapus data kos. Silakan coba kembali.");
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hapus Kos - PAPI KOS</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f7fb;
    color: #263449;
}

/* NAVBAR */

header {
    background: #142b4a;
    padding: 0 7%;
    box-shadow: 0 3px 12px rgba(0,0,0,0.12);
}

.navbar {
    max-width: 1200px;
    min-height: 75px;
    margin: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    color: white;
    font-size: 23px;
    font-weight: bold;
    text-decoration: none;
    letter-spacing: 1px;
}

nav {
    display: flex;
    align-items: center;
    gap: 10px;
}

nav a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    font-size: 14px;
    padding: 11px 15px;
    border-radius: 7px;
    transition: 0.2s;
}

nav a:hover {
    background: #294c75;
}

nav a.logout {
    background: #c62828;
}

nav a.logout:hover {
    background: #a51d1d;
}

/* KONFIRMASI HAPUS */

.konfirmasi {
    max-width: 550px;
    margin: 65px auto;
    padding: 35px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.09);
    text-align: center;
}

.konfirmasi h2 {
    color: #17365d;
    margin-top: 0;
    margin-bottom: 20px;
}

.konfirmasi h3 {
    color: #c62828;
    font-size: 21px;
    margin: 20px 0;
}

.konfirmasi p {
    color: #596579;
    line-height: 1.6;
}

.tombol {
    display: inline-block;
    padding: 12px 22px;
    border: none;
    border-radius: 8px;
    color: white;
    text-decoration: none;
    cursor: pointer;
    margin: 12px 5px 0;
    font-size: 14px;
    font-weight: bold;
    transition: 0.2s;
}

.tombol-hapus {
    background: #c62828;
}

.tombol-hapus:hover {
    background: #a51d1d;
}

.tombol-batal {
    background: #64748b;
}

.tombol-batal:hover {
    background: #475569;
}

/* RESPONSIVE */

@media (max-width: 700px) {

    header {
        padding: 12px;
    }

    .navbar {
        flex-direction: column;
        gap: 12px;
        padding: 10px 0;
    }

    nav {
        justify-content: center;
        flex-wrap: wrap;
        gap: 5px;
    }

    nav a {
        font-size: 12px;
        padding: 9px 10px;
    }

    .konfirmasi {
        margin: 25px 15px;
        padding: 25px 18px;
    }

    .tombol {
        display: block;
        width: 100%;
        margin: 10px 0;
    }
}

</style>
</head>

<body>

<header>
    <div class="navbar">

        <a href="index.php" class="logo">
            PAPI KOS
        </a>

        <nav>
            <a href="index.php">Beranda</a>

            <a href="dashboard-pemilik.php">
                Dashboard
            </a>

            <a href="kelola-kos.php">
                Kelola Kos
            </a>

            <a href="logout.php" class="logout">
                Logout
            </a>
        </nav>

    </div>
</header>

<main class="konfirmasi">

    <h2>Konfirmasi Hapus Kos</h2>

    <p>Apakah kamu yakin ingin menghapus kos berikut?</p>

    <h3><?= htmlspecialchars($kos["nama_kos"]) ?></h3>

    <p>
        Jenis:
        <?= htmlspecialchars($kos["jenis_kos"]) ?>
    </p>

    <p>
        Alamat:
        <?= htmlspecialchars($kos["alamat"]) ?>
    </p>

    <form method="POST">

        <button type="submit" class="tombol tombol-hapus">
            Ya, Hapus Kos
        </button>

        <a href="kelola-kos.php" class="tombol tombol-batal">
            Batal
        </a>

    </form>

</main>

</body>
</html>
