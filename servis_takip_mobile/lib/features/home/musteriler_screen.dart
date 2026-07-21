import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:servis_takip_mobile/core/api/api_client.dart';
import 'package:servis_takip_mobile/core/theme/app_theme.dart';
import 'package:servis_takip_mobile/features/home/musteri_detay_screen.dart';

class MusterilerScreen extends StatefulWidget {
  const MusterilerScreen({super.key});

  @override
  State<MusterilerScreen> createState() => _MusterilerScreenState();
}

class _MusterilerScreenState extends State<MusterilerScreen> {
  List<dynamic> _list = [];
  bool _loading = true;
  String? _error;
  final _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _load({String? q}) async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final api = context.read<ApiClient>();
    final res = await api.getMusteriler(start: 0, length: 50, q: q);
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
            controller: _searchController,
            decoration: const InputDecoration(
              hintText: 'Ara...',
              prefixIcon: Icon(Icons.search),
            ),
            onSubmitted: (v) => _load(q: v.isEmpty ? null : v),
          ),
        ),
        Expanded(
          child: _loading
              ? const Center(child: CircularProgressIndicator())
              : _error != null
                  ? Center(child: Text(_error!, style: const TextStyle(color: AppTheme.danger)))
                  : ListView.builder(
                      itemCount: _list.length,
                      itemBuilder: (context, i) {
                        final m = _list[i] is Map ? _list[i] as Map : {};
                        final id = m['id'] is int ? m['id'] as int : 0;
                        final ad = m['ad'] ?? '-';
                        final tel = m['tel1'] ?? m['tel2'] ?? '';
                        return ListTile(
                          title: Text(ad.toString()),
                          subtitle: tel.isNotEmpty ? Text(tel.toString()) : null,
                          trailing: const Icon(Icons.chevron_right),
                          onTap: () {
                            if (id > 0) {
                              Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (_) => MusteriDetayScreen(musteriId: id),
                                ),
                              );
                            }
                          },
                        );
                      },
                    ),
        ),
      ],
    );
  }
}
