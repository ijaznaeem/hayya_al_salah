import 'package:shared_preferences/shared_preferences.dart';

import '../models/movie.dart';

class SharedPrefHelper {
  final String _key = 'video_list';

  // Save the list of Movies
  Future<void> saveMovieList(List<Movie> objects) async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    final List<String> jsonList = objects
        .map((obj) => obj.toJson())
        .cast<String>()
        .toList(); // Encode to JSON
    await prefs.setStringList(_key, jsonList);
  }

  // Retrieve the list of Movies
  Future<List<Movie>> getMovieList() async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    final List<String>? jsonList = prefs.getStringList(_key);

    if (jsonList == null) return [];
    return jsonList
        .map((jsonStr) => Movie.fromJson(jsonStr))
        .toList(); // Decode from JSON
  }

  // Add a new Movie to the list
  Future<void> addMovie(Movie object) async {
    List<Movie> list = await getMovieList();
    list.add(object);
    await saveMovieList(list);
  }

  // Remove a Movie by ID
  Future<void> removeMovie(int id) async {
    List<Movie> list = await getMovieList();
    list.removeWhere((obj) => obj.movieID == id);
    await saveMovieList(list);
  }
}
