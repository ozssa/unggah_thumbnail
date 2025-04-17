<?php
// C:\xampp\htdocs\unggah_thumbnail\upload.php
include 'includes/koneksi.php';

// Validasi request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    header("HTTP/1.1 400 Bad Request");
    die("Permintaan tidak valid.");
}

// Validasi file
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$file = $_FILES['file'];

if (!in_array($file['type'], $allowed_types)) {
    die("Hanya file gambar (JPEG, PNG, GIF) yang diizinkan.");
}

if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
    die("Ukuran file terlalu besar. Maksimal 5MB.");
}

// Generate nama file unik untuk mencegah overwrite
$filename = uniqid() . '_' . basename($file['name']);
$filepath = 'assets/uploads/' . $filename;
$thumbpath = 'assets/uploads/thumb_' . $filename;

// Ukuran thumbnail
$width = 150;
$height = 150;

try {
    // Pindahkan file ke server
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception("Gagal memindahkan file.");
    }

    // Buat thumbnail
    list($original_width, $original_height, $type) = getimagesize($filepath);
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($filepath);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($filepath);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($filepath);
            break;
        default:
            throw new Exception("Format gambar tidak didukung.");
    }

    $image_p = imagecreatetruecolor($width, $height);
    
    // Preserve transparency untuk PNG/GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 0, 0, 0, 127));
        imagealphablending($image_p, false);
        imagesavealpha($image_p, true);
    }
    
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $original_width, $original_height);

    // Simpan thumbnail berdasarkan tipe asli
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($image_p, $thumbpath, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($image_p, $thumbpath, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($image_p, $thumbpath);
            break;
    }

    // Simpan ke database dengan prepared statement
    $stmt = $conn->prepare("INSERT INTO gambar_thumbnail 
                          (filename, filepath, thumbpath, width, height, original_width, original_height) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiiii", 
        $filename, 
        $filepath, 
        $thumbpath, 
        $width, 
        $height,
        $original_width,
        $original_height
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data ke database: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
    // Redirect ke galeri dengan pesan sukses
    header("Location: galeri_bootstrap3.php?success=1");
    exit();

} catch (Exception $e) {
    // Bersihkan file yang mungkin sudah terupload
    if (file_exists($filepath)) unlink($filepath);
    if (file_exists($thumbpath)) unlink($thumbpath);
    
    die("Error: " . $e->getMessage());
}
?>