import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../theme.dart';
import 'foto.dart';

/// Kartu katalog reusable untuk menampilkan item layanan/produk/paket
/// dalam GridView. Menerima parameter final (best practice StatelessWidget).
class KatalogCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final IconData icon;
  final String? subtitle; // mis. "30 menit" atau kategori
  final VoidCallback onTap;

  const KatalogCard({
    super.key,
    required this.item,
    required this.onTap,
    this.icon = Icons.content_cut,
    this.subtitle,
  });

  String _rupiah(int n) {
    final s = n.toString();
    final buf = StringBuffer();
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) buf.write('.');
      buf.write(s[i]);
    }
    return 'Rp $buf';
  }

  @override
  Widget build(BuildContext context) {
    return _kartu().animate().fadeIn(duration: 350.ms).slideY(begin: 0.08, end: 0);
  }

  Widget _kartu() {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: Card(
        clipBehavior: Clip.antiAlias,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        elevation: 3,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Foto(url: item['gambar'], height: 110, fallbackIcon: icon),
            Padding(
              padding: const EdgeInsets.all(8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item['nama'],
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    _rupiah(item['harga']),
                    style: const TextStyle(
                      color: AppColors.primary,
                      fontWeight: FontWeight.bold,
                      fontSize: 13,
                    ),
                  ),
                  if (subtitle != null) ...[
                    const SizedBox(height: 2),
                    Text(
                      subtitle!,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(color: Colors.grey, fontSize: 11),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
