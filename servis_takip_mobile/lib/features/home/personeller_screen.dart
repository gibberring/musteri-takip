import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:servis_takip_mobile/core/api/api_client.dart';
import 'package:servis_takip_mobile/core/theme/app_theme.dart';

class PersonellerScreen extends StatefulWidget {
  const PersonellerScreen({super.key});

  @override
  State<PersonellerScreen> createState() => _PersonellerScreenState();
}

class _PersonellerScreenState extends State<PersonellerScreen> {
  List<dynamic> _list = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load({String? search}) async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final api = context.read<ApiClient>();
    final res = await api.getPersoneller(start: 0, length: 100, search: search);
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
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Padding(
          padding: const EdgeInsets.all(8),
          child: TextField(
            decoration: const InputDecoration(
              hintText: 'Personel ara...',
              prefixIcon: Icon(Icons.search),
            ),
            onSubmitted: (v) => _load(search: v.isEmpty ? null : v),
          ),
        ),
        Expanded(
          child: _loading
              ? const Center(child: CircularProgressIndicator())
              : _error != null
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(_error!, style: const TextStyle(color: AppTheme.danger)),
                          const SizedBox(height: 16),
                          TextButton(onPressed: () => _load(), child: const Text('Tekrar dene')),
                        ],
                      ),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      itemCount: _list.length,
                      itemBuilder: (context, i) {
                        final p = _list[i] is Map ? _list[i] as Map : {};
                        final ad = p['ad'] ?? '-';
                        final pozisyon = p['pozisyon'] ?? '-';
                        final aktif = p['aktif'] == 1;
                        final mesai = p['mesai_basladimi'] == 1;
                        return Card(
                          margin: const EdgeInsets.only(bottom: 8),
                          child: ListTile(
                            title: Text(ad.toString()),
                            subtitle: Row(
                              children: [
                                Text(pozisyon.toString()),
                                const SizedBox(width: 8),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: aktif ? AppTheme.success.withValues(alpha: 0.2) : AppTheme.danger.withValues(alpha: 0.2),
                                    borderRadius: BorderRadius.circular(4),
                                  ),
                                  child: Text(aktif ? 'Aktif' : 'Pasif', style: TextStyle(fontSize: 11, color: aktif ? AppTheme.success : AppTheme.danger)),
                                ),
                                const SizedBox(width: 4),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: mesai ? AppTheme.success.withValues(alpha: 0.2) : AppTheme.danger.withValues(alpha: 0.2),
                                    borderRadius: BorderRadius.circular(4),
                                  ),
                                  child: Text(mesai ? 'Mesaide' : 'Çalışmıyor', style: TextStyle(fontSize: 11, color: mesai ? AppTheme.success : AppTheme.danger)),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
        ),
      ],
    );
  }
}
