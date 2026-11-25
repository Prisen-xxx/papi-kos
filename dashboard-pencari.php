
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

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Pencari Kos - PAPI KOS</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        .dashboard-container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }

        .welcome-section {
            background: #2563eb;
            color: white;
            padding: 35px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .welcome-section h1 {
            margin-bottom: 10px;
        }

        .search-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .search-form {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .search-form input {
            flex: 1;
            padding: 13px;
            border: 1px solid #ddd;
            border-radius: 6px;
            min-width: 0;
        }

        .search-form button {
            padding: 13px 22px;
            border: none;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            cursor: pointer;
        }

        .kos-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
        }

        .kos-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .kos-image {
            background: #e5e7eb;
            height: 150px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            margin-bottom: 15px;
        }

        .kos-card h3 {
            margin-bottom: 8px;
        }

        .kos-price {
            color: #2563eb;
            font-weight: bold;
            margin-top: 12px;
        }

        .kos-location {
            color: #64748b;
            font-size: 14px;
        }

        @media (max-width: 600px) {

            .dashboard-container {
                padding: 15px;
                margin: 20px auto;
            }

            .welcome-section {
                padding: 25px;
            }

            .search-form {
                flex-direction: column;
            }

        }

    </style>

</head>

<body>

<header class="navbar">

    <div class="logo">
        <h2>PAPI KOS</h2>
    </div>

    <nav>
        <a href="dashboard-pencari.php">Dashboard</a>
        <a href="logout.php">Keluar</a>
    </nav>

</header>

<main class="dashboard-container">

    <!-- BAGIAN SAMBUTAN -->

    <section class="welcome-section">

        <h1>
            Selamat Datang,
            <?php echo htmlspecialchars($_SESSION["nama_lengkap"]); ?>!
        </h1>

        <p>
            Temukan kos yang sesuai dengan kebutuhanmu
            di wilayah Kefamenanu.
        </p>

    </section>


    <!-- BAGIAN PENCARIAN -->

    <section class="search-section">

        <h2>Cari Kos Impianmu</h2>

        <p>
            Masukkan nama atau lokasi kos yang ingin kamu cari.
        </p>

        <form class="search-form" method="GET" action="dashboard-pencari.php">

            <input
                type="text"
                name="cari"
                placeholder="Contoh: Kos dekat UNIMOR"
                value="<?php echo htmlspecialchars($_GET["cari"] ?? ""); ?>">

            <button type="submit">
                Cari Kos
            </button>

        </form>

    </section>


    <!-- BAGIAN DAFTAR KOS -->

    <section>

        <h2>Daftar Kos Tersedia</h2>

        <p style="margin: 10px 0 20px;">
            Berikut contoh tampilan informasi kos di PAPI KOS.
        </p>

        <div class="kos-container">

            <div class="kos-card">

                <div class="kos-image">
                    Foto Kos
                </div>

                <h3>Kos Putra</h3>

                <p class="kos-location">
                    Lokasi: Kefamenanu
                </p>

                <p class="kos-price">
                    Rp500.000 / bulan
                </p>

            </div>


            <div class="kos-card">

                <div class="kos-image">
                    Foto Kos
                </div>

                <h3>Kos Putri</h3>

                <p class="kos-location">
                    Lokasi: Kefamenanu
                </p>

                <p class="kos-price">
                    Rp600.000 / bulan
                </p>

            </div>


            <div class="kos-card">

                <div class="kos-image">
                    Foto Kos
                </div>

                <h3>Kos Campuran</h3>

                <p class="kos-location">
                    Lokasi: Kefamenanu
                </p>

                <p class="kos-price">
                    Rp450.000 / bulan
                </p>

            </div>

        </div>

    </section>

</main>

<footer>

    <p>
        © 2026 Papi Kos - Sistem Informasi Kos Kefamenanu
    </p>

</footer>

</body>
</html>
