import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:servis_takip_mobile/core/theme/app_theme.dart';
import 'package:servis_takip_mobile/features/auth/auth_provider.dart';
import 'package:servis_takip_mobile/features/auth/two_factor_screen.dart';

/// Web auth-login-creative ile uyumlu: sol form, primary renk vurgusu
class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _nickController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

  @override
  void dispose() {
    _nickController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final nick = _nickController.text.trim();
    final password = _passwordController.text;
    if (nick.isEmpty || password.isEmpty) {
      context.read<AuthProvider>().clearError();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Kullanıcı adı ve şifre girin.')),
      );
      return;
    }

    final auth = context.read<AuthProvider>();
    final ok = await auth.login(nick, password);
    if (!mounted) return;
    if (ok) {
      Navigator.of(context).pushReplacementNamed('/home');
      return;
    }
    if (auth.error != null && auth.error!.contains('İki adımlı')) {
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => const TwoFactorScreen(),
        ),
      ).then((_) {
        if (mounted) Navigator.of(context).pushReplacementNamed('/home');
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const SizedBox(height: 24),
                Icon(Icons.build_circle_outlined, size: 64, color: AppTheme.primary),
                const SizedBox(height: 8),
                Text('Servis Takip', style: Theme.of(context).textTheme.titleLarge?.copyWith(color: AppTheme.primary)),
                const SizedBox(height: 24),
                Text(
                  'Sisteme kullanıcı adı ve şifreniz ile giriş yapabilirsiniz.',
                  style: Theme.of(context).textTheme.bodyMedium,
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 24),
                Consumer<AuthProvider>(
                  builder: (_, auth, __) {
                    if (auth.error != null && !auth.error!.contains('İki adımlı')) {
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Text(auth.error!, style: const TextStyle(color: AppTheme.danger)),
                      );
                    }
                    return const SizedBox.shrink();
                  },
                ),
                TextField(
                  controller: _nickController,
                  decoration: const InputDecoration(
                    labelText: 'Kullanıcı Adı',
                    hintText: 'Kullanıcı Adı',
                  ),
                  textInputAction: TextInputAction.next,
                  onSubmitted: (_) => FocusScope.of(context).nextFocus(),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _passwordController,
                  decoration: InputDecoration(
                    labelText: 'Şifre',
                    hintText: 'Şifre',
                    suffixIcon: IconButton(
                      icon: Icon(_obscurePassword ? Icons.visibility : Icons.visibility_off),
                      onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                    ),
                  ),
                  obscureText: _obscurePassword,
                  textInputAction: TextInputAction.done,
                  onSubmitted: (_) => _submit(),
                ),
                const SizedBox(height: 24),
                SizedBox(
                  width: double.infinity,
                  child: Consumer<AuthProvider>(
                    builder: (_, auth, __) => ElevatedButton(
                      onPressed: auth.isLoading ? null : _submit,
                      child: auth.isLoading
                          ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                          : const Text('GİRİŞ YAP'),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text('Servis_Takip v1.2', style: Theme.of(context).textTheme.bodySmall?.copyWith(color: AppTheme.textMuted)),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
