import 'dart:io';
import 'dart:typed_data';
import 'package:image/image.dart' as img; 

class ImageCompressor {
  
  /// [fileAsal] adalah file mentah fotografer, [kualitas] rentang 1-100
  static Future<File?> kompresGambarHibrida(File fileAsal, int kualitas) async {
    try {
      // 1. Baca berkas gambar dari penyimpanan lokal menjadi bytes
      Uint8List bytesAsal = await fileAsal.readAsBytes();

      // 2. Decode bytes menjadi objek Gambar (Image) internal sistem
      img.Image? gambarDecoded = img.decodeImage(bytesAsal);
      if (gambarDecoded == null) return null;

  
      List<int> bytesTersimpan = img.encodeJpg(gambarDecoded, quality: kualitas);

      String pathBaru = fileAsal.path.replaceAll('.jpg', '_compressed.jpg');
      File fileHasil = File(pathBaru);
      await fileHasil.writeAsBytes(bytesTersimpan);

      return fileHasil;
    } catch (e) {
      print("Gagal melakukan kompresi berkas: $e");
      return null;
    }
  }
}