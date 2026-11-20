import '../config/app_config.dart';
import '../models/ulasan.dart';
import 'api_service.dart';

/// CRUD Ulasan. Mode ditentukan terpusat oleh [AppConfig.useBackend].
/// Online: endpoint /ulasan (Sanctum). Offline: in-memory selama sesi.
class UlasanService {
  static const bool _offlineMode = !AppConfig.useBackend;

  static int _nextId = 1;
  static final List<Ulasan> _local = [];

  static Future<List<Ulasan>> fetchAll() async {
    if (_offlineMode) return List<Ulasan>.from(_local);
    final res = await ApiService.get('/ulasan', auth: true);
    final List list = res['data'];
    return list.map((e) => Ulasan.fromJson(Map<String, dynamic>.from(e))).toList();
  }

  static Future<Ulasan> create({
    required int orderId,
    required int rating,
    String komentar = '',
  }) async {
    if (_offlineMode) {
      final u = Ulasan(id: _nextId++, orderId: orderId, rating: rating, komentar: komentar);
      _local.add(u);
      return u;
    }
    final res = await ApiService.post('/ulasan', {
      'order_id': orderId,
      'rating': rating,
      'komentar': komentar,
    }, auth: true);
    return Ulasan.fromJson(Map<String, dynamic>.from(res['data']));
  }

  static Future<Ulasan> update(
    int id, {
    required int rating,
    String komentar = '',
  }) async {
    if (_offlineMode) {
      final idx = _local.indexWhere((u) => u.id == id);
      if (idx == -1) throw ApiException('Ulasan tidak ditemukan.');
      _local[idx] = Ulasan(id: id, orderId: _local[idx].orderId, rating: rating, komentar: komentar);
      return _local[idx];
    }
    final res = await ApiService.put('/ulasan/$id', {
      'rating': rating,
      'komentar': komentar,
    }, auth: true);
    return Ulasan.fromJson(Map<String, dynamic>.from(res['data']));
  }

  static Future<void> delete(int id) async {
    if (_offlineMode) {
      _local.removeWhere((u) => u.id == id);
      return;
    }
    await ApiService.delete('/ulasan/$id', auth: true);
  }
}
