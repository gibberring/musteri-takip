import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:servis_takip_mobile/core/api/api_client.dart';
import 'package:servis_takip_mobile/core/theme/app_theme.dart';

class ServisDetayScreen extends StatefulWidget {
  const ServisDetayScreen({super.key, required this.servisId});

  final int servisId;

  @override
  State<ServisDetayScreen> createState() => _ServisDetayScreenState();
}

class _ServisDetayScreenState extends State<ServisDetayScreen> {
  Map<String, dynamic>? _data;
  bool _loading = true;
  String? _error;
  int? _selectedDurumId;
  bool _saving = false;

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
    final res = await context.read<ApiClient>().getServisDetay(widget.servisId);
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (res.isSuccess && res.data != null) {
        _data = res.data;
        _selectedDurumId = res.data!['mevcutDurumId'] is int ? res.data!['mevcutDurumId'] as int : null;
        _error = null;
      } else {
        _error = res.message ?? 'Yüklenemedi';
      }
    });
  }

  Future<void> _saveDurum() async {
    if (_selectedDurumId == null) return;
    final mevcut = _data?['mevcutDurumId'];
    if (mevcut == _selectedDurumId) return;
    setState(() => _saving = true);
    final res = await context.read<ApiClient>().updateServisDurum(widget.servisId, {
      'servis_durum_id': _selectedDurumId,
      'dinamik_veriler': {},
    });
    if (!mounted) return;
    setState(() => _saving = false);
    if (res.isSuccess) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Durum güncellendi')));
      _load();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res.message ?? 'Güncellenemedi')));
    }
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
    final d = _data ?? {};
    final musteri = d['musteri'] is Map ? d['musteri'] as Map : {};
    final musteriAd = musteri['ad'] ?? '-';
    final musteriTel = musteri['tel1'] ?? '';
    final musteriAdres = musteri['adres'] ?? '';
    final mevcutDurumAd = d['mevcutDurumAdi'] ?? '-';
    final tumDurumlar = d['tumDurumlar'] is List ? d['tumDurumlar'] as List : [];
    final marka = d['marka'] is Map ? d['marka'] as Map : {};
    final cihazTuru = d['cihazTuru'] is Map ? d['cihazTuru'] as Map : {};
    final cihazArizasi = d['cihaz_arizasi'] ?? '';

    return Scaffold(
      appBar: AppBar(title: Text('Servis #${widget.servisId}')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Müşteri', style: Theme.of(context).textTheme.titleSmall?.copyWith(color: AppTheme.textMuted)),
                  const SizedBox(height: 4),
                  Text(musteriAd.toString(), style: Theme.of(context).textTheme.titleMedium),
                  if (musteriTel.toString().isNotEmpty) Text(musteriTel.toString()),
                  if (musteriAdres.toString().isNotEmpty) Text(musteriAdres.toString()),
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Cihaz', style: Theme.of(context).textTheme.titleSmall?.copyWith(color: AppTheme.textMuted)),
                  const SizedBox(height: 4),
                  Text('${marka['ad'] ?? ''} - ${cihazTuru['ad'] ?? ''}'),
                  if (cihazArizasi.toString().isNotEmpty) ...[
                    const SizedBox(height: 8),
                    Text(cihazArizasi.toString()),
                  ],
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Durum', style: Theme.of(context).textTheme.titleSmall?.copyWith(color: AppTheme.textMuted)),
                  const SizedBox(height: 8),
                  Text('Mevcut: $mevcutDurumAd'),
                  const SizedBox(height: 12),
                  if (tumDurumlar.isNotEmpty) ...[
                    DropdownButton<int>(
                      value: _selectedDurumId,
                      isExpanded: true,
                      hint: const Text('Yeni durum seçin'),
                      items: tumDurumlar.map<DropdownMenuItem<int>>((e) {
                        final item = e is Map ? e as Map : {};
                        final id = item['id'] is int ? item['id'] as int : 0;
                        final ad = item['ad']?.toString() ?? '';
                        return DropdownMenuItem<int>(value: id, child: Text(ad));
                      }).toList(),
                      onChanged: (v) => setState(() => _selectedDurumId = v),
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _saving ? null : _saveDurum,
                        child: _saving ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2)) : const Text('Durumu Güncelle'),
                      ),
                    ),
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
