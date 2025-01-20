import 'package:flutter/material.dart';

void showSnack(BuildContext context, String message,
    {Duration duration = const Duration(seconds: 3)}) {
  final snackBar = SnackBar(
    content: Text(message),
    duration: duration,
  );

  ScaffoldMessenger.of(context).showSnackBar(snackBar);
}
