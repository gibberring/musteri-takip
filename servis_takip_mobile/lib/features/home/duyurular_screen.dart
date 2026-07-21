import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:servis_takip_mobile/core/api/api_client.dart';
import 'package:servis_takip_mobile/core/theme/app_theme.dart';

class DuyurularScreen extends StatefulWidget {
  const DuyurularScreen({super.key});

  @override
  State<DuyurularScreen> createState() => _DuyurularScreenState();
}

class _DuyurularScreenState extends State<DuyurularScreen> {
  Map<String, dynamic>? _groups;
  int _unread = 0;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final api = context.read<ApiClient>();
    final res = await api.getDuyurular();
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (res.isSuccess && res.data != null) {
        _groups = res.data!['groups'] is Map ? Map<String, dynamic>.from(res.data!['groups'] as Map) : null;
        _unread = res.data!['unread'] is int ? res.data!['unread'] as int : 0;
      }
    });
  }

  Future<void> _markRead(int id) async {
    await context.read<ApiClient>().markDuyuruOkundu(id);
    _load();
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }
    final groups = _groups ?? {};
    final keys = groups.keys.cast<String>().toList();
    if (keys.isEmpty) {
      return const Center(child: Text('Duyuru yok'));
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          ...keys.expand((key) {
            final items = groups[key] is List ? groups[key] as List : <dynamic>[];
            if (items.isEmpty) return <Widget>[];
            return [
              Padding(
                padding: const EdgeInsets.only(top: 12, bottom: 4),
                child: Text(
                  key,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        color: AppTheme.textMuted,
                        fontWeight: FontWeight.w600,
                      ),
                ),
              ),
              ...items.map<Widget>((e) {
                final m = e is Map ? Map<String, dynamic>.from(e as Map) : <String, dynamic>{};
                final id = m['id'] is int ? m['id'] as int : 0;
                final baslik = m['baslik']?.toString() ?? '';
                final icerik = m['icerik']?.toString() ?? '';
                final okundu = m['okundu'] == true;
                final publishedAt = m['published_at']?.toString();
                return Card(
                  margin: const EdgeInsets.only(bottom: 8),
                  child: ListTile(
                    title: Text(
                      baslik,
                      style: TextStyle(
                        fontWeight: okundu ? FontWeight.normal : FontWeight.w600,
                        color: okundu ? AppTheme.textMuted : AppTheme.textPrimary,
                      ),
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (icerik.isNotEmpty) Text(icerik, maxLines: 3, overflow: TextOverflow.ellipsis),
                        if (publishedAt != null && publishedAt.isNotEmpty)
                          Text(publishedAt, style: Theme.of(context).textTheme.bodySmall?.copyWith(color: AppTheme.textMuted)),
                      ],
                    ),
                    trailing: TextButton(
                      onPressed: okundu ? null : () => _markRead(id),
                      child: Text(okundu ? 'Okundu' : 'Okundu işaretle'),
                    ),
                  ),
                );
              }),
            ];
          }),
        ],
      ),
    );
  }
}
