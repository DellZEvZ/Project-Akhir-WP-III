import 'package:flutter/material.dart';
import '../theme.dart';
import '../services/api_service.dart';
import '../services/booking_service.dart';
import 'struk_page.dart';
import 'payment_page.dart';

/// Riwayat pesanan (booking & produk) — mengikuti riwayat akun website.
class RiwayatPage extends StatefulWidget {
  const RiwayatPage({super.key});

  @override
  State<RiwayatPage> createState() => _RiwayatPageState();
}

class _RiwayatPageState extends State<RiwayatPage> {
  late Future<List<Map<String, dynamic>>> _future;

  @override
  void initState() {
    super.initState();
    _future = BookingService.fetchOrders();
  }

  Future<void> _refresh() async {
    setState(() => _future = BookingService.fetchOrders());
    await _future;
  }

  String _rp(dynamic n) {
    final s = (n ?? 0).toString();
    final buf = StringBuffer();
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) buf.write('.');
      buf.write(s[i]);
    }
    return 'Rp $buf';
  }

  void _snack(String msg, {bool error = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), backgroundColor: error ? Colors.red : AppColors.dark),
    );
  }

  Future<void> _batalkan(Map<String, dynamic> o) async {
    final yakin = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Batalkan pesanan?'),
        content: Text('Pesanan #${o['id']} akan dibatalkan.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('TIDAK')),
          TextButton(onPressed: () => Navigator.pop(context, true), child: const Text('YA, BATALKAN')),
        ],
      ),
    );
    if (yakin != true) return;
    try {
      await BookingService.cancelBooking(o['id'] as int);
      if (!mounted) return;
      _snack('Pesanan dibatalkan.');
      _refresh();
    } on ApiException catch (e) {
      if (mounted) _snack(e.message, error: true);
    }
  }

  Future<void> _ubah(Map<String, dynamic> o) async {
    DateTime tanggal = DateTime.tryParse('${o['tanggal_booking']}') ?? DateTime.now();
    final parts = '${o['jam_booking'] ?? '10:00'}'.split(':');
    TimeOfDay jam = TimeOfDay(
      hour: int.tryParse(parts[0]) ?? 10,
      minute: parts.length > 1 ? int.tryParse(parts[1]) ?? 0 : 0,
    );
    final catatanCtrl = TextEditingController(text: '${o['catatan'] ?? ''}');

    final simpan = await showDialog<bool>(
      context: context,
      builder: (_) => StatefulBuilder(
        builder: (context, setLocal) => AlertDialog(
          title: Text('Ubah Booking #${o['id']}'),
          content: Column(mainAxisSize: MainAxisSize.min, children: [
            OutlinedButton.icon(
              icon: const Icon(Icons.calendar_today, size: 18),
              label: Text(tanggal.toIso8601String().substring(0, 10)),
              onPressed: () async {
                final p = await showDatePicker(
                  context: context,
                  initialDate: tanggal.isBefore(DateTime.now()) ? DateTime.now() : tanggal,
                  firstDate: DateTime.now(),
                  lastDate: DateTime.now().add(const Duration(days: 90)),
                );
                if (p != null) setLocal(() => tanggal = p);
              },
            ),
            OutlinedButton.icon(
              icon: const Icon(Icons.access_time, size: 18),
              label: Text(jam.format(context)),
              onPressed: () async {
                final p = await showTimePicker(context: context, initialTime: jam);
                if (p != null) setLocal(() => jam = p);
              },
            ),
            TextField(
              controller: catatanCtrl,
              decoration: const InputDecoration(labelText: 'Catatan'),
              maxLength: 500,
            ),
          ]),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('BATAL')),
            TextButton(onPressed: () => Navigator.pop(context, true), child: const Text('SIMPAN')),
          ],
        ),
      ),
    );
    if (simpan != true) return;
    try {
      await BookingService.updateBooking(
        o['id'] as int,
        tanggal: tanggal.toIso8601String().substring(0, 10),
        jam: '${jam.hour.toString().padLeft(2, '0')}:${jam.minute.toString().padLeft(2, '0')}',
        catatan: catatanCtrl.text.trim(),
      );
      if (!mounted) return;
      _snack('Booking diubah.');
      _refresh();
    } on ApiException catch (e) {
      if (mounted) _snack(e.message, error: true);
    }
  }

  Widget _badge(String text, Color color) => Container(
        margin: const EdgeInsets.only(right: 6, top: 4),
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
        decoration: BoxDecoration(color: color.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(20)),
        child: Text(text, style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.bold)),
      );

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Riwayat Pesanan')),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator(color: AppColors.primary));
          }
          if (snap.hasError) {
            return Center(child: Text('Gagal memuat: ${snap.error}'));
          }
          final orders = snap.data ?? [];
          if (orders.isEmpty) {
            return const Center(
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                Icon(Icons.receipt_long, size: 56, color: Colors.grey),
                SizedBox(height: 8),
                Text('Belum ada riwayat pesanan.', style: TextStyle(color: Colors.grey)),
              ]),
            );
          }
          return RefreshIndicator(
            onRefresh: _refresh,
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: orders.length,
              itemBuilder: (context, i) {
                final o = orders[i];
                final lunas = o['status_bayar'] == 'lunas';
                final belum = o['status_bayar'] == 'belum';
                final isProduk = o['jenis'] == 'produk';
                final items = (o['items'] as List?) ?? [];
                final namaPertama = items.isNotEmpty ? items.first['nama'].toString() : 'Pesanan';

                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  child: Padding(
                    padding: const EdgeInsets.all(14),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text('${isProduk ? 'Pesanan' : 'Booking'} #${o['id']}',
                                style: const TextStyle(fontWeight: FontWeight.bold)),
                            Text(_rp(o['total_akhir'] ?? o['total_harga']),
                                style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold)),
                          ],
                        ),
                        Wrap(children: [
                          _badge(o['status_label']?.toString() ?? '-', o['status'] == 'done' ? Colors.green : (o['status'] == 'batal' ? Colors.red : Colors.blueGrey)),
                          _badge(o['status_bayar_label']?.toString() ?? '-', lunas ? Colors.green : (belum ? Colors.red : Colors.orange)),
                        ]),
                        if (o['tanggal_booking'] != null)
                          Padding(
                            padding: const EdgeInsets.only(top: 6),
                            child: Text(
                                '📅 ${o['tanggal_booking']}   🕒 ${o['jam_booking'] ?? '-'} WIB'
                                '${o['barber'] != null ? '   ✂️ ${o['barber']['nama']}' : ''}',
                                style: const TextStyle(fontSize: 12, color: Colors.grey)),
                          ),
                        const SizedBox(height: 6),
                        ...items.map((it) => Text('• ${it['nama']} (${it['qty']}x)  ${_rp(it['harga'])}',
                            style: const TextStyle(fontSize: 13))),
                        if ((o['biaya_ongkir'] ?? 0) > 0)
                          Padding(
                            padding: const EdgeInsets.only(top: 2),
                            child: Text(
                                '• Ongkos Kirim (${o['kurir'] ?? ''} ${o['layanan_ongkir'] ?? ''})  ${_rp(o['biaya_ongkir'])}',
                                style: const TextStyle(fontSize: 13)),
                          ),
                        const SizedBox(height: 8),
                        Wrap(spacing: 8, runSpacing: 4, children: [
                          if (o['status'] == 'confirmed' && !lunas && !isProduk)
                            OutlinedButton.icon(
                              onPressed: () => _ubah(o),
                              icon: const Icon(Icons.edit_calendar, size: 18),
                              label: const Text('Ubah'),
                            ),
                          if ((o['status'] == 'confirmed' || o['status'] == 'pending') && !lunas)
                            OutlinedButton.icon(
                              onPressed: () => _batalkan(o),
                              icon: const Icon(Icons.cancel_outlined, size: 18, color: Colors.red),
                              label: const Text('Batalkan', style: TextStyle(color: Colors.red)),
                            ),
                          if (lunas)
                            OutlinedButton.icon(
                              onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => StrukPage(order: o))),
                              icon: const Icon(Icons.receipt, size: 18),
                              label: const Text('Lihat Struk'),
                            ),
                          if (belum && o['status'] != 'batal' && o['metode_bayar'] != 'cash')
                            ElevatedButton.icon(
                              onPressed: () => Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => PaymentPage(
                                    orderId: o['id'] as int,
                                    total: (() {
                                      final v = o['total_akhir'] ?? o['total_harga'] ?? 0;
                                      return v is int ? v : (v as num).toInt();
                                    })(),
                                    namaLayanan: namaPertama,
                                  ),
                                ),
                              ).then((_) => _refresh()),
                              icon: const Icon(Icons.credit_card, size: 18),
                              label: const Text('Bayar Sekarang'),
                            ),
                        ]),
                      ],
                    ),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
