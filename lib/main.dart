import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:provider/provider.dart';

import 'screens/splash_screen.dart';
import 'services/api_service.dart';
import 'services/app_localizations.dart';
import 'services/auth_service.dart';
import 'services/settings_service.dart';
import 'services/theme_service.dart';
import 'themes/app_themes.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize API service
  final apiService = ApiService();
  await apiService.initialize();

  // Initialize Auth service
  final authService = AuthService();
  await authService.initialize();

  // Initialize Settings service
  final settingsService = SettingsService();
  await settingsService.initialize();

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (context) => ThemeService()),
        ChangeNotifierProvider.value(value: authService),
        ChangeNotifierProvider.value(value: settingsService),
      ],
      child: const HayyaAlSalahApp(),
    ),
  );
}

class HayyaAlSalahApp extends StatelessWidget {
  const HayyaAlSalahApp({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer2<ThemeService, SettingsService>(
      builder: (context, themeService, settingsService, child) {
        return MaterialApp(
          title: 'Hayya Al Salah',
          debugShowCheckedModeBanner: false,
          themeMode: themeService.themeMode,
          theme: AppThemes.lightTheme,
          darkTheme: AppThemes.darkTheme,
          locale: settingsService.currentLocale,
          localizationsDelegates: const [
            AppLocalizations.delegate,
            GlobalMaterialLocalizations.delegate,
            GlobalWidgetsLocalizations.delegate,
            GlobalCupertinoLocalizations.delegate,
          ],
          supportedLocales: const [
            Locale('en', 'US'),
            Locale('ar', 'SA'),
            Locale('ur', 'PK'),
          ],
          builder: (context, child) {
            return Directionality(
              textDirection:
                  settingsService.isRTL ? TextDirection.rtl : TextDirection.ltr,
              child: child!,
            );
          },
          home: const SplashScreen(),
        );
      },
    );
  }
}
