import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:hayya_al_salah/models/movie.dart';
import 'package:hayya_al_salah/widgets/movieList.dart';
import 'package:http/http.dart' as http;

class CategoryVideoScreen extends StatefulWidget {
  final int categoryId;
  final String categoryName;

  const CategoryVideoScreen(
      {Key? key, required this.categoryId, required this.categoryName})
      : super(key: key);

  @override
  _CategoryVideoScreenState createState() => _CategoryVideoScreenState();
}

class _CategoryVideoScreenState extends State<CategoryVideoScreen> {
  List<Movie> movies = [];
  bool isLoading = true;
  int currentPage = 1;
  bool hasMore = true;
  Map<int, String> categoryMap = {};

  @override
  void initState() {
    super.initState();
    fetchVideosByCats();
  }

  Future<void> fetchVideosByCats() async {
    if (isLoading) return;

    setState(() {
      isLoading = true;
    });
    final response = await http.get(
        Uri.parse('https://salah.pakperegrine.com/apis/index.php/apis/videos'));

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      final List<Movie> fetchedMovies =
          await Future.wait((data as List).map((movieData) async {
        return Movie(
          movieID: int.tryParse(movieData['videoID']) ?? 0,
          title: movieData['title'],
          description: movieData['description'],
          categoryID: int.tryParse(movieData['categoryID']) ??
              0, // You can set a default or parse if available
          pdfFile: '', // Not available in API response
          animationFile: movieData['animationFile'] ?? "", // Trailer link
          videoFile: movieData['videoFile'] ?? "", // Trailer link
          image:
              'https://salah.pakperegrine.com/apis/uploads/${movieData['image']}',
          genre: '', // Convert genre IDs to names
        );
      }).toList());

      setState(() {
        currentPage++;
        isLoading = false;
        hasMore = fetchedMovies.isNotEmpty;
        movies.addAll(fetchedMovies);
      });
    } else {
      throw Exception('Failed to load videos');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.transparent,
      appBar: AppBar(
        title: Text(widget.categoryName),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0.2,
      ),
      body: NotificationListener<ScrollNotification>(
        onNotification: (ScrollNotification scrollInfo) {
          if (!isLoading &&
              hasMore &&
              scrollInfo.metrics.pixels ==
                  scrollInfo.metrics.maxScrollExtent) {}
          return false;
        },
        child: movies.isEmpty
            ? const Center(child: CircularProgressIndicator())
            : MovieList(movies: movies), // Use the reusable widget
      ),
    );
  }
}
