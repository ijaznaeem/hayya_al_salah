import 'package:flutter/material.dart';
import 'package:hayya_al_salah/models/genre.dart';
import 'package:hayya_al_salah/services/api_service.dart'; // Add this import
import 'package:hayya_al_salah/widgets/appBr.dart';
import 'package:shimmer/shimmer.dart';

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
  final ApiService _apiService = ApiService(); // Add this line

  @override
  void initState() {
    super.initState();
    fetchCategories();
    searchController.addListener(() {
      filterCategories();
    });
  }

  Future<void> fetchCategories() async {
    final response = await _apiService.get('categories');

    if (response.statusCode == 200) {
      final data = response.data;
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
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
            image: DecorationImage(
                image: AssetImage('assets/images/background1.jpg'),
                fit: BoxFit.cover,
                opacity: 0.3)),
        child: isLoading
            ? ListView.builder(
                itemCount: 5,
                itemBuilder: (context, index) => Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: Shimmer.fromColors(
                    baseColor: Colors.grey[300]!,
                    highlightColor: Colors.grey[100]!,
                    period:
                        const Duration(seconds: 2), // Add animation duration
                    child: Row(
                      children: [
                        Container(
                          width: 50,
                          height: 50,
                          color: Colors.white,
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Container(
                                width: double.infinity,
                                height: 20,
                                color: Colors.white,
                              ),
                              const SizedBox(height: 5),
                              Container(
                                width: double.infinity,
                                height: 20,
                                color: Colors.white,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              )
            : SafeArea(
                child: SingleChildScrollView(
                  child: Column(
                    children: [
                      Padding(
                        padding: const EdgeInsets.all(8.0),
                        child: TextField(
                            controller: searchController,
                            decoration: InputDecoration(
                              labelText: 'Search topics',
                              filled: true,
                              focusedBorder: const UnderlineInputBorder(
                                  borderSide: BorderSide.none,
                                  borderRadius:
                                      BorderRadius.all(Radius.circular(10))),
                              enabledBorder: const UnderlineInputBorder(
                                  borderSide: BorderSide.none,
                                  borderRadius:
                                      BorderRadius.all(Radius.circular(10))),
                              fillColor: Colors.white,
                              hintText: 'Search topics',
                              labelStyle: const TextStyle(color: Colors.purple),
                              prefixIcon: const Icon(
                                Icons.search,
                                color: Colors.purple,
                              ),
                              suffixIcon: searchController.text.isNotEmpty
                                  ? IconButton(
                                      icon: const Icon(Icons.clear,
                                          color: Colors.purple),
                                      onPressed: () {
                                        setState(() {
                                          searchController.clear();
                                        });
                                      },
                                    )
                                  : null,
                              border: const OutlineInputBorder(
                                borderRadius:
                                    BorderRadius.all(Radius.circular(20.0)),
                              ),
                            )),
                      ),
                      ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: filteredCategories.length,
                        itemBuilder: (context, index) {
                          final genre = filteredCategories[index];
                          return ListTile(
                            title: Container(
                              padding: const EdgeInsets.all(8),
                              // _formKey!.currentState!.validate() ? 200 : 600,
                              // height: isEmailCorrect ? 260 : 182,
                              width: MediaQuery.of(context).size.width / 1.1,
                              decoration: BoxDecoration(
                                  color: Colors.white.withOpacity(0.3),
                                  borderRadius: BorderRadius.circular(20)),
                              child: Text(genre.categoryName,
                                  style: const TextStyle(color: Colors.purple),
                                  textAlign: TextAlign.center),
                            ),
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
                    ],
                  ),
                ),
              ),
      ),
    );
  }
}
