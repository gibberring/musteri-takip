import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:servis_takip_mobile/core/api/api_client.dart';
import 'package:servis_takip_mobile/core/theme/app_theme.dart';
import 'package:servis_takip_mobile/core/storage/auth_storage.dart';
import 'package:servis_takip_mobile/features/auth/auth_provider.dart';
import 'package:servis_takip_mobile/features/auth/login_screen.dart';
import 'package:servis_takip_mobile/features/home/home_shell.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const ServisTakipApp());
}

class ServisTakipApp extends StatelessWidget {
  const ServisTakipApp({super.key});

  @override
  Widget build(BuildContext context) {
    final storage = AuthStorage();
    final api = ApiClient(storage: storage);
    return MultiProvider(
      providers: [
        Provider<ApiClient>.value(value: api),
        ChangeNotifierProvider<AuthProvider>(
          create: (_) => AuthProvider(api: api, storage: storage),
        ),
      ],
      child: MaterialApp(
        title: 'Servis Takip',
        theme: AppTheme.light,
        initialRoute: '/',
        routes: {
          '/': (context) => const _AuthGate(),
          '/login': (context) => const LoginScreen(),
          '/home': (context) => const HomeShell(),
        },
      ),
    );
  }
}

class _AuthGate extends StatefulWidget {
  const _AuthGate();

  @override
  State<_AuthGate> createState() => _AuthGateState();
}

class _AuthGateState extends State<_AuthGate> {
  bool _checked = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _checkAuth());
  }

  Future<void> _checkAuth() async {
    final ok = await context.read<AuthProvider>().loadUser();
    if (!mounted) return;
    setState(() => _checked = true);
    if (ok) {
      Navigator.of(context).pushReplacementNamed('/home');
    } else {
      Navigator.of(context).pushReplacementNamed('/login');
    }
  }

  @override
  Widget build(BuildContext context) {
    if (!_checked) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }
    return const SizedBox.shrink();
  }
}
