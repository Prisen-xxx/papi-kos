
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

$pesan = "";
$status_pesan = "";
$link_whatsapp = "";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    die("ID kos tidak valid.");
}

$query = $koneksi->prepare(
    "SELECT kos.*, users.nama_lengkap AS nama_pemilik,
            users.no_hp AS hp_pemilik
     FROM kos
     INNER JOIN users ON kos.pemilik_id = users.id
     WHERE kos.id = :id
     LIMIT 1"
);

$query->execute([":id" => $id]);
$kos = $query->fetch(PDO::FETCH_ASSOC);

if (!$kos) {
    die("Data kos tidak ditemukan.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $tanggal_booking = $_POST["tanggal_booking"] ?? "";
    $lama_sewa = (int) ($_POST["lama_sewa"] ?? 0);
    $pesan_pencari = trim($_POST["pesan"] ?? "");

    if ($kos["status"] !== "Tersedia") {

        $pesan = "Maaf, kos ini sedang penuh.";
        $status_pesan = "error";

    } elseif ($tanggal_booking === "" || $lama_sewa <= 0) {

        $pesan = "Tanggal mulai dan lama sewa wajib diisi.";
        $status_pesan = "error";

    } elseif (
        strtotime($tanggal_booking) === false ||
        $tanggal_booking < date("Y-m-d")
    ) {

        $pesan = "Tanggal mulai sewa tidak boleh sebelum hari ini.";
        $status_pesan = "error";

    } elseif ($lama_sewa > 120) {

        $pesan = "Lama sewa maksimal 120 bulan.";
        $status_pesan = "error";

    } else {

        $query = $koneksi->prepare(
            "INSERT INTO booking
            (kos_id, pencari_id, tanggal_booking, lama_sewa, pesan)
            VALUES
            (:kos_id, :pencari_id, :tanggal_booking, :lama_sewa, :pesan)"
        );

        $query->execute([
            ":kos_id" => $id,
            ":pencari_id" => $_SESSION["user_id"],
            ":tanggal_booking" => $tanggal_booking,
            ":lama_sewa" => $lama_sewa,
            ":pesan" => $pesan_pencari
        ]);

        $nomor_wa = preg_replace('/\D/', '', $kos["hp_pemilik"]);

        if (substr($nomor_wa, 0, 1) === "0") {
            $nomor_wa = "62" . substr($nomor_wa, 1);
        } elseif (substr($nomor_wa, 0, 2) !== "62") {
            $nomor_wa = "62" . $nomor_wa;
        }

        if (strlen($nomor_wa) < 10 || strlen($nomor_wa) > 15) {

            $pesan = "Nomor WhatsApp pemilik belum valid. Silakan hubungi pemilik melalui informasi kontak yang tersedia.";
            $status_pesan = "error";

        } else {

            $isi_pesan =
                "Halo, saya ingin memesan kos melalui PAPI KOS.\n\n" .
                "Nama pencari: " . $_SESSION["nama_lengkap"] . "\n" .
                "Nama kos: " . $kos["nama_kos"] . "\n" .
                "Jenis kos: " . $kos["jenis_kos"] . "\n" .
                "Alamat: " . $kos["alamat"] . "\n" .
                "Harga: Rp " . number_format((float) $kos["harga"], 0, ',', '.') . " per bulan\n" .
                "Tanggal mulai sewa: " . $tanggal_booking . "\n" .
                "Lama sewa: " . $lama_sewa . " bulan\n" .
                "Pesan: " . ($pesan_pencari !== "" ? $pesan_pencari : "-") .
                "\n\nMohon informasi lebih lanjut. Terima kasih.";

            $link_whatsapp =
                "https://wa.me/" . $nomor_wa .
                "?text=" . urlencode($isi_pesan);

            $pesan = "Permintaan berhasil disiapkan. Silakan lanjutkan ke WhatsApp untuk menghubungi pemilik kos.";
            $status_pesan = "sukses";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Booking Kos - PAPI KOS</title>

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

        .navbar {
            background: #142b4a;
            color: white;
            padding: 17px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .logo h2 {
            margin: 0;
            font-size: 25px;
            letter-spacing: 1px;
        }

        .navbar nav {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        .navbar nav a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .navbar nav a:hover {
            color: #8dc5ff;
        }

        .booking-container {
            width: 92%;
            max-width: 850px;
            margin: 45px auto;
        }

        .judul-halaman {
            text-align: center;
            margin-bottom: 28px;
        }

        .judul-halaman h1 {
            color: #142b4a;
            font-size: 30px;
            margin-bottom: 10px;
        }

        .judul-halaman p {
            color: #718096;
            line-height: 1.6;
            font-size: 15px;
        }

        .booking-card {
            background: white;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(25, 50, 80, 0.08);
            border: 1px solid #e9eef5;
        }

        .bagian-info {
            background: #f2f7ff;
            border: 1px solid #dce9fa;
            padding: 22px;
            border-radius: 12px;
            margin-bottom: 28px;
        }

        .bagian-info h3 {
            color: #142b4a;
            font-size: 21px;
            margin: 0 0 17px;
        }

        .info-baris {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #e0e9f5;
            font-size: 14px;
        }

        .info-baris:last-child {
            border-bottom: none;
        }

        .info-baris span:first-child {
            color: #718096;
        }

        .info-baris strong {
            color: #263449;
            text-align: right;
        }

        .harga-kos {
            color: #1768c4 !important;
            font-size: 17px;
        }

        .status-tersedia {
            color: #168447 !important;
        }

        .subjudul-form {
            color: #142b4a;
            font-size: 19px;
            margin-bottom: 8px;
        }

        .keterangan-form {
            color: #718096;
            font-size: 14px;
            margin-bottom: 22px;
            line-height: 1.6;
        }

        .booking-card label {
            display: block;
            margin-top: 18px;
            margin-bottom: 9px;
            color: #34445a;
            font-weight: 600;
            font-size: 14px;
        }

        .booking-card input,
        .booking-card textarea {
            width: 100%;
            padding: 14px;
            border: 1px solid #d9e1ec;
            border-radius: 9px;
            font-size: 14px;
            outline: none;
            background: #fff;
            color: #263449;
            transition: 0.2s;
        }

        .booking-card input:focus,
        .booking-card textarea:focus {
            border-color: #2878cc;
            box-shadow: 0 0 0 3px rgba(40, 120, 204, 0.10);
        }

        .booking-card textarea {
            min-height: 115px;
            resize: vertical;
        }

        .tombol-submit,
        .tombol-wa {
            display: block;
            width: 100%;
            margin-top: 25px;
            padding: 15px;
            border: none;
            border-radius: 9px;
            color: white;
            text-align: center;
            text-decoration: none;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
        }

        .tombol-submit {
            background: #1768c4;
        }

        .tombol-submit:hover {
            background: #1055a3;
        }

        .tombol-wa {
            background: #20a65a;
        }

        .tombol-wa:hover {
            background: #168447;
        }

        .booking-pesan {
            padding: 15px;
            margin-bottom: 22px;
            border-radius: 9px;
            font-size: 14px;
            line-height: 1.5;
        }

        .sukses {
            background: #e5f8ec;
            color: #176b39;
            border: 1px solid #bce8cb;
        }

        .error {
            background: #fff0f0;
            color: #a83232;
            border: 1px solid #f3cccc;
        }

        .catatan-wa {
            text-align: center;
            color: #718096;
            font-size: 13px;
            margin-top: 13px;
            line-height: 1.5;
        }

        .tombol-kembali {
            display: inline-block;
            margin-top: 24px;
            color: #1768c4;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
        }

        .tombol-kembali:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            padding: 24px 15px;
            background: #142b4a;
            color: #dce7f5;
            font-size: 13px;
        }

        @media (max-width: 600px) {

            .navbar {
                padding: 17px 5%;
                flex-direction: column;
                align-items: flex-start;
            }

            .navbar nav {
                gap: 17px;
            }

            .booking-container {
                margin: 28px auto;
            }

            .booking-card {
                padding: 20px;
            }

            .judul-halaman h1 {
                font-size: 25px;
            }

            .info-baris {
                flex-direction: column;
                gap: 5px;
            }

            .info-baris strong {
                text-align: left;
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
        <a href="index.php">Beranda</a>
        <a href="daftar-kos.php">Daftar Kos</a>
        <a href="logout.php">Keluar</a>
    </nav>

</header>

<section class="booking-container">

    <div class="judul-halaman">
        <h1>Booking Kos</h1>
        <p>
            Lengkapi informasi penyewaan untuk menghubungi pemilik kos secara langsung.
        </p>
    </div>

    <div class="booking-card">

        <?php if ($pesan !== ""): ?>

            <div class="booking-pesan <?php echo $status_pesan; ?>">
                <?php echo htmlspecialchars($pesan); ?>
            </div>

        <?php endif; ?>

        <div class="bagian-info">

            <h3><?php echo htmlspecialchars($kos["nama_kos"]); ?></h3>

            <div class="info-baris">
                <span>Nama Pemilik</span>
                <strong><?php echo htmlspecialchars($kos["nama_pemilik"]); ?></strong>
            </div>

            <div class="info-baris">
                <span>Jenis Kos</span>
                <strong><?php echo htmlspecialchars($kos["jenis_kos"]); ?></strong>
            </div>

            <div class="info-baris">
                <span>Alamat</span>
                <strong><?php echo htmlspecialchars($kos["alamat"]); ?></strong>
            </div>

            <div class="info-baris">
                <span>Harga Sewa</span>
                <strong class="harga-kos">
                    Rp <?php echo number_format(
                        (float) $kos["harga"],
                        0,
                        ',',
                        '.'
                    ); ?> / bulan
                </strong>
            </div>

            <div class="info-baris">
                <span>Status</span>
                <strong class="<?php echo $kos["status"] === "Tersedia" ? "status-tersedia" : ""; ?>">
                    <?php echo htmlspecialchars($kos["status"]); ?>
                </strong>
            </div>

        </div>

        <?php if (!empty($link_whatsapp)): ?>

            <a
                href="<?php echo htmlspecialchars($link_whatsapp, ENT_QUOTES, 'UTF-8'); ?>"
                target="_blank"
                rel="noopener noreferrer"
                class="tombol-wa">

                Lanjutkan ke WhatsApp

            </a>

            <p class="catatan-wa">
                WhatsApp akan terbuka dengan pesan booking yang sudah disiapkan.
                Periksa kembali pesan sebelum mengirimkannya.
            </p>

        <?php elseif ($kos["status"] === "Tersedia"): ?>

            <h3 class="subjudul-form">Informasi Penyewaan</h3>

            <p class="keterangan-form">
                Masukkan tanggal mulai dan lama penyewaan yang kamu inginkan.
                Setelah itu, kamu dapat menghubungi pemilik melalui WhatsApp.
            </p>

            <form method="POST">

                <label for="tanggal_booking">
                    Tanggal Mulai Sewa
                </label>

                <input
                    type="date"
                    id="tanggal_booking"
                    name="tanggal_booking"
                    min="<?php echo date('Y-m-d'); ?>"
                    required>

                <label for="lama_sewa">
                    Lama Sewa (Bulan)
                </label>

                <input
                    type="number"
                    id="lama_sewa"
                    name="lama_sewa"
                    min="1"
                    max="120"
                    placeholder="Contoh: 6"
                    required>

                <label for="pesan">
                    Pesan untuk Pemilik (Opsional)
                </label>

                <textarea
                    id="pesan"
                    name="pesan"
                    placeholder="Tuliskan pesan kepada pemilik kos..."></textarea>

                <button type="submit" class="tombol-submit">
                    Siapkan Booking WhatsApp
                </button>

            </form>

        <?php else: ?>

            <p>
                Kos ini sedang penuh dan belum dapat dipesan.
            </p>

        <?php endif; ?>

        <a
            href="detail-kos.php?id=<?php echo (int) $kos["id"]; ?>"
            class="tombol-kembali">

            ← Kembali ke Detail Kos

        </a>

    </div>

</section>

<footer>
    <p>© 2026 PAPI KOS - Sistem Informasi Kos Kefamenanu</p>
</footer>

</body>
</html>
