import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:hayya_al_salah/models/movie.dart';
import 'package:hayya_al_salah/widgets/appBr.dart';
import 'package:hayya_al_salah/widgets/movieList.dart';
import 'package:http/http.dart' as http;

class HomeScreen extends StatefulWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  _HomeScreenState createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<Movie> movies = [];
  int currentPage = 1;
  int pageLimit = 10;
  bool isLoading = false;
  bool hasMore = true;
  Map<int, String> genreMap = {};

  @override
  void initState() {
    super.initState();
    fetchTrendingMovies();
  }

  Future<void> fetchTrendingMovies() async {
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
          genre: movieData['genre'] ?? '', // Convert genre IDs to names
        );
      }).toList());

      setState(() {
        currentPage++;
        isLoading = false;
        hasMore = fetchedMovies.isNotEmpty;
        movies.addAll(fetchedMovies);
      });
    } else {
      setState(() {
        isLoading = false;
      });
      throw Exception('Failed to load trending movies');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.transparent,
      appBar: const CustomAppBar(title: 'Hayya Alal Salah'),
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
            image: DecorationImage(
                image: AssetImage('assets/images/background1.jpg'),
                fit: BoxFit.cover,
                opacity: 0.3)),
        child: Column(
          children: [
            // Add banner image
            Container(
              width: double.infinity,
              height: 200.0,
              decoration: const BoxDecoration(
                image: DecorationImage(
                  image: AssetImage('assets/images/banner_image.png'),
                  fit: BoxFit.cover,
                ),
              ),
            ),
            Expanded(
              child: NotificationListener<ScrollNotification>(
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
            ),
          ],
        ),
      ),
    );
  }
}
