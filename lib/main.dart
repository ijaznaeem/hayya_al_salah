import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:hayya_al_salah/screens/main_screen.dart';

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
      title: 'Awesome Bottom Navigation',
      theme: ThemeData(
        primarySwatch: Colors.blue,
      ),
      home: const MainScreen(),
    );
  }
}
