import 'package:flutter/material.dart';

import '../utilities/icon_path_util.dart';
import '../widgets/appBr.dart';

class FavoritesScreen extends StatelessWidget {
  const FavoritesScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: Colors.transparent,
      appBar: CustomAppBar(title: "Favorities"),
      body: Center(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            Image(image: AssetImage(tabIconFav), height: 100.0),
            SizedBox(height: 50.0),
            Text(
              'My Sweet Favorites',
              style: TextStyle(fontSize: 24),
            ),
          ],
        ),
      ),
    );
  }
}
