import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:servis_takip_mobile/core/api/api_client.dart';
import 'package:servis_takip_mobile/core/theme/app_theme.dart';
import 'package:servis_takip_mobile/features/auth/auth_provider.dart';
import 'package:servis_takip_mobile/features/home/panel_screen.dart';
import 'package:servis_takip_mobile/features/home/musteriler_screen.dart';
import 'package:servis_takip_mobile/features/home/servisler_screen.dart';
import 'package:servis_takip_mobile/features/home/duyurular_screen.dart';
import 'package:servis_takip_mobile/features/home/bekleyen_kayitlar_screen.dart';
import 'package:servis_takip_mobile/features/home/personeller_screen.dart';
import 'package:servis_takip_mobile/features/home/kasa_screen.dart';
import 'package:servis_takip_mobile/features/home/bekleyen_odemeler_screen.dart';
import 'package:servis_takip_mobile/features/home/genel_kasa_screen.dart';
import 'package:servis_takip_mobile/features/home/ayarlar_screen.dart';
import 'package:servis_takip_mobile/features/home/profil_screen.dart';

/// Web sidebar menü poz_id ile aynı: 1071 Patron, 1073 Operatör, 1076 İdari, 1077 Teknisyen, 1080 Muhasebe
class HomeShell extends StatefulWidget {
  const HomeShell({super.key});

  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  int _selectedIndex = 0;
  int _duyuruUnread = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadDuyuruUnread());
  }

  Future<void> _loadDuyuruUnread() async {
    final api = context.read<ApiClient>();
    final res = await api.getDuyurular();
    if (!mounted) return;
    if (res.isSuccess && res.data != null && res.data!['unread'] is int) {
      setState(() => _duyuruUnread = res.data!['unread'] as int);
    }
  }

  static const List<_NavItem> _allItems = [
    _NavItem(0, 'GENEL BAKIŞ', Icons.home, hideForPoz: [1073, 1076, 1077]),
    _NavItem(1, 'PROFİLİM', Icons.person, showOnlyForPoz: [1073, 1077]),
    _NavItem(2, 'MÜŞTERİLER', Icons.people, hideForPoz: [1076, 1077]),
    _NavItem(3, 'SERVİSLER', Icons.build, showOnlyForPoz: null),
    _NavItem(4, 'BEKLEYEN KAYITLAR', Icons.schedule, showOnlyForPoz: [1071, 1080, 1073]),
    _NavItem(5, 'PERSONELLER', Icons.badge, hideForPoz: [1076, 1077, 1073]),
    _NavItem(6, 'GENEL KASA', Icons.account_balance, hideForPoz: [1076, 1077, 1073]),
    _NavItem(7, 'KASA', Icons.bar_chart, hideForPoz: [1076, 1073]),
    _NavItem(8, 'BEKLEYEN ÖDEMELER', Icons.pending_actions, hideForPoz: [1076, 1073]),
    _NavItem(9, 'AYARLAR', Icons.settings, showOnlyForPoz: [1071]),
  ];

  List<_NavItem> get _visibleItems {
    final pozId = context.read<AuthProvider>().pozId;
    if (pozId == null) return _allItems;
    return _allItems.where((e) {
      if (e.showOnlyForPoz != null && e.showOnlyForPoz!.isNotEmpty) {
        return e.showOnlyForPoz!.contains(pozId);
      }
      if (e.hideForPoz != null) return !e.hideForPoz!.contains(pozId);
      return true;
    }).toList();
  }

  Widget _pageForIndex(int index) {
    final rawIndex = _visibleItems[index].rawIndex;
    switch (rawIndex) {
      case 0:
        return const PanelScreen();
      case 1:
        return const ProfilScreen();
      case 2:
        return const MusterilerScreen();
      case 3:
        return const ServislerScreen();
      case 4:
        return const BekleyenKayitlarScreen();
      case 5:
        return const PersonellerScreen();
      case 6:
        return const GenelKasaScreen();
      case 7:
        return const KasaScreen();
      case 8:
        return const BekleyenOdemeScreen();
      case 9:
        return const AyarlarScreen();
      default:
        return Center(child: Text('${_visibleItems[index].title} sayfası yakında eklenecek.'));
    }
  }

  @override
  Widget build(BuildContext context) {
    final items = _visibleItems;
    if (_selectedIndex >= items.length) _selectedIndex = 0;

    return Scaffold(
      appBar: AppBar(
        title: Text(items.isNotEmpty ? items[_selectedIndex].title : 'Servis Takip'),
        actions: [
          Stack(
            clipBehavior: Clip.none,
            children: [
              IconButton(
                icon: const Icon(Icons.notifications_outlined),
                onPressed: () async {
                  await Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const DuyurularScreen()),
                  );
                  _loadDuyuruUnread();
                },
              ),
              if (_duyuruUnread > 0)
                Positioned(
                  right: 8,
                  top: 8,
                  child: Container(
                    padding: const EdgeInsets.all(4),
                    decoration: const BoxDecoration(color: AppTheme.danger, shape: BoxShape.circle),
                    constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                    child: Text(
                      _duyuruUnread > 99 ? '99+' : '$_duyuruUnread',
                      style: const TextStyle(color: Colors.white, fontSize: 10),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
            ],
          ),
          PopupMenuButton<String>(
            icon: CircleAvatar(child: Text(context.watch<AuthProvider>().userName?.substring(0, 1).toUpperCase() ?? '?')),
            onSelected: (v) async {
              if (v == 'logout') {
                await context.read<AuthProvider>().logout();
                if (context.mounted) Navigator.of(context).pushReplacementNamed('/login');
              }
            },
            itemBuilder: (_) => [
              const PopupMenuItem(value: 'profile', child: Text('Profil')),
              const PopupMenuItem(value: 'logout', child: Text('Çıkış')),
            ],
          ),
        ],
      ),
      drawer: Drawer(
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            DrawerHeader(
              decoration: const BoxDecoration(color: AppTheme.primary),
              child: Text(
                'Servis Takip',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(color: Colors.white),
              ),
            ),
            ...List.generate(items.length, (i) {
              final item = items[i];
              return ListTile(
                leading: Icon(item.icon),
                title: Text(item.title),
                selected: _selectedIndex == i,
                onTap: () {
                  setState(() => _selectedIndex = i);
                  Navigator.pop(context);
                },
              );
            }),
          ],
        ),
      ),
      body: _pageForIndex(_selectedIndex),
    );
  }
}

class _NavItem {
  const _NavItem(this.rawIndex, this.title, this.icon, {this.hideForPoz, this.showOnlyForPoz});
  final int rawIndex;
  final String title;
  final IconData icon;
  final List<int>? hideForPoz;
  final List<int>? showOnlyForPoz;
}
