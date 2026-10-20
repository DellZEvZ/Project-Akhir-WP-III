import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:shimmer/shimmer.dart';
import '../theme.dart';
import '../services/catalog_service.dart';
import '../widgets/foto.dart';
import 'detail_layanan.dart';

/// Halaman Beranda — menampilkan paket unggulan dari API.
class BerandaPage extends StatefulWidget {
  const BerandaPage({super.key});

  @override
  State<BerandaPage> createState() => _BerandaPageState();
}

class _BerandaPageState extends State<BerandaPage> {
  late Future<List<Map<String, dynamic>>> _future;

  @override
  void initState() {
    super.initState();
    _future = CatalogService.fetchLayanan();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Image.asset('assets/barber_logo.png', height: 38),
        centerTitle: true,
        automaticallyImplyLeading: false,
      ),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const _BerandaSkeleton();
          }

          final semuaLayanan = snapshot.data ?? [];
          // Ambil top 6 sebagai "Paket Unggulan"
          final paketUnggulan = semuaLayanan.take(6).toList();

          if (paketUnggulan.isEmpty) {
            return const Center(child: Text('Belum ada paket unggulan'));
          }

          return ListView(
            children: [
              // Banner
              Stack(
                children: [
                  const Foto(
                    url: 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=900&q=80',
                    height: 180,
                  ),
                  Container(height: 180, color: Colors.black.withValues(alpha: 0.45)),
                  const Positioned(
                    left: 20,
                    bottom: 24,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Tampil Rapi & Bergaya',
                            style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
                        SizedBox(height: 4),
                        Text('Paket grooming terbaik untuk pria',
                            style: TextStyle(color: AppColors.grey, fontSize: 13)),
                      ],
                    ),
                  ),
                ],
              ),
              const Padding(
                padding: EdgeInsets.fromLTRB(16, 20, 16, 8),
                child: Text('Paket Unggulan',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              ),
              GridView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                padding: const EdgeInsets.all(12),
                itemCount: paketUnggulan.length,
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  crossAxisSpacing: 12,
                  mainAxisSpacing: 12,
                  mainAxisExtent: 230,
                ),
                itemBuilder: (context, index) {
                  final paket = paketUnggulan[index];
                  return _PaketCard(paket: paket)
                      .animate()
                      .fadeIn(delay: (80 * index).ms, duration: 400.ms)
                      .slideY(begin: 0.1, end: 0, curve: Curves.easeOut);
                },
              ),
            ],
          );
        },
      ),
    );
  }
}

/// Kerangka shimmer yang meniru susunan Beranda selama data dimuat.
class _BerandaSkeleton extends StatelessWidget {
  const _BerandaSkeleton();

  Widget _kotak({double? height, double radius = 10}) => Container(
        height: height,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(radius),
        ),
      );

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: Colors.grey.shade300,
      highlightColor: Colors.grey.shade100,
      child: ListView(
        physics: const NeverScrollableScrollPhysics(),
        children: [
          _kotak(height: 180, radius: 0),
          const SizedBox(height: 20),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: SizedBox(width: 140, child: _kotak(height: 20)),
          ),
          GridView.count(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            padding: const EdgeInsets.all(12),
            crossAxisCount: 2,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            childAspectRatio: 0.85,
            children: List.generate(4, (_) => _kotak()),
          ),
        ],
      ),
    );
  }
}

class _PaketCard extends StatelessWidget {
  final Map<String, dynamic> paket;
  const _PaketCard({required this.paket});

  @override
  Widget build(BuildContext context) {
    return Card(
      clipBehavior: Clip.antiAlias,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      elevation: 3,
      child: InkWell(
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => DetailLayanan(layanan: paket)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Foto(url: paket['gambar'], height: 110, fallbackIcon: Icons.spa),
            Padding(
              padding: const EdgeInsets.all(8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(paket['nama'],
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  const SizedBox(height: 2),
                  Text('Rp ${paket['harga']}',
                      style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 2),
                  Text('${paket['durasi']} menit',
                      style: const TextStyle(color: Colors.grey, fontSize: 11)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
