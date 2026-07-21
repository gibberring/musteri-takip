import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:servis_takip_mobile/core/theme/app_theme.dart';
import 'package:servis_takip_mobile/features/auth/auth_provider.dart';

class ProfilScreen extends StatelessWidget {
  const ProfilScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    if (user == null) {
      return const Center(child: Text('Oturum bilgisi yok'));
    }
    final ad = user['ad'] ?? user['nick'] ?? '-';
    final pozAd = user['poz_ad'] ?? '-';
    final nick = user['nick'] ?? '-';
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 40,
                    backgroundColor: AppTheme.primary.withValues(alpha: 0.2),
                    child: Text(
                      ad.toString().substring(0, 1).toUpperCase(),
                      style: const TextStyle(fontSize: 32, color: AppTheme.primary),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(ad.toString(), style: Theme.of(context).textTheme.titleLarge),
                  const SizedBox(height: 4),
                  Text(pozAd.toString(), style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: AppTheme.textMuted)),
                  const SizedBox(height: 4),
                  Text('@$nick', style: Theme.of(context).textTheme.bodySmall?.copyWith(color: AppTheme.textMuted)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
