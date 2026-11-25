
<?php

session_start();

require_once "config/koneksi.php";

// Pastikan pengguna sudah login sebagai pemilik kos
if (!isset($_SESSION["user_id"]) || $_SESSION["jenis_akun"] !== "pemilik") {
    header("Location: login.php");
    exit;
}

$pesan = "";
$status = "";

// Fungsi untuk memproses upload foto
function uploadFoto($nama_input, $awalan) {

    if (!isset($_FILES[$nama_input]) || $_FILES[$nama_input]["error"] === UPLOAD_ERR_NO_FILE) {
        throw new Exception("Kedua foto kos wajib diunggah.");
    }

    if ($_FILES[$nama_input]["error"] !== UPLOAD_ERR_OK) {
        throw new Exception("Foto gagal diunggah. Periksa file yang dipilih.");
    }

    $ukuran = $_FILES[$nama_input]["size"];
    $tipe = mime_content_type($_FILES[$nama_input]["tmp_name"]);

    $tipe_diizinkan = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if ($ukuran > 2 * 1024 * 1024) {
        throw new Exception("Ukuran masing-masing foto maksimal 2 MB.");
    }

    if (!isset($tipe_diizinkan[$tipe])) {
        throw new Exception("Format foto harus JPG, PNG, atau WEBP.");
    }

    $folder = "uploads/";

    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    $nama_foto = uniqid($awalan, true) . "." . $tipe_diizinkan[$tipe];

    if (!move_uploaded_file(
        $_FILES[$nama_input]["tmp_name"],
        $folder . $nama_foto
    )) {
        throw new Exception("Foto gagal disimpan ke folder uploads.");
    }

    return $nama_foto;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama_kos = trim($_POST["nama_kos"] ?? "");
    $jenis_kos = $_POST["jenis_kos"] ?? "";
    $alamat = trim($_POST["alamat"] ?? "");
    $harga = trim($_POST["harga"] ?? "");
    $fasilitas = trim($_POST["fasilitas"] ?? "");
    $deskripsi = trim($_POST["deskripsi"] ?? "");

    $foto_luar = null;
    $foto_kamar = null;

    try {

        if (
            $nama_kos === "" ||
            $jenis_kos === "" ||
            $alamat === "" ||
            $harga === ""
        ) {
            throw new Exception("Nama kos, jenis kos, alamat, dan harga wajib diisi.");
        }

        if (!in_array($jenis_kos, ["Putra", "Putri", "Campur"], true)) {
            throw new Exception("Jenis kos tidak valid.");
        }

        if (!is_numeric($harga) || (float)$harga <= 0) {
            throw new Exception("Harga harus berupa angka lebih dari 0.");
        }

        // Upload foto bagian luar
        $foto_luar = uploadFoto("foto", "luar_");

        // Upload foto bagian dalam kamar
        $foto_kamar = uploadFoto("foto_kamar", "kamar_");

        // Simpan data ke database
        $query = $koneksi->prepare("
            INSERT INTO kos
            (
                pemilik_id,
                nama_kos,
                jenis_kos,
                alamat,
                harga,
                fasilitas,
                deskripsi,
                foto,
                foto_kamar
            )
            VALUES
            (
                :pemilik_id,
                :nama_kos,
                :jenis_kos,
                :alamat,
                :harga,
                :fasilitas,
                :deskripsi,
                :foto,
                :foto_kamar
            )
        ");

        $query->execute([
            ":pemilik_id" => $_SESSION["user_id"],
            ":nama_kos" => $nama_kos,
            ":jenis_kos" => $jenis_kos,
            ":alamat" => $alamat,
            ":harga" => $harga,
            ":fasilitas" => $fasilitas,
            ":deskripsi" => $deskripsi,
            ":foto" => $foto_luar,
            ":foto_kamar" => $foto_kamar
        ]);

        $pesan = "Data kos berhasil ditambahkan!";
        $status = "sukses";

    } catch (Exception $e) {

        // Hapus foto jika gagal menyimpan data
        if ($foto_luar !== null && file_exists("uploads/" . $foto_luar)) {
            unlink("uploads/" . $foto_luar);
        }

        if ($foto_kamar !== null && file_exists("uploads/" . $foto_kamar)) {
            unlink("uploads/" . $foto_kamar);
        }

        $pesan = $e->getMessage();
        $status = "error";

    } catch (PDOException $e) {

        $pesan = "Gagal menyimpan data kos ke database.";
        $status = "error";

    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Kos - PAPI KOS</title>

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
        color: white;
    }

    nav a.logout {
        background: #c62828;
    }

    nav a.logout:hover {
        background: #a51d1d;
    }

    /* FORM */

    .form-kos {
        max-width: 650px;
        margin: 40px auto;
        padding: 28px;
        background: white;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }

    .form-kos h2 {
        margin-bottom: 20px;
        color: #17365d;
    }

    .form-kos label {
        display: block;
        margin-top: 17px;
        margin-bottom: 7px;
        font-weight: bold;
        color: #344054;
    }

    .form-kos input,
    .form-kos select,
    .form-kos textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #d0d5dd;
        border-radius: 8px;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
        font-size: 14px;
    }

    .form-kos input:focus,
    .form-kos select:focus,
    .form-kos textarea:focus {
        outline: none;
        border-color: #2468b4;
        box-shadow: 0 0 0 3px rgba(36,104,180,0.12);
    }

    .form-kos textarea {
        min-height: 90px;
        resize: vertical;
    }

    .form-kos button {
        margin-top: 25px;
        padding: 13px 20px;
        border: none;
        border-radius: 8px;
        background: #176b45;
        color: white;
        cursor: pointer;
        width: 100%;
        font-size: 15px;
        font-weight: bold;
        transition: 0.2s;
    }

    .form-kos button:hover {
        background: #105536;
    }

    .pesan {
        padding: 12px;
        margin-bottom: 15px;
        border-radius: 7px;
    }

    .sukses {
        background: #d9f5e4;
        color: #176b45;
    }

    .error {
        background: #ffe0e0;
        color: #a00000;
    }

    .petunjuk-foto {
        font-size: 13px;
        color: #667085;
        margin-top: 5px;
    }

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

        .form-kos {
            margin: 20px 15px;
            padding: 20px;
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
            <a href="logout.php" class="logout">Logout</a>
        </nav>

    </div>

</header>

<main class="form-kos">

    <h2>Tambah Data Kos</h2>

    <?php if ($pesan !== ""): ?>

        <div class="pesan <?= htmlspecialchars($status) ?>">
            <?= htmlspecialchars($pesan) ?>
        </div>

    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Nama Kos</label>
        <input type="text" name="nama_kos" required>

        <label>Jenis Kos</label>

        <select name="jenis_kos" required>
            <option value="">-- Pilih Jenis Kos --</option>
            <option value="Putra">Kos Putra</option>
            <option value="Putri">Kos Putri</option>
            <option value="Campur">Kos Campur</option>
        </select>

        <label>Alamat Kos</label>
        <textarea name="alamat" required></textarea>

        <label>Harga Sewa per Bulan (Rp)</label>
        <input type="number" name="harga" min="1" required>

        <label>Fasilitas</label>
        <textarea name="fasilitas" placeholder="Contoh: WiFi, kamar mandi dalam, kasur"></textarea>

        <label>Deskripsi Kos</label>
        <textarea name="deskripsi" placeholder="Ceritakan kondisi dan keunggulan kos"></textarea>

        <label>Foto Bagian Luar Kos</label>
        <input
            type="file"
            name="foto"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            required
        >
        <p class="petunjuk-foto">Unggah foto bangunan atau tampak depan kos. Maksimal 2 MB.</p>

        <label>Foto Bagian Dalam Kamar</label>
        <input
            type="file"
            name="foto_kamar"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            required
        >
        <p class="petunjuk-foto">Unggah foto kondisi bagian dalam kamar. Maksimal 2 MB.</p>

        <button type="submit">Simpan Data Kos</button>

    </form>

</main>

</body>
</html>
