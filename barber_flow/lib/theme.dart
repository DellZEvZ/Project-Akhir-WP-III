import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// Palet warna utama aplikasi Barber Flow (Tema Modern Clean - Merah & Putih).
class AppColors {
  static const Color primary = Color(0xFFD32F2F); // Merah Aksen (dari tombol Lihat Katalog)
  static const Color dark = Color(0xFF212121);    // Hitam/Abu-abu gelap untuk teks & header
  static const Color grey = Color(0xFF757575);    // Abu-abu untuk subtitle
  static const Color bg = Color(0xFFF8F9FA);      // Background putih bersih/light grey
  static const Color surface = Colors.white;      // Warna kartu/surface
}

/// Tema global aplikasi.
ThemeData buildAppTheme() {
  return ThemeData(
    useMaterial3: true,
    scaffoldBackgroundColor: AppColors.bg,
    textTheme: GoogleFonts.poppinsTextTheme(),
    colorScheme: ColorScheme.fromSeed(
      seedColor: AppColors.primary,
      primary: AppColors.primary,
      surface: AppColors.surface,
    ),
    appBarTheme: const AppBarTheme(
      backgroundColor: Colors.white,
      foregroundColor: AppColors.dark,
      centerTitle: true,
      elevation: 0,
      iconTheme: IconThemeData(color: AppColors.dark),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        textStyle: const TextStyle(fontWeight: FontWeight.bold),
        padding: const EdgeInsets.symmetric(vertical: 14),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    ),
  );
}
