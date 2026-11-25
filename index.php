
<?php

require_once "config/koneksi.php";

$query = $koneksi->prepare("
    SELECT * FROM kos
    WHERE status = 'Tersedia'
    ORDER BY created_at DESC
    LIMIT 4
");

$query->execute();
$data_kos = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>PAPI KOS - Temukan Kos Nyaman di Kefamenanu</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    background: #f4f7fc;
    color: #183153;
}

/* NAVBAR */

header {
    width: 100%;
    min-height: 82px;
    padding: 0 7%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #082b4c;
    box-shadow: 0 3px 15px rgba(0,0,0,0.15);
    position: relative;
    z-index: 10;
}

.logo img {
    height: 68px;
    width: auto;
    max-width: 220px;
    display: block;
    object-fit: contain;
}

nav {
    display: flex;
    align-items: center;
    gap: 30px;
}

nav a {
    color: white;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
    transition: 0.2s;
}

nav a:hover {
    color: #42c8ff;
}

/* HERO */

.hero {
    position: relative;
    min-height: 360px;
    padding: 65px 7%;
    display: flex;
    align-items: center;
    overflow: hidden;
    isolation: isolate;

    background:
        radial-gradient(circle at 85% 30%,
        rgba(0,159,255,0.55), transparent 35%),
        linear-gradient(115deg,
        #062d50 0%, #0756a5 55%, #087fbd 100%);

    color: white;
}

.hero::before {
    content: "";
    position: absolute;
    z-index: 0;
    width: 650px;
    height: 430px;
    top: 50%;
    right: 1%;
    transform: translateY(-50%);
    background-image: url("images/logo-papi-kos.png");
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    filter: brightness(0) invert(1);
    opacity: 0.12;
    pointer-events: none;
}

.hero-content {
    width: 100%;
    max-width: 750px;
    position: relative;
    z-index: 1;
}

.hero-label {
    display: inline-block;
    padding: 9px 15px;
    margin-bottom: 20px;
    border: 1px solid rgba(255,255,255,0.35);
    border-radius: 30px;
    background: rgba(255,255,255,0.10);
    color: #d9f3ff;
    font-size: 12px;
    font-weight: bold;
    letter-spacing: 1px;
}

.hero h1 {
    font-size: 48px;
    line-height: 1.16;
    margin-bottom: 18px;
    letter-spacing: -1px;
}

.hero h1 span {
    color: #42c8ff;
}

.hero p {
    max-width: 600px;
    font-size: 17px;
    line-height: 1.8;
    color: #e4f3ff;
    margin-bottom: 28px;
}

.tombol-cari {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 15px 25px;
    background: white;
    color: #0756a5;
    border-radius: 9px;
    text-decoration: none;
    font-size: 15px;
    font-weight: bold;
    box-shadow: 0 5px 18px rgba(0,0,0,0.15);
    transition: 0.25s;
}

.tombol-cari:hover {
    background: #e5f5ff;
    transform: translateY(-3px);
}

/* BAGIAN KOS */

.kos-section {
    max-width: 1250px;
    margin: 0 auto;
    padding: 60px 25px 55px;
}

.judul-section {
    text-align: center;
    margin-bottom: 38px;
}

.judul-section .garis {
    width: 75px;
    height: 4px;
    margin: 0 auto 15px;
    border-radius: 10px;
    background: linear-gradient(to right, #0876d1, #42c8ff);
}

.judul-section h2 {
    font-size: 29px;
    color: #092f57;
    margin-bottom: 12px;
}

.judul-section p {
    color: #718096;
    font-size: 15px;
    line-height: 1.6;
}

/* KARTU KOS */

.kos-container {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 23px;
}

.kos-card {
    background: white;
    border-radius: 17px;
    overflow: hidden;
    border: 1px solid #e8eef7;
    box-shadow: 0 5px 18px rgba(20,55,90,0.08);
    transition: transform 0.25s, box-shadow 0.25s;
}

.kos-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 14px 28px rgba(20,55,90,0.16);
}

.kos-foto {
    width: 100%;
    height: 210px;
    overflow: hidden;
    background: #e8edf4;
}

.kos-foto img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    transition: transform 0.35s;
}

.kos-card:hover .kos-foto img {
    transform: scale(1.05);
}

.foto-kosong {
    width: 100%;
    height: 210px;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #e8edf4;
    color: #718096;
    font-size: 14px;
}

.kos-info {
    padding: 19px;
}

.jenis-kos {
    display: inline-flex;
    align-items: center;
    padding: 7px 13px;
    margin-bottom: 13px;
    background: #e5f2ff;
    color: #0756a5;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.kos-info h3 {
    color: #092f57;
    font-size: 19px;
    line-height: 1.4;
    margin-bottom: 14px;
}

/* ALAMAT DAN IKON LOKASI */

.alamat-kos {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    min-height: 62px;
    padding: 11px;
    margin-bottom: 15px;
    background: #f0f7ff;
    border: 1px solid #e0edfb;
    border-radius: 10px;
    color: #52677f;
    font-size: 12px;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.ikon-lokasi {
    width: 29px;
    height: 29px;
    min-width: 29px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #d9ecff;
    border-radius: 8px;
    color: #0872c5;
}

.ikon-lokasi svg {
    width: 17px;
    height: 17px;
    display: block;
}

.alamat-teks {
    padding-top: 2px;
}

/* HARGA */

.pemisah {
    border: none;
    border-top: 1px solid #edf1f7;
    margin-bottom: 15px;
}

.label-harga {
    color: #718096;
    font-size: 12px;
    margin-bottom: 5px;
}

.price {
    color: #0756c5;
    font-size: 21px;
    font-weight: bold;
    margin-bottom: 17px;
}

.price span {
    font-size: 12px;
    font-weight: normal;
    color: #718096;
}

/* TOMBOL DETAIL */

.detail {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px;
    background: linear-gradient(135deg, #0756a5, #087fbd);
    color: white;
    text-decoration: none;
    border-radius: 9px;
    font-size: 14px;
    font-weight: bold;
    transition: 0.2s;
}

.detail:hover {
    background: #063b70;
}

/* KOS KOSONG */

.kos-kosong {
    background: white;
    padding: 35px;
    border-radius: 14px;
    text-align: center;
    color: #718096;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.kos-kosong h3 {
    color: #092f57;
    margin-bottom: 10px;
}

.lihat-semua {
    text-align: center;
    margin-top: 38px;
}

.tombol-semua {
    display: inline-block;
    padding: 14px 27px;
    background: #e5f2ff;
    color: #0756a5;
    border: 1px solid #b9dcff;
    border-radius: 9px;
    text-decoration: none;
    font-weight: bold;
    transition: 0.2s;
}

.tombol-semua:hover {
    background: #d2eaff;
}

/* FOOTER */

footer {
    background: #082b4c;
    color: #dcecff;
    text-align: center;
    padding: 24px 15px;
    margin-top: 25px;
    font-size: 14px;
}

/* TABLET */

@media (max-width: 950px) {
    .kos-container {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .hero h1 {
        font-size: 40px;
    }

    .hero::before {
        width: 480px;
        right: -100px;
    }
}

/* HP */

@media (max-width: 600px) {
    header {
        flex-direction: column;
        gap: 8px;
        padding: 10px 12px 15px;
    }

    .logo img {
        height: 60px;
    }

    nav {
        gap: 17px;
        flex-wrap: wrap;
        justify-content: center;
    }

    nav a {
        font-size: 12px;
    }

    .hero {
        min-height: 340px;
        padding: 45px 22px;
    }

    .hero::before {
        width: 300px;
        height: 240px;
        right: -75px;
        opacity: 0.10;
    }

    .hero-label {
        font-size: 10px;
        margin-bottom: 15px;
    }

    .hero h1 {
        font-size: 32px;
    }

    .hero p {
        font-size: 14px;
    }

    .tombol-cari {
        padding: 13px 20px;
        font-size: 13px;
    }

    .kos-section {
        padding: 45px 16px;
    }

    .judul-section h2 {
        font-size: 24px;
    }

    .judul-section p {
        font-size: 13px;
    }

    .kos-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .kos-foto,
    .foto-kosong {
        height: 220px;
    }
}

</style>
</head>

<body>

<header>

    <div class="logo">
        <a href="index.php">
            <img src="images/logo-papi-kos.png" alt="Logo PAPI KOS">
        </a>
    </div>

    <nav>
        <a href="index.php">Beranda</a>
        <a href="daftar-kos.php">Daftar Kos</a>
        <a href="login.php">Login</a>
        <a href="register.php">Daftar</a>
    </nav>

</header>

<section class="hero">

    <div class="hero-content">

        <div class="hero-label">
            PAPI KOS • KEFAMENANU
        </div>

        <h1>
            Temukan Kos Nyaman
            <br>
            <span>di Kefamenanu</span>
        </h1>

        <p>
            Cari kos terbaik dengan mudah, aman,
            dan terpercaya. Temukan tempat tinggal
            yang sesuai dengan kebutuhan dan anggaranmu
            melalui PAPI KOS.
        </p>

        <a href="daftar-kos.php" class="tombol-cari">
            Cari Kos Sekarang &nbsp; →
        </a>

    </div>

</section>

<section class="kos-section">

    <div class="judul-section">
        <div class="garis"></div>

        <h2>Kos Pilihan di Kefamenanu</h2>

        <p>
            Temukan tempat tinggal yang nyaman
            sesuai kebutuhan dan anggaranmu.
        </p>
    </div>

    <?php if (count($data_kos) > 0): ?>

        <div class="kos-container">

            <?php foreach ($data_kos as $kos): ?>

                <article class="kos-card">

                    <?php if (!empty($kos["foto"])): ?>

                        <div class="kos-foto">
                            <img
                                src="uploads/<?= htmlspecialchars($kos["foto"]) ?>"
                                alt="<?= htmlspecialchars($kos["nama_kos"]) ?>"
                            >
                        </div>

                    <?php else: ?>

                        <div class="foto-kosong">
                            Foto belum tersedia
                        </div>

                    <?php endif; ?>

                    <div class="kos-info">

                        <span class="jenis-kos">
                            <?= htmlspecialchars($kos["jenis_kos"]) ?>
                        </span>

                        <h3>
                            <?= htmlspecialchars($kos["nama_kos"]) ?>
                        </h3>

                        <div class="alamat-kos">

                            <span class="ikon-lokasi">
                                <svg viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor"
                                     stroke-width="2"
                                     stroke-linecap="round"
                                     stroke-linejoin="round"
                                     aria-hidden="true">
                                    <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/>
                                    <circle cx="12" cy="10" r="2.5"/>
                                </svg>
                            </span>

                            <span class="alamat-teks">
                                <?= htmlspecialchars($kos["alamat"]) ?>
                            </span>

                        </div>

                        <hr class="pemisah">

                        <p class="label-harga">
                            Harga sewa per bulan
                        </p>

                        <p class="price">
                            Rp <?= number_format(
                                (float) $kos["harga"],
                                0,
                                ",",
                                "."
                            ) ?>
                            <span>/ bulan</span>
                        </p>

                        <a
                            href="detail-kos.php?id=<?= (int) $kos["id"] ?>"
                            class="detail"
                        >
                            Lihat Detail <span>→</span>
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="kos-kosong">
            <h3>Belum Ada Kos Tersedia</h3>

            <p>
                Saat ini belum ada tempat kos yang tersedia.
                Silakan kunjungi kembali nanti.
            </p>
        </div>

    <?php endif; ?>

    <div class="lihat-semua">
        <a href="daftar-kos.php" class="tombol-semua">
            Lihat Semua Kos →
        </a>
    </div>

</section>

<footer>
    <p>
        © 2026 PAPI KOS - Sistem Informasi Kos Kefamenanu
    </p>
</footer>

</body>
</html>
