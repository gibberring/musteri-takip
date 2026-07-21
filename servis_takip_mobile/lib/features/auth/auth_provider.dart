import 'package:flutter/foundation.dart';
import 'package:servis_takip_mobile/core/api/api_client.dart';
import 'package:servis_takip_mobile/core/storage/auth_storage.dart';

class AuthProvider with ChangeNotifier {
  AuthProvider({ApiClient? api, AuthStorage? storage})
      : _api = api ?? ApiClient(),
        _storage = storage ?? AuthStorage();

  final ApiClient _api;
  final AuthStorage _storage;

  Map<String, dynamic>? _user;
  bool _isLoading = false;
  String? _error;

  Map<String, dynamic>? get user => _user;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isLoggedIn => _user != null;

  int? get pozId => _user != null ? _user!['poz_id'] as int? : null;
  String? get userName => _user != null ? (_user!['ad'] ?? _user!['nick']) as String? : null;

  Future<bool> login(String nick, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    final res = await _api.login(nick, password);
    _isLoading = false;

    if (res.statusCode == 423 && res.data?['two_factor_required'] == true) {
      await _storage.saveCredentialsFor2fa(nick, password);
      _error = res.message ?? 'İki adımlı doğrulama gerekli.';
      notifyListeners();
      return false; // Caller should show 2FA screen
    }

    if (!res.isSuccess || res.data == null) {
      _error = res.message ?? 'Giriş başarısız.';
      notifyListeners();
      return false;
    }

    final token = res.data!['token'] as String?;
    if (token != null) await _storage.saveToken(token);
    _user = res.data!['user'] is Map ? Map<String, dynamic>.from(res.data!['user'] as Map) : null;
    _error = null;
    notifyListeners();
    return true;
  }

  Future<bool> login2fa(String code) async {
    final creds = await _storage.getCredentialsFor2fa();
    if (creds == null) {
      _error = 'Oturum bilgisi bulunamadı. Tekrar giriş yapın.';
      notifyListeners();
      return false;
    }

    _isLoading = true;
    _error = null;
    notifyListeners();

    final res = await _api.login2fa(creds['nick']!, creds['password']!, code);
    _isLoading = false;
    await _storage.clearCredentialsFor2fa();

    if (!res.isSuccess || res.data == null) {
      _error = res.message ?? 'Doğrulama başarısız.';
      notifyListeners();
      return false;
    }

    final token = res.data!['token'] as String?;
    if (token != null) await _storage.saveToken(token);
    _user = res.data!['user'] is Map ? Map<String, dynamic>.from(res.data!['user'] as Map) : null;
    _error = null;
    notifyListeners();
    return true;
  }

  Future<void> logout() async {
    await _api.logout();
    await _storage.clearAll();
    _user = null;
    _error = null;
    notifyListeners();
  }

  Future<bool> loadUser() async {
    final token = await _storage.getToken();
    if (token == null || token.isEmpty) {
      _user = null;
      notifyListeners();
      return false;
    }

    final res = await _api.getUser();
    if (!res.isSuccess || res.data == null) {
      await _storage.clearAll();
      _user = null;
      notifyListeners();
      return false;
    }

    final userData = res.data!['user'];
    _user = userData is Map ? Map<String, dynamic>.from(userData as Map) : null;
    notifyListeners();
    return true;
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
