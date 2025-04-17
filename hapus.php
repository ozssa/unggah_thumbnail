<?php
// C:\xampp\htdocs\unggah_thumbnail\hapus.php
include 'includes/koneksi.php';

// Validasi request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header("HTTP/1.1 400 Bad Request");
    die("Permintaan tidak valid.");
}

$id = (int)$_POST['id'];
$filepath = $_POST['filepath'] ?? '';
$thumbpath = $_POST['thumbpath'] ?? '';

try {
    // Mulai transaksi
    $conn->begin_transaction();
    
    // Hapus dari database dulu
    $stmt = $conn->prepare("DELETE FROM gambar_thumbnail WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal menghapus data dari database.");
    }
    
    // Hapus file fisik jika ada
    if (!empty($filepath)) {
        if (file_exists($filepath)) {
            if (!unlink($filepath)) {
                throw new Exception("Gagal menghapus file asli.");
            }
        }
    }
    
    if (!empty($thumbpath)) {
        if (file_exists($thumbpath)) {
            if (!unlink($thumbpath)) {
                throw new Exception("Gagal menghapus thumbnail.");
            }
        }
    }
    
    // Commit transaksi
    $conn->commit();
    
    // Redirect dengan pesan sukses
    header("Location: galeri_bootstrap3.php?deleted=1");
    exit();
    
} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();
    
    // Log error
    error_log($e->getMessage(), 3, "error.log");
    
    // Redirect dengan pesan error
    header("Location: galeri_bootstrap3.php?error=1");
    exit();
} finally {
    $stmt->close();
    $conn->close();
}
?>