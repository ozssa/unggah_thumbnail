<?php include 'includes/koneksi.php'; ?>
<?php
// C:\xampp\htdocs\unggah_thumbnail\galeri_bootstrap3.php

// Pesan sukses dari upload
if (isset($_GET['success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            Gambar berhasil diupload!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}

// Filter tanggal
$tanggal = isset($_GET['tanggal']) ? $conn->real_escape_string($_GET['tanggal']) : null;

// Query dasar dengan prepared statement
$sql = "SELECT * FROM gambar_thumbnail";
$count_sql = "SELECT COUNT(*) FROM gambar_thumbnail";
$params = [];
$types = "";

if ($tanggal) {
    $sql .= " WHERE DATE(uploaded_at) = ?";
    $count_sql .= " WHERE DATE(uploaded_at) = ?";
    $params[] = $tanggal;
    $types .= "s";
}

$sql .= " ORDER BY uploaded_at DESC";

// Pagination
$limit = 6; // jumlah gambar per halaman (diubah menjadi 6 untuk layout yang lebih baik)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total gambar
$stmt_count = $conn->prepare($count_sql);
if ($tanggal) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_result = $stmt_count->get_result()->fetch_row()[0];
$total_pages = ceil($total_result / $limit);
$stmt_count->close();

// Ambil data dengan pagination
$sql .= " LIMIT ? OFFSET ?";
$types .= "ii";
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Gambar Responsive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .img-info {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Galeri Gambar</h2>
        <a href="index.php" class="btn btn-primary">
            <i class="fas fa-upload me-1"></i> Upload Baru
        </a>
    </div>
    
    <!-- Filter dan Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="tanggal" class="form-label">Filter Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal) ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-success me-2">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="galeri_bootstrap3.php" class="btn btn-outline-secondary">
                        <i class="fas fa-sync-alt me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($tanggal): ?>
        <div class="alert alert-info mb-4">
            Menampilkan gambar yang diunggah pada tanggal: <strong><?= htmlspecialchars($tanggal) ?></strong>
        </div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= htmlspecialchars($row['thumbpath']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($row['filename']) ?>"
                             loading="lazy">
                        <div class="card-body">
                            <div class="img-info mb-2">
                                <div><i class="fas fa-file-image me-1"></i> <?= htmlspecialchars($row['filename']) ?></div>
                                <div><i class="fas fa-expand me-1"></i> <?= $row['original_width'] ?>x<?= $row['original_height'] ?> px</div>
                                <div><i class="fas fa-calendar-alt me-1"></i> <?= date('d M Y H:i', strtotime($row['uploaded_at'])) ?></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="<?= htmlspecialchars($row['filepath']) ?>" 
                                   class="btn btn-sm btn-outline-primary" 
                                   target="_blank"
                                   data-bs-toggle="tooltip" 
                                   title="Lihat gambar asli">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="hapus.php" method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="filepath" value="<?= htmlspecialchars($row['filepath']) ?>">
                                    <input type="hidden" name="thumbpath" value="<?= htmlspecialchars($row['thumbpath']) ?>">
                                    <button type="submit" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Yakin ingin menghapus gambar ini?')"
                                            data-bs-toggle="tooltip" 
                                            title="Hapus gambar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-image fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Belum ada gambar diunggah</h4>
            <a href="index.php" class="btn btn-primary mt-3">
                <i class="fas fa-upload me-1"></i> Upload Gambar Pertama
            </a>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-5">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" 
                           href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                           aria-label="First">
                            <span aria-hidden="true">&laquo;&laquo;</span>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" 
                           href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                           aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php 
                // Tampilkan maksimal 5 halaman di sekitar halaman aktif
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                if ($start > 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                
                for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor;
                
                if ($end < $total_pages) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" 
                           href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                           aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" 
                           href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                           aria-label="Last">
                            <span aria-hidden="true">&raquo;&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Aktifkan tooltip
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>