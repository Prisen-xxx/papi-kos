
<?php

session_start();

require_once "config/koneksi.php";

// Keamanan: hanya pemilik kos yang boleh masuk
if (!isset($_SESSION["user_id"]) || $_SESSION["jenis_akun"] !== "pemilik") {
    header("Location: login.php");
    exit;
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    die("ID kos tidak valid.");
}

// Ambil data kos milik pemilik yang sedang login
$query = $koneksi->prepare("
    SELECT * FROM kos
    WHERE id = :id AND pemilik_id = :pemilik_id
");

$query->execute([
    ":id" => $id,
    ":pemilik_id" => $_SESSION["user_id"]
]);

$kos = $query->fetch(PDO::FETCH_ASSOC);

if (!$kos) {
    die("Data kos tidak ditemukan atau bukan milik akun Anda.");
}

$pesan = "";
$status = "";

// Fungsi upload foto baru
function uploadFotoBaru($nama_input, $awalan) {

    if (
        !isset($_FILES[$nama_input]) ||
        $_FILES[$nama_input]["error"] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    if ($_FILES[$nama_input]["error"] !== UPLOAD_ERR_OK) {
        throw new Exception("Foto gagal diunggah.");
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
        throw new Exception("Foto baru gagal disimpan.");
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
    $status_kos = $_POST["status"] ?? "";

    $foto_luar_lama = $kos["foto"];
    $foto_kamar_lama = $kos["foto_kamar"];

    $foto_luar_baru = null;
    $foto_kamar_baru = null;

    try {

        if (
            $nama_kos === "" ||
            $jenis_kos === "" ||
            $alamat === "" ||
            $harga === "" ||
            $status_kos === ""
        ) {
            throw new Exception("Semua kolom wajib harus diisi.");
        }

        if (!in_array($jenis_kos, ["Putra", "Putri", "Campur"], true)) {
            throw new Exception("Jenis kos tidak valid.");
        }

        if (!in_array($status_kos, ["Tersedia", "Penuh"], true)) {
            throw new Exception("Status kos tidak valid.");
        }

        if (!is_numeric($harga) || (float)$harga <= 0) {
            throw new Exception("Harga harus berupa angka lebih dari 0.");
        }

        // Upload foto luar jika diganti
        $hasil_foto_luar = uploadFotoBaru("foto", "luar_");

        if ($hasil_foto_luar !== null) {
            $foto_luar_baru = $hasil_foto_luar;
        }

        // Upload foto kamar jika diganti
        $hasil_foto_kamar = uploadFotoBaru("foto_kamar", "kamar_");

        if ($hasil_foto_kamar !== null) {
            $foto_kamar_baru = $hasil_foto_kamar;
        }

        // Tentukan foto yang akan disimpan
        $foto_luar = $foto_luar_baru ?? $foto_luar_lama;
        $foto_kamar = $foto_kamar_baru ?? $foto_kamar_lama;

        // Simpan perubahan ke database
        $update = $koneksi->prepare("
            UPDATE kos SET
                nama_kos = :nama_kos,
                jenis_kos = :jenis_kos,
                alamat = :alamat,
                harga = :harga,
                fasilitas = :fasilitas,
                deskripsi = :deskripsi,
                foto = :foto,
                foto_kamar = :foto_kamar,
                status = :status
            WHERE id = :id
            AND pemilik_id = :pemilik_id
        ");

        $update->execute([
            ":nama_kos" => $nama_kos,
            ":jenis_kos" => $jenis_kos,
            ":alamat" => $alamat,
            ":harga" => $harga,
            ":fasilitas" => $fasilitas,
            ":deskripsi" => $deskripsi,
            ":foto" => $foto_luar,
            ":foto_kamar" => $foto_kamar,
            ":status" => $status_kos,
            ":id" => $id,
            ":pemilik_id" => $_SESSION["user_id"]
        ]);

        // Hapus foto lama setelah database berhasil diperbarui
        if ($foto_luar_baru !== null && !empty($foto_luar_lama)) {
            $path_lama = "uploads/" . basename($foto_luar_lama);

            if (is_file($path_lama)) {
                unlink($path_lama);
            }
        }

        if ($foto_kamar_baru !== null && !empty($foto_kamar_lama)) {
            $path_lama = "uploads/" . basename($foto_kamar_lama);

            if (is_file($path_lama)) {
                unlink($path_lama);
            }
        }

        header("Location: kelola-kos.php?pesan=edit_berhasil");
        exit;

    } catch (Exception $e) {

        // Hapus file baru jika proses gagal
        if ($foto_luar_baru !== null && is_file("uploads/" . $foto_luar_baru)) {
            unlink("uploads/" . $foto_luar_baru);
        }

        if ($foto_kamar_baru !== null && is_file("uploads/" . $foto_kamar_baru)) {
            unlink("uploads/" . $foto_kamar_baru);
        }

        $pesan = $e->getMessage();
        $status = "error";

    } catch (PDOException $e) {

        $pesan = "Gagal memperbarui data kos.";
        $status = "error";

    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Kos - PAPI KOS</title>

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

    /* FORM EDIT */

    .form-kos {
        max-width: 700px;
        margin: 40px auto;
        padding: 28px;
        background: white;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }

    .form-kos h2 {
        color: #17365d;
        margin-bottom: 20px;
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

    /* GALERI FOTO */

    .galeri-foto {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-top: 10px;
    }

    .foto-item {
        background: #f4f7fb;
        padding: 12px;
        border-radius: 10px;
    }

    .foto-item img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 8px;
        display: block;
    }

    .foto-item p {
        font-weight: bold;
        margin-bottom: 8px;
        color: #344054;
    }

    .petunjuk {
        font-size: 13px;
        color: #667085;
        margin-top: 5px;
        line-height: 1.5;
    }

    .pesan {
        padding: 12px;
        margin-bottom: 15px;
        border-radius: 7px;
    }

    .error {
        background: #ffe0e0;
        color: #a00000;
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

        .galeri-foto {
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

            <a href="kelola-kos.php">Kelola Kos</a>

            <a href="logout.php" class="logout">Logout</a>
        </nav>

    </div>

</header>


<main class="form-kos">

    <h2>Edit Data Kos</h2>

    <?php if ($pesan !== ""): ?>

        <div class="pesan error">
            <?= htmlspecialchars($pesan) ?>
        </div>

    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Nama Kos</label>
        <input
            type="text"
            name="nama_kos"
            value="<?= htmlspecialchars($kos["nama_kos"]) ?>"
            required
        >

        <label>Jenis Kos</label>

        <select name="jenis_kos" required>
            <option value="Putra" <?= $kos["jenis_kos"] === "Putra" ? "selected" : "" ?>>Kos Putra</option>
            <option value="Putri" <?= $kos["jenis_kos"] === "Putri" ? "selected" : "" ?>>Kos Putri</option>
            <option value="Campur" <?= $kos["jenis_kos"] === "Campur" ? "selected" : "" ?>>Kos Campur</option>
        </select>

        <label>Alamat Kos</label>
        <textarea name="alamat" required><?= htmlspecialchars($kos["alamat"]) ?></textarea>

        <label>Harga Sewa per Bulan (Rp)</label>
        <input
            type="number"
            name="harga"
            min="1"
            value="<?= htmlspecialchars($kos["harga"]) ?>"
            required
        >

        <label>Fasilitas</label>
        <textarea name="fasilitas"><?= htmlspecialchars($kos["fasilitas"] ?? "") ?></textarea>

        <label>Deskripsi Kos</label>
        <textarea name="deskripsi"><?= htmlspecialchars($kos["deskripsi"] ?? "") ?></textarea>

        <label>Status Kos</label>

        <select name="status" required>
            <option value="Tersedia" <?= $kos["status"] === "Tersedia" ? "selected" : "" ?>>Tersedia</option>
            <option value="Penuh" <?= $kos["status"] === "Penuh" ? "selected" : "" ?>>Penuh</option>
        </select>


        <!-- FOTO SAAT INI -->

        <h3 style="margin-top:25px;">Foto Saat Ini</h3>

        <div class="galeri-foto">

            <div class="foto-item">

                <p>Foto Bagian Luar Kos</p>

                <?php if (!empty($kos["foto"])): ?>

                    <img
                        src="uploads/<?= rawurlencode(basename($kos["foto"])) ?>"
                        alt="Foto luar kos"
                    >

                <?php else: ?>

                    <p>Belum ada foto luar.</p>

                <?php endif; ?>

            </div>


            <div class="foto-item">

                <p>Foto Bagian Dalam Kamar</p>

                <?php if (!empty($kos["foto_kamar"])): ?>

                    <img
                        src="uploads/<?= rawurlencode(basename($kos["foto_kamar"])) ?>"
                        alt="Foto dalam kamar"
                    >

                <?php else: ?>

                    <p>Belum ada foto kamar.</p>

                <?php endif; ?>

            </div>

        </div>


        <!-- GANTI FOTO -->

        <label>Ganti Foto Bagian Luar Kos (Opsional)</label>

        <input
            type="file"
            name="foto"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
        >

        <p class="petunjuk">
            Kosongkan jika tidak ingin mengganti foto luar. Maksimal 2 MB.
        </p>


        <label>Ganti Foto Bagian Dalam Kamar (Opsional)</label>

        <input
            type="file"
            name="foto_kamar"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
        >

        <p class="petunjuk">
            Kosongkan jika tidak ingin mengganti foto kamar. Maksimal 2 MB.
        </p>


        <button type="submit">Simpan Perubahan</button>

    </form>

</main>

</body>
</html>
