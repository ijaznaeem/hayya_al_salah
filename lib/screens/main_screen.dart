import 'package:flutter/material.dart';
import 'package:hayya_al_salah/screens/geners_screen.dart';
import 'package:hayya_al_salah/screens/home_screen.dart';
import 'package:hayya_al_salah/screens/plants_screen.dart';
import 'package:hayya_al_salah/utilities/icon_path_util.dart';
import 'package:persistent_bottom_nav_bar/persistent_bottom_nav_bar.dart';

import '../utilities/constant.dart';

class MainScreen extends StatefulWidget {
  const MainScreen({Key? key}) : super(key: key);

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  PersistentTabController? _controller;
  int? selectedIndex;

  @override
  void initState() {
    _controller = PersistentTabController();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: PersistentTabView(
        context,
        controller: _controller,
        navBarHeight: kSizeBottomNavigationBarHeight,
        screens: _buildScreens(),
        items: _navBarsItems(),
        confineToSafeArea: true,
        backgroundColor: Colors.amber,
        handleAndroidBackButtonPress: true,
        resizeToAvoidBottomInset: true,
        stateManagement: true,
        navBarStyle: NavBarStyle.style14,
        onItemSelected: (final index) {
          setState(() {
            _controller?.index = index; // THIS IS CRITICAL!! Don't miss it!

            if (index == 1) {}
          });
        },
      ),
    );
  }

  List<Widget> _buildScreens() {
    return [
      const HomeScreen(),
      const GenersScreen(),
      const PlantsScreen(),
    ];
  }

  List<PersistentBottomNavBarItem> _navBarsItems() {
    return [
      PersistentBottomNavBarItem(
        icon: Column(
          children: [
            SizedBox(
              height: kSizeBottomNavigationBarIconHeight,
              child: Image.asset(
                kIconPathBottomNavigationBarHome,
              ),
            ),
          ],
        ),
        inactiveIcon: Column(
          children: [
            SizedBox(
              height: kSizeBottomNavigationBarIconHeight,
              child: Image.asset(
                kIconPathBottomNavigationBarHomeDeactive,
              ),
            ),
          ],
        ),
        title: ('Home'),
        activeColorPrimary: kColorBNBActiveTitleColor,
        textStyle: const TextStyle(color: Colors.red),
        inactiveColorPrimary: kColorBNBDeactiveTitleColor,
      ),
      PersistentBottomNavBarItem(
        icon: Column(
          children: [
            SizedBox(
              height: kSizeBottomNavigationBarIconHeight,
              child: Image.asset(
                kIconPathBottomNavigationBarAnimals,
              ),
            ),
          ],
        ),
        inactiveIcon: Column(
          children: [
            SizedBox(
              height: kSizeBottomNavigationBarIconHeight,
              child: Image.asset(
                kIconPathBottomNavigationBarAnimalsDeactive,
              ),
            ),
          ],
        ),
        title: ('Categories'),
        activeColorPrimary: kColorBNBActiveTitleColor,
        inactiveColorPrimary: kColorBNBDeactiveTitleColor,
      ),
      PersistentBottomNavBarItem(
        icon: Column(
          children: [
            SizedBox(
              height: kSizeBottomNavigationBarIconHeight,
              child: Image.asset(
                kIconPathBottomNavigationBarPlants,
              ),
            ),
          ],
        ),
        inactiveIcon: Column(
          children: [
            SizedBox(
              height: kSizeBottomNavigationBarIconHeight,
              child: Image.asset(
                kIconPathBottomNavigationBarPlantsDeactive,
              ),
            ),
          ],
        ),
        title: ('Favorites'),
        activeColorPrimary: kColorBNBActiveTitleColor,
        inactiveColorPrimary: kColorBNBDeactiveTitleColor,
      ),
    ];
  }
}
