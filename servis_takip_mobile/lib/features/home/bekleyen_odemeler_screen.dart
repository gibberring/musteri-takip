import 'package:flutter/material.dart';
import 'package:servis_takip_mobile/core/theme/app_theme.dart';

class BekleyenOdemeScreen extends StatelessWidget {
  const BekleyenOdemeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.pending_actions, size: 64, color: AppTheme.primary.withValues(alpha: 0.6)),
            const SizedBox(height: 16),
            Text(
              'Bekleyen Ödemeler',
              style: Theme.of(context).textTheme.titleLarge,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'Bu sayfa web ile aynı verileri gösterecek şekilde geliştirilecek.',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: AppTheme.textMuted),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
