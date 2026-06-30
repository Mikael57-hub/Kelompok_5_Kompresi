<?php
$conn = new mysqli("localhost", "root", "", "db_arsip_digital");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = $conn->query("SELECT * FROM berkas_media WHERE id = $id");
    
    if ($query->num_rows > 0) {
        $berkas = $query->fetch_assoc();
        
        // Cek Logika Akses berdasarkan status metadata 
        if ($berkas['status_akses'] == 'read-only') {
            // Tampilan Proteksi Tautan Unduhan [cite: 92]
            echo "<body style='background:#0f172a; color:#f1f5f9; font-family:sans-serif; text-align:center; padding-top:100px;'>";
            echo "<div style='max-width:500px; margin:0 auto; background:#1e293b; padding:30px; border-radius:15px; border:1px solid #f59e0b;'>";
            echo "<h2 style='color:#f59e0b;'>⚠️ Akses Terbatas (Security Gateway)</h2>";
            echo "<p style='font-size:14px; color:#94a3b8;'>Maaf, Berkas berkode <b>".$berkas['nama_file']."</b> disetel ke status metadata <b>Read-Only</b> oleh pemilik sistem.</p>";
            echo "<p style='font-size:12px; color:#64748b;'>Anda diperkenankan melihat pratinjau, namun tidak diizinkan mendownload file master asli.</p>";
            echo "<hr style='border-color:#334155; margin:20px 0;'>";
            echo "<a href='index.php' style='color:#10b981; text-decoration:none; font-size:14px;'>Kembali ke Beranda</a>";
            echo "</div>";
            echo "</body>";
            exit();
        } else {
            // Jika full-access, izinkan pengguna mendownload file asli
            if (file_exists($berkas['path_file'])) {
                header('Content-Description: File Transfer');
                header('Content-Type: ' . $berkas['tipe_file']);
                header('Content-Disposition: attachment; filename="' . $berkas['nama_file'] . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($berkas['path_file']));
                readfile($berkas['path_file']);
                exit();
            }
        }
    }
}
echo "Berkas tidak ditemukan.";
?>