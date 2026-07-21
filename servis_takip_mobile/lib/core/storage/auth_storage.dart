import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class AuthStorage {
  static const _keyToken = 'auth_token';
  static const _keyNick = 'auth_nick';
  static const _keyPassword = 'auth_password'; // Sadece 2FA ikinci istek için geçici

  final FlutterSecureStorage _storage = const FlutterSecureStorage(aOptions: AndroidOptions(encryptedSharedPreferences: true));

  Future<void> saveToken(String token) async {
    await _storage.write(key: _keyToken, value: token);
  }

  Future<String?> getToken() async {
    return _storage.read(key: _keyToken);
  }

  Future<void> clearToken() async {
    await _storage.delete(key: _keyToken);
  }

  Future<void> saveCredentialsFor2fa(String nick, String password) async {
    await _storage.write(key: _keyNick, value: nick);
    await _storage.write(key: _keyPassword, value: password);
  }

  Future<Map<String, String>?> getCredentialsFor2fa() async {
    final nick = await _storage.read(key: _keyNick);
    final password = await _storage.read(key: _keyPassword);
    if (nick == null || password == null) return null;
    return {'nick': nick, 'password': password};
  }

  Future<void> clearCredentialsFor2fa() async {
    await _storage.delete(key: _keyNick);
    await _storage.delete(key: _keyPassword);
  }

  Future<void> clearAll() async {
    await clearToken();
    await clearCredentialsFor2fa();
  }
}
