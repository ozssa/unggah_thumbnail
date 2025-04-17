<?php
// C:\xampp\htdocs\unggah_thumbnail\includes\koneksi.php
$host = "localhost";
$user = "root";
$pass = "";
$db = "akademik07018";

try {
    $conn = new mysqli($host, $user, $pass, $db);
    
    if ($conn->connect_error) {
        throw new Exception("Koneksi gagal: " . $conn->connect_error);
    }
    
    // Set charset untuk mencegah masalah encoding
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Log error ke file (opsional)
    error_log($e->getMessage(), 3, "error.log");
    
    // Tampilkan pesan error yang ramah pengguna
    die("Maaf, terjadi masalah koneksi ke database. Silakan coba lagi nanti.");
}
?>