import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:hayya_al_salah/models/movie.dart';
import 'package:hayya_al_salah/widgets/movieList.dart';
import 'package:http/http.dart' as http;

class GenreMoviesScreen extends StatefulWidget {
  final int genreId;
  final String genreName;

  const GenreMoviesScreen(
      {Key? key, required this.genreId, required this.genreName})
      : super(key: key);

  @override
  _GenreMoviesScreenState createState() => _GenreMoviesScreenState();
}

class _GenreMoviesScreenState extends State<GenreMoviesScreen> {
  List<Movie> movies = [];
  bool isLoading = true;
  int currentPage = 1;
  bool hasMore = true;
  Map<int, String> genreMap = {};

  @override
  void initState() {
    super.initState();
    fetchGenres();
    fetchMoviesByGenre();
  }

  Future<void> fetchGenres() async {
    final response = await http.get(Uri.parse(
        'https://api.themoviedb.org/3/genre/movie/list?api_key=eae26be244b6ba02e8ee80cfc64acb5a&language=en-US'));

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      setState(() {
        genreMap = Map.fromIterable(data['genres'],
            key: (genre) => genre['id'], value: (genre) => genre['name']);
      });
    } else {
      throw Exception('Failed to load genres');
    }
  }

  Future<void> fetchMoviesByGenre() async {
    if (isLoading) return;

    setState(() {
      isLoading = true;
    });

    final response = await http.get(Uri.parse(
        'https://api.themoviedb.org/3/discover/movie?api_key=eae26be244b6ba02e8ee80cfc64acb5a&with_genres=${widget.genreId}&page=$currentPage'));

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      final List<Movie> fetchedMovies =
          await Future.wait((data['results'] as List).map((movieData) async {
        final trailerLink = await fetchMovieTrailer(movieData['id']);
        return Movie(
          movieID: movieData['id'],
          title: movieData['title'],
          description: movieData['overview'],
          categoryID: 0, // You can set a default or parse if available
          pdfFile: '', // Not available in API response
          animationFile: trailerLink, // Trailer link
          image: 'https://image.tmdb.org/t/p/w500${movieData['poster_path']}',
          genre: (movieData['genre_ids'] as List)
              .map((id) => genreMap[id])
              .join(', '), // Convert genre IDs to names
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
      throw Exception('Failed to load movies by genre');
    }
  }

  Future<String> fetchMovieTrailer(int movieId) async {
    final response = await http.get(Uri.parse(
        'https://api.themoviedb.org/3/movie/$movieId/videos?api_key=eae26be244b6ba02e8ee80cfc64acb5a'));

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      final trailer = (data['results'] as List).firstWhere(
          (video) => video['type'] == 'Trailer' && video['site'] == 'YouTube',
          orElse: () => null);
      return trailer != null
          ? 'https://www.youtube.com/watch?v=${trailer['key']}'
          : '';
    } else {
      return '';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.transparent,
      appBar: AppBar(
        title: Text(widget.genreName),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0.2,
      ),
      body: NotificationListener<ScrollNotification>(
        onNotification: (ScrollNotification scrollInfo) {
          if (!isLoading &&
              hasMore &&
              scrollInfo.metrics.pixels == scrollInfo.metrics.maxScrollExtent) {
            fetchMoviesByGenre();
          }
          return false;
        },
        child: movies.isEmpty
            ? const Center(child: CircularProgressIndicator())
            : MovieList(movies: movies), // Use the reusable widget
      ),
    );
  }
}
