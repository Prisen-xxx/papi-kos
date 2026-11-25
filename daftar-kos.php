
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

$query = $koneksi->prepare(
    "SELECT * FROM kos ORDER BY created_at DESC"
);

$query->execute();
$data_kos = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kos - PAPI KOS</title>

    <link rel="stylesheet" href="css/style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f6fb;
            font-family: Arial, sans-serif;
            color: #263449;
        }

        /* NAVBAR */
        .navbar {
            background: #082b4c;
            padding: 16px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .logo img {
            width: 145px;
            height: auto;
            display: block;
        }

        .navbar nav {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .navbar nav a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s;
        }

        .navbar nav a:hover {
            color: #8dc5ff;
        }

        /* HEADER */
        .page-header {
            position: relative;
            overflow: hidden;
            background: linear-gradient(120deg, #082b4c, #1768a8);
            color: white;
            padding: 65px 7%;
        }

        .page-header::before {
    content: "";
    position: absolute;
    width: 350px;
    height: 350px;
    right: 5%;
    top: 50%;
    transform: translateY(-50%);
    background: url("images/logo-papi-kos.png") center/contain no-repeat;
    filter: brightness(0) invert(1);
    opacity: 0.10;
    pointer-events: none;
}

        .header-content {
            position: relative;
            z-index: 1;
            max-width: 750px;
        }

        .header-label {
            display: inline-block;
            background: rgba(255,255,255,0.13);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 8px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 18px;
        }

        .page-header h1 {
            font-size: 36px;
            line-height: 1.25;
            margin: 0 0 15px;
        }

        .page-header p {
            color: #e0ebf8;
            font-size: 15px;
            line-height: 1.8;
            max-width: 600px;
            margin: 0;
        }

        /* PENCARIAN */
        .filter-section {
            padding: 30px 7%;
        }

        .filter-container {
            max-width: 1050px;
            margin: auto;
            display: flex;
            gap: 12px;
            background: white;
            padding: 17px;
            border-radius: 14px;
            box-shadow: 0 6px 24px rgba(20,43,74,0.07);
            border: 1px solid #e8eef6;
        }

        .filter-container input,
        .filter-container select {
            flex: 1;
            min-width: 0;
            padding: 14px;
            border: 1px solid #dce5ef;
            border-radius: 9px;
            outline: none;
            font-size: 14px;
            background: white;
            color: #263449;
        }

        .filter-container input:focus,
        .filter-container select:focus {
            border-color: #1768c4;
        }

        .filter-container button {
            padding: 13px 27px;
            background: #1768c4;
            color: white;
            border: none;
            border-radius: 9px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: 0.2s;
        }

        .filter-container button:hover {
            background: #1055a3;
        }

        /* DAFTAR KOS */
        .kos-section {
            padding: 10px 7% 70px;
        }

        .section-heading {
            max-width: 1200px;
            margin: 0 auto 25px;
        }

        .section-heading h2 {
            color: #082b4c;
            font-size: 25px;
            margin: 0 0 9px;
        }

        .section-heading p {
            color: #718096;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        .kos-container {
            max-width: 1200px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
        }

        .kos-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(20,43,74,0.07);
            border: 1px solid #e9eef5;
            transition: transform 0.25s, box-shadow 0.25s;
        }

        .kos-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(20,43,74,0.13);
        }

        .kos-card img {
            width: 100%;
            height: 210px;
            object-fit: cover;
            display: block;
        }

        .kos-no-image {
            width: 100%;
            height: 210px;
            background: #e9eff7;
            color: #718096;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .kos-info {
            padding: 20px;
        }

        .kos-type {
            display: inline-block;
            background: #e8f1ff;
            color: #1768c4;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .kos-info h3 {
            color: #082b4c;
            font-size: 19px;
            margin: 15px 0 13px;
        }

        /* IKON LOKASI */
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
        .kos-info .price {
            color: #1768c4;
            font-size: 20px;
            font-weight: bold;
            margin: 17px 0;
        }

        .status-row {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap;
            color: #718096;
            font-size: 13px;
        }

        .status-label {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-tersedia {
            background: #e5f8ec;
            color: #168447;
        }

        .status-penuh {
            background: #fff0f0;
            color: #b83232;
        }

        .detail-link {
            display: block;
            margin-top: 18px;
            padding: 13px;
            text-align: center;
            background: #1768c4;
            color: white;
            text-decoration: none;
            border-radius: 9px;
            font-weight: bold;
            font-size: 14px;
            transition: 0.2s;
        }

        .detail-link:hover {
            background: #1055a3;
        }

        /* PESAN KOS */
        .not-found {
            text-align: center;
            color: #718096;
            padding: 35px;
            grid-column: 1 / -1;
            background: white;
            border-radius: 12px;
        }

        .kos-kosong {
            max-width: 1200px;
            margin: auto;
            text-align: center;
            background: white;
            padding: 45px 20px;
            border-radius: 14px;
            color: #718096;
        }

        .kos-kosong h3 {
            color: #082b4c;
        }

        /* FOOTER */
        footer {
            background: #082b4c;
            color: #dce7f5;
            text-align: center;
            padding: 24px 15px;
            font-size: 13px;
        }

        /* RESPONSIVE */
        @media (max-width: 650px) {
            .navbar {
                padding: 17px 5%;
                flex-direction: column;
                align-items: flex-start;
            }

            .logo img {
                width: 130px;
            }

            .navbar nav {
                gap: 18px;
                flex-wrap: wrap;
            }

            .page-header {
                padding: 45px 6%;
            }

            .page-header h1 {
                font-size: 28px;
            }

            .page-header::before {
                width: 220px;
                height: 220px;
                right: -40px;
            }

            .filter-section {
                padding: 22px 5%;
            }

            .filter-container {
                flex-direction: column;
            }

            .filter-container input,
            .filter-container select,
            .filter-container button {
                width: 100%;
                min-height: 45px;
            }

            .kos-section {
                padding: 10px 5% 45px;
            }

            .kos-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<header class="navbar">

    <div class="logo">
        <a href="index.php">
            <img src="images/logo-papi-kos.png" alt="Logo PAPI KOS">
        </a>
    </div>

    <nav>
        <a href="index.php">Beranda</a>
        <a href="daftar-kos.php">Daftar Kos</a>
        <a href="logout.php">Keluar</a>
    </nav>

</header>

<section class="page-header">
    <div class="header-content">

        <span class="header-label">PAPI KOS • KEFAMENANU</span>

        <h1>Temukan Kos Impianmu</h1>

        <p>
            Temukan tempat tinggal yang nyaman,
            aman, dan sesuai kebutuhanmu
            di Kefamenanu melalui PAPI KOS.
        </p>

    </div>
</section>

<section class="filter-section">

    <div class="filter-container">

        <input
            type="text"
            id="searchKos"
            placeholder="Cari nama kos atau lokasi..."
            onkeyup="cariKos()">

        <select id="filterTipe" onchange="cariKos()">
            <option value="Semua">Semua Tipe</option>
            <option value="Putra">Kos Putra</option>
            <option value="Putri">Kos Putri</option>
            <option value="Campur">Kos Campur</option>
        </select>

        <button type="button" onclick="cariKos()">
            Cari Kos
        </button>

    </div>

</section>

<section class="kos-section">

    <div class="section-heading">
        <h2>Pilihan Kos</h2>
        <p>
            Temukan kos yang sesuai dengan kebutuhan dan anggaranmu.
        </p>
    </div>

    <?php if (count($data_kos) > 0): ?>

        <div class="kos-container">

            <?php foreach ($data_kos as $kos): ?>

                <div class="kos-card"
                    data-nama="<?php echo htmlspecialchars($kos["nama_kos"], ENT_QUOTES, 'UTF-8'); ?>"
                    data-lokasi="<?php echo htmlspecialchars($kos["alamat"], ENT_QUOTES, 'UTF-8'); ?>"
                    data-tipe="<?php echo htmlspecialchars($kos["jenis_kos"], ENT_QUOTES, 'UTF-8'); ?>">

                    <?php if (!empty($kos["foto"])): ?>

                        <img
                            src="uploads/<?php echo rawurlencode(basename($kos["foto"])); ?>"
                            alt="<?php echo htmlspecialchars($kos["nama_kos"]); ?>">

                    <?php else: ?>

                        <div class="kos-no-image">
                            Foto belum tersedia
                        </div>

                    <?php endif; ?>

                    <div class="kos-info">

                        <span class="kos-type">
                            <?php echo htmlspecialchars($kos["jenis_kos"]); ?>
                        </span>

                        <h3>
                            <?php echo htmlspecialchars($kos["nama_kos"]); ?>
                        </h3>

                        <div class="alamat-kos">

                            <span class="ikon-lokasi">
                                <svg viewBox="0 0 24 24"
                                    fill="none"
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
                                <?php echo nl2br(htmlspecialchars($kos["alamat"])); ?>
                            </span>

                        </div>

                        <p class="price">

                            Rp <?php echo number_format(
                                (float) $kos["harga"],
                                0,
                                ',',
                                '.'
                            ); ?>

                            <span style="font-size:13px;font-weight:normal;">
                                / bulan
                            </span>

                        </p>

                        <div class="status-row">
                            Status:

                            <span class="status-label <?php echo $kos["status"] === "Tersedia" ? "status-tersedia" : "status-penuh"; ?>">
                                <?php echo htmlspecialchars($kos["status"]); ?>
                            </span>
                        </div>

                        <a
                            href="detail-kos.php?id=<?php echo (int) $kos["id"]; ?>"
                            class="detail-link">
                            Lihat Detail
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <div id="tidakDitemukan" class="not-found" style="display:none;">
            Kos tidak ditemukan. Coba kata kunci atau tipe lainnya.
        </div>

    <?php else: ?>

        <div class="kos-kosong">
            <h3>Belum Ada Kos Terdaftar</h3>

            <p>
                Saat ini belum ada pemilik yang menambahkan data kos.
                Silakan kunjungi kembali nanti.
            </p>
        </div>

    <?php endif; ?>

</section>

<footer>
    <p>
        © 2026 PAPI KOS - Sistem Informasi Kos Kefamenanu
    </p>
</footer>

<script>
function cariKos() {

    let keyword = document
        .getElementById("searchKos")
        .value
        .toLowerCase();

    let tipe = document
        .getElementById("filterTipe")
        .value;

    let cards = document.querySelectorAll(".kos-card");
    let jumlah = 0;

    cards.forEach(function(card) {

        let nama = card.dataset.nama.toLowerCase();
        let lokasi = card.dataset.lokasi.toLowerCase();
        let jenis = card.dataset.tipe;

        let cocokKeyword =
            nama.includes(keyword) ||
            lokasi.includes(keyword);

        let cocokTipe =
            tipe === "Semua" ||
            jenis === tipe;

        if (cocokKeyword && cocokTipe) {
            card.style.display = "block";
            jumlah++;
        } else {
            card.style.display = "none";
        }

    });

    let pesan = document.getElementById("tidakDitemukan");

    if (pesan) {
        pesan.style.display = jumlah === 0 ? "block" : "none";
    }
}
</script>

</body>
</html>
