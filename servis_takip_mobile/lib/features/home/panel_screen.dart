import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:servis_takip_mobile/core/api/api_client.dart';
import 'package:servis_takip_mobile/core/theme/app_theme.dart';
import 'package:servis_takip_mobile/shared/app_card.dart';

class PanelScreen extends StatefulWidget {
  const PanelScreen({super.key});

  @override
  State<PanelScreen> createState() => _PanelScreenState();
}

class _PanelScreenState extends State<PanelScreen> {
  Map<String, dynamic>? _data;
  String? _error;
  bool _loading = true;

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
    final res = await api.getPanel();
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
    if (_data?['redirect_to'] != null) {
      return Center(child: Text(_data!['message'] ?? 'Yönlendiriliyorsunuz...'));
    }

    final bugun = _data?['bugun_servis_sayisi'] ?? 0;
    final dun = _data?['dun_servis_sayisi'] ?? 0;
    final toplam = _data?['toplam_servis_sayisi'] ?? 0;
    final iptal = _data?['iptal_servis_sayisi'] ?? 0;

    return RefreshIndicator(
      onRefresh: _load,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            AppCard(title: 'Bugün Kaydedilen Servis', value: bugun.toString(), icon: Icons.build),
            const SizedBox(height: 12),
            AppCard(title: 'Dün Kaydedilen Servis', value: dun.toString(), icon: Icons.history),
            const SizedBox(height: 12),
            AppCard(title: 'Toplam Servis', value: toplam.toString(), icon: Icons.assignment),
            const SizedBox(height: 12),
            AppCard(title: 'İptal Edilen', value: iptal.toString(), icon: Icons.cancel),
          ],
        ),
      ),
    );
  }
}
