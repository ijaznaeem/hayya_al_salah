import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:hayya_al_salah/models/genre.dart';
import 'package:hayya_al_salah/widgets/appBr.dart';
import 'package:http/http.dart' as http;

import 'category_video_screen.dart';

class CategoriesScreen extends StatefulWidget {
  const CategoriesScreen({Key? key}) : super(key: key);

  @override
  _CategoriesScreenState createState() => _CategoriesScreenState();
}

class _CategoriesScreenState extends State<CategoriesScreen> {
  List<Categories> genres = [];
  List<Categories> filteredCategories = [];
  bool isLoading = true;
  TextEditingController searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    fetchCategories();
    searchController.addListener(() {
      filterCategories();
    });
  }

  Future<void> fetchCategories() async {
    final response = await http.get(Uri.parse(
        'https://salah.pakperegrine.com/apis/index.php/apis/categories'));

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      setState(() {
        genres = (data as List)
            .map((genreData) => Categories.fromJson(genreData))
            .toList();
        filteredCategories = genres;
        isLoading = false;
      });
    } else {
      throw Exception('Failed to load genres');
    }
  }

  void filterCategories() {
    final query = searchController.text.toLowerCase();
    setState(() {
      filteredCategories = genres
          .where((genre) => genre.categoryName.toLowerCase().contains(query))
          .toList();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.transparent,
      appBar: const CustomAppBar(title: "Categories"),
      body: isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: TextField(
                    controller: searchController,
                    decoration: const InputDecoration(
                      labelText: 'Search Categories',
                      border: OutlineInputBorder(),
                    ),
                  ),
                ),
                Expanded(
                  child: ListView.builder(
                    itemCount: filteredCategories.length,
                    itemBuilder: (context, index) {
                      final genre = filteredCategories[index];
                      return ListTile(
                        title: Text(genre.categoryName),
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => CategoryVideoScreen(
                                categoryId: genre.categoryId,
                                categoryName: genre.categoryName,
                              ),
                            ),
                          );
                        },
                      );
                    },
                  ),
                ),
              ],
            ),
    );
  }
}
