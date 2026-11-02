import 'dart:async';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/app_config.dart';

/// Klien HTTP dasar: menangani header, token, dan parsing JSON.
class ApiService {
  static const String _tokenKey = 'auth_token';

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }

  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
  }

  static Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
  }

  static Future<Map<String, String>> _headers({bool auth = false}) async {
    final headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    if (auth) {
      final token = await getToken();
      if (token != null) headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  static Uri _uri(String path) => Uri.parse('${AppConfig.baseUrl}$path');

  /// Host server tanpa '/api', untuk memuat aset (mis. logo pembayaran).
  static String get host => AppConfig.baseUrl.replaceAll('/api', '');

  static Future<dynamic> get(String path, {bool auth = false}) async {
    return _safeCall(() async {
      final res = await http
          .get(_uri(path), headers: await _headers(auth: auth))
          .timeout(const Duration(seconds: AppConfig.timeoutSeconds));
      return _decode(res);
    });
  }

  static Future<dynamic> put(String path, Map body, {bool auth = false}) async {
    return _safeCall(() async {
      final res = await http
          .put(_uri(path), headers: await _headers(auth: auth), body: jsonEncode(body))
          .timeout(const Duration(seconds: AppConfig.timeoutSeconds));
      return _decode(res);
    });
  }

  static Future<dynamic> delete(String path, {bool auth = false}) async {
    return _safeCall(() async {
      final res = await http
          .delete(_uri(path), headers: await _headers(auth: auth))
          .timeout(const Duration(seconds: AppConfig.timeoutSeconds));
      return _decode(res);
    });
  }

  static Future<dynamic> post(String path, Map body, {bool auth = false}) async {
    return _safeCall(() async {
      final res = await http
          .post(_uri(path), headers: await _headers(auth: auth), body: jsonEncode(body))
          .timeout(const Duration(seconds: AppConfig.timeoutSeconds));
      return _decode(res);
    });
  }

  static Future<dynamic> _safeCall(Future<dynamic> Function() fn) async {
    try {
      return await fn();
    } on ApiException {
      rethrow;
    } on TimeoutException {
      throw ApiException('Server tidak merespons (timeout). Periksa koneksi.', statusCode: 0);
    } catch (_) {
      throw ApiException('Tidak dapat terhubung ke server.', statusCode: 0);
    }
  }

  static dynamic _decode(http.Response res) {
    final data = jsondecodeSafe(res.body);
    if (res.statusCode >= 200 && res.statusCode < 300) {
      return data;
    }

    // Laravel ValidationException (HTTP 422) mengembalikan field 'errors'
    // berisi rincian per-field, contoh: {"barber_id": ["wajib diisi"]}.
    // Tampilkan rincian itu jika ada, supaya error lebih jelas daripada
    // pesan generik "The barber id field is required.".
    if (data is Map && data['errors'] is Map) {
      final errors = data['errors'] as Map;
      final pesanPertama = errors.values.isNotEmpty && errors.values.first is List
          ? (errors.values.first as List).first.toString()
          : null;
      if (pesanPertama != null) {
        throw ApiException(pesanPertama, statusCode: res.statusCode);
      }
    }

    final msg = (data is Map && data['message'] != null)
        ? data['message'].toString()
        : 'Terjadi kesalahan (${res.statusCode})';
    throw ApiException(msg, statusCode: res.statusCode);
  }

  static dynamic jsondecodeSafe(String body) {
    try {
      return jsonDecode(body);
    } catch (_) {
      return null;
    }
  }
}

class ApiException implements Exception {
  final String message;
  final int statusCode;

  const ApiException(this.message, {this.statusCode = 0});

  /// true jika token tidak valid / kadaluarsa (perlu re-login)
  bool get isUnauthorized => statusCode == 401;

  @override
  String toString() => message;
}
