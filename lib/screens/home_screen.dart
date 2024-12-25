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
  List<Movie> topMovies = [];
  int currentPage = 1;
  bool isLoading = false;
  bool hasMore = true;
  Map<int, String> genreMap = {};

  @override
  void initState() {
    super.initState();
    fetchGenres();
    fetchTrendingMovies();
    fetchTopMovies();
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

  Future<void> fetchTrendingMovies() async {
    if (isLoading) return;

    setState(() {
      isLoading = true;
    });

    final response = await http.get(Uri.parse(
        'https://api.themoviedb.org/3/trending/movie/week?api_key=eae26be244b6ba02e8ee80cfc64acb5a&page=$currentPage'));

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
      throw Exception('Failed to load trending movies');
    }
  }

  Future<void> fetchTopMovies() async {
    final response = await http.get(Uri.parse(
        'https://api.themoviedb.org/3/trending/movie/day?api_key=eae26be244b6ba02e8ee80cfc64acb5a'));

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      final List<Movie> fetchedMovies = await Future.wait(
          (data['results'] as List).take(5).map((movieData) async {
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
        topMovies = fetchedMovies;
      });
    } else {
      throw Exception('Failed to load top movies');
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
      appBar: const CustomAppBar(title: 'Home'),
      body: Column(
        children: [
          if (topMovies.isNotEmpty)
            Container(
              margin: const EdgeInsets.all(10.0),
              child: ClipRect(
                /** Banner Widget **/
                child: Banner(
                  message: "20% off !!",
                  location: BannerLocation.bottomStart,
                  color: Colors.red,
                  child: Container(
                    color: Colors.green[100],
                    height: 300,
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(10, 20, 10, 20),
                      child: Column(
                        children: <Widget>[
                          Image.network(
                              'https://media.geeksforgeeks.org/wp-content/cdn-uploads/20190806131525/forkPython.jpg'), //Image.network
                          const SizedBox(height: 10),
                          const Text(
                            'GeeksforGeeks',
                            style: TextStyle(
                                color: Colors.green,
                                fontSize: 40,
                                fontWeight: FontWeight.bold), //TextStyle
                          ),
                          const SizedBox(
                            height: 5,
                          ), //SizedBox
                          const Text(
                            'Fork Python Course',
                            style: TextStyle(
                                color: Colors.green,
                                fontSize: 20,
                                fontWeight: FontWeight.bold), //TextStyle
                          ), //Text
                          const SizedBox(height: 20),

                          // RaiseButton is deprecated and should not be used. Use ElevatedButton instead.

                          // RaisedButton(
                          // color: Colors.greenAccent[400],
                          // onPressed: () {},
                          // child: const Text('Register'),
                          // ) //RaisedButton
                        ], //<Widget>[]
                      ), //Column
                    ), //Padding
                  ), //Container
                ), //Banner
              ), //ClipRect
            ),
          Expanded(
            child: NotificationListener<ScrollNotification>(
              onNotification: (ScrollNotification scrollInfo) {
                if (!isLoading &&
                    hasMore &&
                    scrollInfo.metrics.pixels ==
                        scrollInfo.metrics.maxScrollExtent) {
                  fetchTrendingMovies();
                }
                return false;
              },
              child: movies.isEmpty
                  ? const Center(child: CircularProgressIndicator())
                  : MovieList(movies: movies), // Use the reusable widget
            ),
          ),
        ],
      ),
    );
  }
}
