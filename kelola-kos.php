
<?php

session_start();

require_once "config/koneksi.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["jenis_akun"] !== "pemilik") {
    header("Location: login.php");
    exit;
}

$query = $koneksi->prepare("
    SELECT * FROM kos
    WHERE pemilik_id = :pemilik_id
    ORDER BY created_at DESC
");

$query->execute([
    ":pemilik_id" => $_SESSION["user_id"]
]);

$data_kos = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Kos - PAPI KOS</title>

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

        nav a:hover,
        nav a.aktif {
            background: #294c75;
            color: #ffffff;
        }

        nav a.logout {
            background: #c62828;
        }

        nav a.logout:hover {
            background: #a51d1d;
        }

        /* KONTEN */

        .container-kelola {
            max-width: 1150px;
            margin: 40px auto;
            padding: 20px;
        }

        .judul-halaman {
            margin-bottom: 28px;
        }

        .judul-halaman h1 {
            margin-bottom: 10px;
            color: #17365d;
            font-size: 30px;
        }

        .judul-halaman p {
            color: #667085;
            line-height: 1.7;
        }

        /* TOMBOL */

        .tombol {
            display: inline-block;
            padding: 11px 17px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .tombol-tambah {
            background: #176b45;
        }

        .tombol-tambah:hover {
            background: #105234;
            transform: translateY(-2px);
        }

        .tombol-edit {
            background: #2468b4;
        }

        .tombol-edit:hover {
            background: #174e8c;
        }

        .tombol-hapus {
            background: #c62828;
        }

        .tombol-hapus:hover {
            background: #a51d1d;
        }

        /* DAFTAR KOS */

        .daftar-kos {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 25px;
            margin-top: 28px;
        }

        .kartu-kos {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.07);
            transition: 0.25s;
        }

        .kartu-kos:hover {
            transform: translateY(-4px);
            box-shadow: 0 9px 23px rgba(0,0,0,0.12);
        }

        /* GALERI FOTO */

        .galeri-kos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3px;
            background: #e8edf4;
        }

        .foto-item img {
            width: 100%;
            height: 155px;
            object-fit: cover;
            display: block;
        }

        .foto-kosong {
            height: 155px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e8edf4;
            color: #667085;
            font-size: 13px;
            text-align: center;
            padding: 10px;
        }

        .label-foto {
            display: block;
            padding: 7px 5px;
            background: #f1f5f9;
            color: #526176;
            font-size: 11px;
            text-align: center;
            font-weight: bold;
        }

        /* ISI KARTU */

        .isi-kartu {
            padding: 20px;
        }

        .isi-kartu h3 {
            color: #17365d;
            margin: 0 0 15px;
            font-size: 20px;
        }

        .isi-kartu p {
            font-size: 14px;
            line-height: 1.6;
            color: #596579;
            overflow-wrap: anywhere;
            margin: 10px 0;
        }

        .harga {
            color: #176b45 !important;
            font-size: 19px !important;
            font-weight: bold;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            background: #e0f5e9;
            color: #176b45;
            font-size: 12px;
            font-weight: bold;
        }

        .status-penuh {
            background: #fee2e2;
            color: #b42318;
        }

        .aksi-kos {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 17px;
            border-top: 1px solid #edf0f4;
        }

        .aksi-kos .tombol {
            flex: 1;
            text-align: center;
        }

        /* PESAN */

        .pesan {
            padding: 14px 18px;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 14px;
        }

        .sukses {
            background: #d9f5e4;
            color: #176b45;
        }

        /* KOS KOSONG */

        .kos-kosong {
            margin-top: 28px;
            padding: 45px 20px;
            background: white;
            border-radius: 14px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .kos-kosong h3 {
            color: #17365d;
            font-size: 22px;
        }

        .kos-kosong p {
            color: #667085;
            margin-bottom: 22px;
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

            .container-kelola {
                margin: 20px auto;
                padding: 15px;
            }

            .judul-halaman h1 {
                font-size: 25px;
            }

            .daftar-kos {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<header>

    <div class="navbar">

        <a href="index.php" class="logo">PAPI KOS</a>

        <nav>
            <a href="index.php">Beranda</a>

            <a href="dashboard-pemilik.php">Dashboard</a>

            <a href="kelola-kos.php" class="aktif">Kelola Kos</a>

            <a href="logout.php" class="logout">Logout</a>
        </nav>

    </div>

</header>

<main class="container-kelola">

    <section class="judul-halaman">

        <h1>Kelola Data Kos</h1>

        <p>
            Kelola dan perbarui informasi tempat kos milikmu.
            Pastikan informasi dan foto kos selalu sesuai dengan kondisi sebenarnya.
        </p>

    </section>

    <a href="tambah-kos.php" class="tombol tombol-tambah">
        + Tambah Kos Baru
    </a>

    <?php if (isset($_GET["pesan"]) && $_GET["pesan"] === "edit_berhasil"): ?>

        <div class="pesan sukses">
            Data kos berhasil diperbarui!
        </div>

    <?php elseif (isset($_GET["pesan"]) && $_GET["pesan"] === "hapus_berhasil"): ?>

        <div class="pesan sukses">
            Data kos berhasil dihapus!
        </div>

    <?php endif; ?>

    <?php if (count($data_kos) > 0): ?>

        <section class="daftar-kos">

            <?php foreach ($data_kos as $kos): ?>

                <article class="kartu-kos">

                    <div class="galeri-kos">

                        <div class="foto-item">

                            <?php if (!empty($kos["foto"])): ?>

                                <img
                                    src="uploads/<?= htmlspecialchars($kos["foto"]) ?>"
                                    alt="Foto luar <?= htmlspecialchars($kos["nama_kos"]) ?>"
                                >

                            <?php else: ?>

                                <div class="foto-kosong">
                                    Foto luar belum tersedia
                                </div>

                            <?php endif; ?>

                            <span class="label-foto">Bagian Luar Kos</span>

                        </div>

                        <div class="foto-item">

                            <?php if (!empty($kos["foto_kamar"])): ?>

                                <img
                                    src="uploads/<?= htmlspecialchars($kos["foto_kamar"]) ?>"
                                    alt="Foto kamar <?= htmlspecialchars($kos["nama_kos"]) ?>"
                                >

                            <?php else: ?>

                                <div class="foto-kosong">
                                    Foto kamar belum tersedia
                                </div>

                            <?php endif; ?>

                            <span class="label-foto">Bagian Dalam Kamar</span>

                        </div>

                    </div>

                    <div class="isi-kartu">

                        <h3>
                            <?= htmlspecialchars($kos["nama_kos"]) ?>
                        </h3>

                        <p>
                            Jenis:
                            <?= htmlspecialchars($kos["jenis_kos"]) ?>
                        </p>

                        <p class="harga">
                            Rp <?= number_format((float)$kos["harga"], 0, ",", ".") ?>
                            / bulan
                        </p>

                        <p>
                            <strong>Alamat:</strong><br>
                            <?= nl2br(htmlspecialchars($kos["alamat"])) ?>
                        </p>

                        <p>
                            <strong>Status:</strong>

                            <?php if ($kos["status"] === "Penuh"): ?>

                                <span class="status status-penuh">
                                    Penuh
                                </span>

                            <?php else: ?>

                                <span class="status">
                                    Tersedia
                                </span>

                            <?php endif; ?>

                        </p>

                        <div class="aksi-kos">

                            <a
                                href="edit-kos.php?id=<?= (int)$kos["id"] ?>"
                                class="tombol tombol-edit"
                            >
                                Edit Kos
                            </a>

                            <a
                                href="hapus-kos.php?id=<?= (int)$kos["id"] ?>"
                                class="tombol tombol-hapus"
                            >
                                Hapus Kos
                            </a>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        </section>

    <?php else: ?>

        <div class="kos-kosong">

            <h3>Belum Ada Data Kos</h3>

            <p>
                Kamu belum menambahkan tempat kos.
                Silakan tambahkan kos pertamamu.
            </p>

            <a href="tambah-kos.php" class="tombol tombol-tambah">
                + Tambah Kos Sekarang
            </a>

        </div>

    <?php endif; ?>

</main>

</body>
</html>
