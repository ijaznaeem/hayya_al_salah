import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:hayya_al_salah/models/genre.dart';
import 'package:hayya_al_salah/screens/genre_movies_screen.dart';
import 'package:http/http.dart' as http;

class GenersScreen extends StatefulWidget {
  const GenersScreen({Key? key}) : super(key: key);

  @override
  _GenersScreenState createState() => _GenersScreenState();
}

class _GenersScreenState extends State<GenersScreen> {
  List<Genre> genres = [];
  List<Genre> filteredGenres = [];
  bool isLoading = true;
  TextEditingController searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    fetchGenres();
    searchController.addListener(() {
      filterGenres();
    });
  }

  Future<void> fetchGenres() async {
    final response = await http.get(Uri.parse(
        'https://api.themoviedb.org/3/genre/movie/list?api_key=eae26be244b6ba02e8ee80cfc64acb5a&language=en-US'));

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      setState(() {
        genres = (data['genres'] as List)
            .map((genreData) => Genre.fromJson(genreData))
            .toList();
        filteredGenres = genres;
        isLoading = false;
      });
    } else {
      throw Exception('Failed to load genres');
    }
  }

  void filterGenres() {
    final query = searchController.text.toLowerCase();
    setState(() {
      filteredGenres = genres
          .where((genre) => genre.genreName.toLowerCase().contains(query))
          .toList();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.transparent,
      appBar: AppBar(
        title: const Text('Geners'),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0.2,
      ),
      body: isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: TextField(
                    controller: searchController,
                    decoration: const InputDecoration(
                      labelText: 'Search Genres',
                      border: OutlineInputBorder(),
                    ),
                  ),
                ),
                Expanded(
                  child: ListView.builder(
                    itemCount: filteredGenres.length,
                    itemBuilder: (context, index) {
                      final genre = filteredGenres[index];
                      return ListTile(
                        title: Text(genre.genreName),
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => GenreMoviesScreen(
                                genreId: genre.genreId,
                                genreName: genre.genreName,
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
