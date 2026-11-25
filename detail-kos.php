
<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION["jenis_akun"] !== "pencari") {
    header("Location: login.php");
    exit;
}

require_once "config/koneksi.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    die("ID kos tidak valid.");
}

$query = $koneksi->prepare(
    "SELECT * FROM kos WHERE id = :id LIMIT 1"
);

$query->execute([
    ":id" => $id
]);

$kos = $query->fetch(PDO::FETCH_ASSOC);

if (!$kos) {
    die("Data kos tidak ditemukan.");
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Detail <?= htmlspecialchars($kos["nama_kos"]); ?> - PAPI KOS
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f6fb;
            color: #263449;
        }

        /* NAVBAR */

        .detail-header {
            background: white;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .detail-navbar {
            max-width: 1200px;
            min-height: 75px;
            margin: auto;
            padding: 0 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .detail-logo {
            color: #1765c1;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }

        .detail-nav {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .detail-nav a {
            text-decoration: none;
            color: #344054;
            font-weight: bold;
            font-size: 14px;
            padding: 11px 15px;
            border-radius: 7px;
            transition: 0.2s;
        }

        .detail-nav a:hover {
            background: #eaf2ff;
            color: #1765c1;
        }

        .detail-nav .keluar {
            background: #c62828;
            color: white;
        }

        .detail-nav .keluar:hover {
            background: #a51d1d;
            color: white;
        }

        /* CONTAINER */

        .detail-container {
            max-width: 1100px;
            width: calc(100% - 40px);
            margin: 40px auto;
        }

        .detail-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.07);
        }

        /* FOTO */

        .judul-galeri {
            color: #17365d;
            font-size: 21px;
            margin: 0 0 18px;
        }

        .galeri-foto {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .galeri-item {
            min-width: 0;
            background: #f7f9fc;
            padding: 12px;
            border-radius: 12px;
        }

        .galeri-item h3 {
            margin: 5px 0 12px;
            font-size: 15px;
            color: #344054;
        }

        .detail-foto,
        .detail-no-foto {
            width: 100%;
            height: 340px;
            border-radius: 9px;
            display: block;
        }

        .detail-foto {
            object-fit: contain;
            background: #e9eef5;
        }

        .detail-no-foto {
            background: #e9eef5;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #667085;
            padding: 15px;
        }

        /* INFORMASI KOS */

        .kos-type {
            display: inline-block;
            background: #e8f1ff;
            color: #1765c1;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .detail-nama {
            font-size: 30px;
            color: #17365d;
            margin: 15px 0 8px;
        }

        .detail-harga {
            color: #168447;
            font-size: 25px;
            font-weight: bold;
            margin: 0 0 25px;
        }

        .informasi-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-top: 25px;
        }

        .info-box {
            background: #f7f9fc;
            padding: 20px;
            border-radius: 11px;
            border: 1px solid #e8edf4;
        }

        .info-box h3 {
            color: #17365d;
            font-size: 16px;
            margin: 0 0 12px;
        }

        .info-box p {
            color: #596579;
            font-size: 14px;
            line-height: 1.8;
            margin: 0;
            overflow-wrap: anywhere;
        }

        .status-teks {
            color: #168447 !important;
            font-weight: bold;
        }

        /* TOMBOL */

        .detail-tombol {
            display: inline-block;
            padding: 13px 22px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin-top: 25px;
            transition: 0.2s;
        }

        .tombol-kembali {
            background: #64748b;
        }

        .tombol-kembali:hover {
            background: #475569;
        }

        .tombol-booking {
            background: #168447;
            margin-left: 8px;
        }

        .tombol-booking:hover {
            background: #116b38;
        }

        /* FOOTER */

        footer {
            background: #142b4a;
            color: white;
            text-align: center;
            padding: 22px 15px;
            margin-top: 50px;
            font-size: 13px;
        }

        /* RESPONSIVE */

        @media (max-width: 700px) {

            .detail-navbar {
                flex-direction: column;
                gap: 12px;
                padding: 15px;
            }

            .detail-nav {
                justify-content: center;
                flex-wrap: wrap;
                gap: 3px;
            }

            .detail-nav a {
                font-size: 12px;
                padding: 9px;
            }

            .detail-container {
                width: calc(100% - 24px);
                margin: 20px auto;
            }

            .detail-card {
                padding: 17px;
            }

            .galeri-foto,
            .informasi-grid {
                grid-template-columns: 1fr;
            }

            .detail-foto,
            .detail-no-foto {
                height: 280px;
            }

            .detail-nama {
                font-size: 24px;
            }

            .detail-harga {
                font-size: 21px;
            }

            .detail-tombol {
                display: block;
                margin: 12px 0 0;
            }

            .tombol-booking {
                margin-left: 0;
            }

        }

    </style>

</head>

<body>

<!-- NAVBAR -->

<header class="detail-header">

    <div class="detail-navbar">

        <a href="index.php" class="detail-logo">
            PAPI KOS
        </a>

        <nav class="detail-nav">
            <a href="index.php">Beranda</a>
            <a href="daftar-kos.php">Daftar Kos</a>
            <a href="logout.php" class="keluar">Keluar</a>
        </nav>

    </div>

</header>


<!-- DETAIL KOS -->

<main class="detail-container">

    <div class="detail-card">

        <h2 class="judul-galeri">Foto Kos</h2>

        <div class="galeri-foto">

            <div class="galeri-item">

                <h3>Foto Bagian Luar Kos</h3>

                <?php if (!empty($kos["foto"])): ?>

                    <img
                        src="uploads/<?= rawurlencode(basename($kos["foto"])); ?>"
                        alt="Foto bagian luar kos"
                        class="detail-foto">

                <?php else: ?>

                    <div class="detail-no-foto">
                        Foto bagian luar belum tersedia.
                    </div>

                <?php endif; ?>

            </div>


            <div class="galeri-item">

                <h3>Foto Bagian Dalam Kamar</h3>

                <?php if (!empty($kos["foto_kamar"])): ?>

                    <img
                        src="uploads/<?= rawurlencode(basename($kos["foto_kamar"])); ?>"
                        alt="Foto bagian dalam kamar"
                        class="detail-foto">

                <?php else: ?>

                    <div class="detail-no-foto">
                        Foto bagian dalam kamar belum tersedia.
                    </div>

                <?php endif; ?>

            </div>

        </div>


        <span class="kos-type">
            <?= htmlspecialchars($kos["jenis_kos"]); ?>
        </span>

        <h1 class="detail-nama">
            <?= htmlspecialchars($kos["nama_kos"]); ?>
        </h1>

        <p class="detail-harga">
            Rp <?= number_format((float) $kos["harga"], 0, ',', '.'); ?>
            / bulan
        </p>


        <div class="informasi-grid">

            <div class="info-box">
                <h3>Alamat Kos</h3>
                <p>
                    <?= nl2br(htmlspecialchars($kos["alamat"])); ?>
                </p>
            </div>

            <div class="info-box">
                <h3>Fasilitas</h3>
                <p>
                    <?= !empty($kos["fasilitas"])
                        ? nl2br(htmlspecialchars($kos["fasilitas"]))
                        : "Informasi fasilitas belum tersedia."; ?>
                </p>
            </div>

            <div class="info-box">
                <h3>Deskripsi</h3>
                <p>
                    <?= !empty($kos["deskripsi"])
                        ? nl2br(htmlspecialchars($kos["deskripsi"]))
                        : "Deskripsi belum tersedia."; ?>
                </p>
            </div>

            <div class="info-box">
                <h3>Status Kos</h3>
                <p class="status-teks">
                    <?= htmlspecialchars($kos["status"]); ?>
                </p>
            </div>

        </div>


        <a href="daftar-kos.php" class="detail-tombol tombol-kembali">
            Kembali ke Daftar Kos
        </a>


        <?php if ($kos["status"] === "Tersedia"): ?>

            <a href="booking.php?id=<?= (int) $kos["id"]; ?>"
               class="detail-tombol tombol-booking">
                Booking Kos via WhatsApp
            </a>

        <?php endif; ?>

    </div>

</main>


<footer>
    <p>
        © 2026 PAPI KOS - Sistem Informasi Kos Kefamenanu
    </p>
</footer>

</body>
</html>
