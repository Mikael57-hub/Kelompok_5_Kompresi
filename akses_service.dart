import 'dart:convert';
import 'http' as http; // Package untuk request API global

class AksesService {
  // Ganti IP sesuai dengan server lokal/hosting 
  static const String urlApi = "http://localhost/arsip/cek_akses.php";

  /// Memeriksa hak akses berkas digital berdasarkan ID File
  static Future<void> periksaAksesBerkas(int idFile) async {
    try {
      // 1. Kirim request GET ke backend PHP membawa parameter ID berkas
      final respon = await http.get(Uri.parse("$urlApi?id=$idFile"));

      if (respon.statusCode == 200) {
        // 2. Parsing data JSON dari server
        final Map<String, dynamic> data = json.decode(respon.body);

        // 3. Logika penentuan hak akses pada antarmuka (UI) aplikasi
        if (data['status'] == 'success' && data['akses'] == 'full-access') {
          print("Akses Diterima: ${data['pesan']}");
          print("Link Download File Asli: ${data['download_link']}");
          // Di sini lo bisa aktifkan tombol download di aplikasi mobile
        } else {
          print("Akses Ditolak: ${data['pesan']}");
          // Di sini tombol download otomatis disembunyikan/di-disable
        }
      } else {
        print("Koneksi ke server berkas bermasalah.");
      }
    } catch (e) {
      print("Terjadi kesalahan sistem: $e");
    }
  }
}