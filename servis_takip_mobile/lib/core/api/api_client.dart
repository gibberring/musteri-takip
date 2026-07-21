import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:servis_takip_mobile/core/storage/auth_storage.dart';

/// Backend API base URL - uygulama başında veya .env ile ayarlanmalı
const String kBaseUrl = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: 'https://your-domain.com',
);

class ApiClient {
  ApiClient({AuthStorage? storage}) : _storage = storage ?? AuthStorage();

  final AuthStorage _storage;
  String get baseUrl => _baseUrlOverride ?? kBaseUrl;
  String? _baseUrlOverride;
  void setBaseUrl(String url) => _baseUrlOverride = url;

  Future<Map<String, String>> _headers({bool withAuth = true}) async {
    final headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    if (withAuth) {
      final token = await _storage.getToken();
      if (token != null && token.isNotEmpty) {
        headers['Authorization'] = 'Bearer $token';
      }
    }
    return headers;
  }

  Future<http.Response> get(String path, {bool withAuth = true}) async {
    final uri = Uri.parse('$baseUrl/api$path');
    return http.get(uri, headers: await _headers(withAuth: withAuth));
  }

  Future<http.Response> post(String path, {Map<String, dynamic>? body, bool withAuth = true}) async {
    final uri = Uri.parse('$baseUrl/api$path');
    return http.post(
      uri,
      headers: await _headers(withAuth: withAuth),
      body: body != null ? jsonEncode(body) : null,
    );
  }

  Future<http.Response> put(String path, {Map<String, dynamic>? body, bool withAuth = true}) async {
    final uri = Uri.parse('$baseUrl/api$path');
    return http.put(
      uri,
      headers: await _headers(withAuth: withAuth),
      body: body != null ? jsonEncode(body) : null,
    );
  }

  Future<http.Response> delete(String path, {bool withAuth = true}) async {
    final uri = Uri.parse('$baseUrl/api$path');
    return http.delete(uri, headers: await _headers(withAuth: withAuth));
  }

  Future<ApiResponse<Map<String, dynamic>>> login(String nick, String password) async {
    final res = await post('/login', body: {'nick': nick, 'password': password}, withAuth: false);
    return _parseMapResponse(res, successStatus: 200);
  }

  Future<ApiResponse<Map<String, dynamic>>> login2fa(String nick, String password, String code) async {
    final res = await post('/login/2fa', body: {'nick': nick, 'password': password, 'code': code}, withAuth: false);
    return _parseMapResponse(res, successStatus: 200);
  }

  Future<ApiResponse<void>> logout() async {
    final res = await post('/logout');
    if (res.statusCode == 200) return ApiResponse.success(null);
    return ApiResponse.fail(res.statusCode, _tryParseMessage(res));
  }

  Future<ApiResponse<Map<String, dynamic>>> getUser() async {
    final res = await get('/user');
    return _parseMapResponse(res);
  }

  Future<ApiResponse<Map<String, dynamic>>> getPanel() async {
    final res = await get('/panel');
    return _parseMapResponse(res);
  }

  Future<ApiResponse<Map<String, dynamic>>> getDuyurular() async {
    final res = await get('/duyurular/son');
    return _parseMapResponse(res);
  }

  Future<ApiResponse<void>> markDuyuruOkundu(int id) async {
    final res = await post('/duyurular/$id/okundu');
    if (res.statusCode == 200) return ApiResponse.success(null);
    return ApiResponse.fail(res.statusCode, _tryParseMessage(res));
  }

  Future<ApiResponse<Map<String, dynamic>>> getMusteriler({int start = 0, int length = 20, String? q}) async {
    var path = '/musteriler?start=$start&length=$length';
    if (q != null && q.isNotEmpty) path += '&q=${Uri.encodeComponent(q)}';
    final res = await get(path);
    return _parseMapResponse(res);
  }

  Future<ApiResponse<Map<String, dynamic>>> getMusteriDetay(int id) async {
    final res = await get('/musteriler/$id/detay');
    return _parseMapResponse(res);
  }

  Future<ApiResponse<Map<String, dynamic>>> getServisler({int start = 0, int length = 20}) async {
    final res = await get('/servisler?start=$start&length=$length');
    return _parseMapResponse(res);
  }

  Future<ApiResponse<Map<String, dynamic>>> getServislerBekleyen({int start = 0, int length = 50}) async {
    final res = await get('/servisler/bekleyen-kayitlar?start=$start&length=$length');
    return _parseMapResponse(res);
  }

  Future<ApiResponse<Map<String, dynamic>>> getServisDetay(int servisId) async {
    final res = await get('/servisler/$servisId/detay');
    return _parseMapResponse(res);
  }

  Future<ApiResponse<Map<String, dynamic>>> updateServisDurum(int servisId, Map<String, dynamic> body) async {
    final res = await put('/servisler/$servisId/durum-guncelle-detayli', body: body);
    return _parseMapResponse(res);
  }

  Future<ApiResponse<List<dynamic>>> getIller() async {
    final res = await get('/iller');
    final list = _tryParseJson(res.body);
    if (list is List) return ApiResponse.success(List<dynamic>.from(list));
    return ApiResponse.fail(res.statusCode ?? 500, _tryParseMessage(res));
  }

  Future<ApiResponse<List<dynamic>>> getIlceler(int ilId) async {
    final res = await get('/ilceler/$ilId');
    final list = _tryParseJson(res.body);
    if (list is List) return ApiResponse.success(List<dynamic>.from(list));
    return ApiResponse.fail(res.statusCode ?? 500, _tryParseMessage(res));
  }

  Future<ApiResponse<Map<String, dynamic>>> getPersoneller({int start = 0, int length = 50, String? search}) async {
    var path = '/personeller?start=$start&length=$length';
    if (search != null && search.isNotEmpty) path += '&search%5Bvalue%5D=${Uri.encodeComponent(search)}';
    final res = await get(path);
    return _parseMapResponse(res);
  }

  Future<ApiResponse<Map<String, dynamic>>> getPersonelDetay(int personelId) async {
    final res = await get('/personeller/$personelId/get-detay');
    return _parseMapResponse(res);
  }

  ApiResponse<Map<String, dynamic>> _parseMapResponse(http.Response res, {int? successStatus}) {
    final status = successStatus ?? 200;
    final body = _tryParseJson(res.body);
    final message = body is Map ? (body['message'] as String?) : null;
    if (res.statusCode == status && body is Map) {
      return ApiResponse.success(Map<String, dynamic>.from(body as Map), statusCode: res.statusCode);
    }
    return ApiResponse.fail(res.statusCode, message ?? _tryParseMessage(res));
  }

  String? _tryParseMessage(http.Response res) {
    final b = _tryParseJson(res.body);
    if (b is Map && b['message'] != null) return b['message'] as String?;
    return res.body.isNotEmpty ? res.body : null;
  }

  dynamic _tryParseJson(String s) {
    try {
      return jsonDecode(s);
    } catch (_) {
      return null;
    }
  }
}

class ApiResponse<T> {
  ApiResponse._({this.data, this.statusCode, this.message, required this.isSuccess});

  factory ApiResponse.success(T? data, {int? statusCode}) =>
      ApiResponse._(data: data, statusCode: statusCode ?? 200, isSuccess: true);
  factory ApiResponse.fail(int statusCode, [String? message]) =>
      ApiResponse._(statusCode: statusCode, message: message, isSuccess: false);

  final T? data;
  final int? statusCode;
  final String? message;
  final bool isSuccess;
}
