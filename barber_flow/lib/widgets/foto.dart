import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';
import '../theme.dart';

/// Menampilkan gambar dari URL (di-cache) dengan efek shimmer saat memuat
/// dan fallback ikon jika kosong / gagal dimuat.
class Foto extends StatelessWidget {
  final String? url;
  final double? width;
  final double height;
  final IconData fallbackIcon;

  const Foto({
    super.key,
    required this.url,
    this.width,
    this.height = 180,
    this.fallbackIcon = Icons.content_cut,
  });

  Widget _fallback() => Container(
        width: width ?? double.infinity,
        height: height,
        color: AppColors.dark,
        child: Icon(fallbackIcon, color: AppColors.primary, size: 48),
      );

  @override
  Widget build(BuildContext context) {
    // Tidak ada foto → tampilkan ikon fallback.
    if (url == null || url!.isEmpty) return _fallback();

    return CachedNetworkImage(
      imageUrl: url!,
      width: width ?? double.infinity,
      height: height,
      fit: BoxFit.cover,
      placeholder: (context, _) => Shimmer.fromColors(
        baseColor: Colors.grey.shade300,
        highlightColor: Colors.grey.shade100,
        child: Container(
          width: width ?? double.infinity,
          height: height,
          color: Colors.white,
        ),
      ),
      errorWidget: (context, _, _) => _fallback(),
    );
  }
}
