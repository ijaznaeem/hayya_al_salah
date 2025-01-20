import 'package:flutter/material.dart';

ButtonStyle buttonStyle(bool isNormal) {
  return ElevatedButton.styleFrom(
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(10.0),
    ),
    backgroundColor: isNormal ? Colors.purple : Colors.red,
    padding: const EdgeInsets.all(10),
    foregroundColor: Colors.white,
  );
}
