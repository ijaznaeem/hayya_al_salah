import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import 'widgets/splash_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(
    statusBarColor: Colors.transparent, // transparent status bar
  ));
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  // This widget is the root of your application.
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Hayya Al Salah',
      theme: ThemeData(
        primarySwatch: const MaterialColor(
          0xFFB0B42B,
          <int, Color>{
            50: Color(0xFFF5F5DC),
            100: Color(0xFFE5E5B8),
            200: Color(0xFFD5D594),
            300: Color(0xFFC5C570),
            400: Color(0xFFB5B54C),
            500: Color(0xFFA5A528),
            600: Color(0xFF959514),
            700: Color(0xFF858500),
            800: Color(0xFF757500),
            900: Color(0xFF656500),
          },
        ),
      ),
      home: SplashScreen(), // Set SplashScreen as the home widget
    );
  }
}
