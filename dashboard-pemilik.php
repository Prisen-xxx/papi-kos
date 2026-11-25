
<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["jenis_akun"] !== "pemilik") {
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Pemilik - PAPI KOS</title>

    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            margin: 0;
            background: #f4f7fb;
            font-family: Arial, sans-serif;
            color: #263449;
        }

        header {
            background: #142b4a;
            padding: 18px 7%;
        }

        header nav {
            display: flex;
            justify-content: flex-end;
            gap: 28px;
        }

        header nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        header nav a:hover {
            color: #b9d8ff;
        }

        .dashboard-pemilik {
            max-width: 1050px;
            margin: 45px auto;
            padding: 20px;
        }

        .sambutan {
            background: linear-gradient(135deg, #17365d, #2876b9);
            color: white;
            padding: 40px;
            border-radius: 16px;
            margin-bottom: 30px;
        }

        .sambutan h1 {
            margin-top: 0;
            font-size: 28px;
        }

        .sambutan p {
            line-height: 1.7;
            margin-bottom: 0;
        }

        .judul-menu {
            margin-bottom: 20px;
        }

        .menu-dashboard {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 22px;
        }

        .kartu-menu {
            background: white;
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        .kartu-menu h3 {
            margin-top: 0;
            color: #17365d;
        }

        .kartu-menu p {
            color: #667085;
            line-height: 1.6;
        }

        .tombol {
            display: inline-block;
            margin-top: 12px;
            padding: 12px 20px;
            background: #176b45;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .tombol:hover {
            background: #105234;
        }

        .tombol-kelola {
            background: #1765a8;
        }

        .tombol-kelola:hover {
            background: #104b80;
        }

        @media (max-width: 600px) {
            header nav {
                justify-content: center;
                gap: 18px;
            }

            .dashboard-pemilik {
                margin: 20px auto;
            }

            .sambutan {
                padding: 25px;
            }

            .sambutan h1 {
                font-size: 23px;
            }

            .menu-dashboard {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<header>
    <nav>
        <a href="index.php">Beranda</a>
        <a href="dashboard-pemilik.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="dashboard-pemilik">

    <section class="sambutan">

        <h1>
            Selamat Datang,
            <?= htmlspecialchars($_SESSION["nama_lengkap"]) ?>!
        </h1>

        <p>
            Kelola tempat kos milikmu dengan mudah melalui PAPI KOS.
            Tambahkan informasi kos dan perbarui data agar calon penghuni
            dapat menemukan tempat tinggal yang sesuai.
        </p>

    </section>

    <h2 class="judul-menu">Kelola Kos Anda</h2>

    <section class="menu-dashboard">

        <div class="kartu-menu">

            <h3>Tambah Data Kos</h3>

            <p>
                Daftarkan tempat kos milikmu agar dapat ditemukan
                oleh calon pencari kos di Kefamenanu.
            </p>

            <a href="tambah-kos.php" class="tombol">
                + Tambah Kos
            </a>

        </div>

        <div class="kartu-menu">

            <h3>Kelola Data Kos</h3>

            <p>
                Lihat daftar kos milikmu, perbarui informasi,
                atau hapus data kos yang sudah tidak diperlukan.
            </p>

            <a href="kelola-kos.php" class="tombol tombol-kelola">
                Kelola Kos
            </a>

        </div>

    </section>

</main>

</body>
</html>
