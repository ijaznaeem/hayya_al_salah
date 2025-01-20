import 'package:flutter/material.dart';

import './button_styles.dart';

class CustomButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool isNormal;
  final VoidCallback onPressed;

  const CustomButton({
    Key? key,
    required this.icon,
    required this.label,
    required this.isNormal,
    required this.onPressed,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
        style: buttonStyle(isNormal),
        onPressed: onPressed,
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon),
            SizedBox(width: 8), // Add space between icon and text
            Text(
              label,
              style: TextStyle(fontSize: 12),
            ),
          ],
        ));
  }
}
