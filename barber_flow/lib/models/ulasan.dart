/// Ulasan layanan milik customer (entitas CRUD ketiga).
class Ulasan {
  final int id;
  final int orderId;
  final int rating; // 1-5
  final String komentar;

  const Ulasan({
    required this.id,
    required this.orderId,
    required this.rating,
    this.komentar = '',
  });

  factory Ulasan.fromJson(Map<String, dynamic> j) => Ulasan(
        id: (j['id'] as num).toInt(),
        orderId: (j['order_id'] as num).toInt(),
        rating: (j['rating'] as num).toInt(),
        komentar: (j['komentar'] ?? '').toString(),
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'order_id': orderId,
        'rating': rating,
        'komentar': komentar,
      };
}
