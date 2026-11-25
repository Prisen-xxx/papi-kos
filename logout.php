
<?php

session_start();

// Menghapus semua data sesi pengguna
session_unset();

// Menghancurkan sesi
session_destroy();

// Mengarahkan pengguna kembali ke halaman login
header("Location: login.php");
exit;

?>
