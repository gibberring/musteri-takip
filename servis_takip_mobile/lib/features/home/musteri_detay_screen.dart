import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:servis_takip_mobile/core/api/api_client.dart';
import 'package:servis_takip_mobile/core/theme/app_theme.dart';

class MusteriDetayScreen extends StatefulWidget {
  const MusteriDetayScreen({super.key, required this.musteriId});

  final int musteriId;

  @override
  State<MusteriDetayScreen> createState() => _MusteriDetayScreenState();
}

class _MusteriDetayScreenState extends State<MusteriDetayScreen> {
  Map<String, dynamic>? _data;
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
    final res = await context.read<ApiClient>().getMusteriDetay(widget.musteriId);
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (res.isSuccess && res.data != null) {
        _data = res.data;
        _error = null;
      } else {
        _error = res.message ?? 'Yüklenemedi';
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Center(child: CircularProgressIndicator());
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
    final m = _data ?? {};
    final ad = m['ad'] ?? '-';
    final tel1 = m['tel1'] ?? '';
    final tel2 = m['tel2'] ?? '';
    final adres = m['adres'] ?? '';
    final il = m['ilce'] is Map ? (m['ilce'] as Map)['il'] is Map ? ((m['ilce'] as Map)['il'] as Map)['ad'] : null : null;
    final ilce = m['ilce'] is Map ? (m['ilce'] as Map)['ad'] : null;

    return Scaffold(
      appBar: AppBar(title: Text(ad.toString())),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Ad', style: Theme.of(context).textTheme.titleSmall?.copyWith(color: AppTheme.textMuted)),
                  const SizedBox(height: 4),
                  Text(ad.toString(), style: Theme.of(context).textTheme.titleMedium),
                  if (tel1.toString().isNotEmpty) ...[
                    const SizedBox(height: 12),
                    Text('Telefon', style: Theme.of(context).textTheme.titleSmall?.copyWith(color: AppTheme.textMuted)),
                    const SizedBox(height: 4),
                    Text(tel1.toString()),
                  ],
                  if (tel2.toString().isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(tel2.toString()),
                  ],
                  if (adres.toString().isNotEmpty) ...[
                    const SizedBox(height: 12),
                    Text('Adres', style: Theme.of(context).textTheme.titleSmall?.copyWith(color: AppTheme.textMuted)),
                    const SizedBox(height: 4),
                    Text(adres.toString()),
                  ],
                  if (il != null || ilce != null) ...[
                    const SizedBox(height: 12),
                    Text('İl / İlçe', style: Theme.of(context).textTheme.titleSmall?.copyWith(color: AppTheme.textMuted)),
                    const SizedBox(height: 4),
                    Text('${il ?? ''} / ${ilce ?? ''}'),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
