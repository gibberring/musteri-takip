import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:servis_takip_mobile/core/api/api_client.dart';
import 'package:servis_takip_mobile/core/theme/app_theme.dart';
import 'package:servis_takip_mobile/features/home/servis_detay_screen.dart';

class ServislerScreen extends StatefulWidget {
  const ServislerScreen({super.key});

  @override
  State<ServislerScreen> createState() => _ServislerScreenState();
}

class _ServislerScreenState extends State<ServislerScreen> {
  List<dynamic> _list = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final api = context.read<ApiClient>();
    final res = await api.getServisler(start: 0, length: 50);
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (res.isSuccess && res.data != null) {
        final data = res.data!['data'];
        _list = data is List ? List<dynamic>.from(data) : [];
        _error = null;
      } else {
        _error = res.message ?? 'Liste yüklenemedi';
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(_error!, style: const TextStyle(color: AppTheme.danger)),
            const SizedBox(height: 16),
            TextButton(onPressed: _load, child: const Text('Tekrar dene')),
          ],
        ),
      );
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.builder(
        itemCount: _list.length,
        itemBuilder: (context, i) {
          final s = _list[i] is Map ? _list[i] as Map : {};
          final id = s['id'] is int ? s['id'] as int : 0;
          final musteri = s['musteri'] is Map ? s['musteri'] as Map : {};
          final durum = s['servis_durum'] is Map ? s['servis_durum'] as Map : {};
          final ad = musteri['ad'] ?? '-';
          final durumAd = durum['ad'] ?? '-';
          return ListTile(
            title: Text(ad.toString()),
            subtitle: Text('Durum: $durumAd'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () {
              if (id > 0) {
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (_) => ServisDetayScreen(servisId: id),
                  ),
                );
              }
            },
          );
        },
      ),
    );
  }
}
