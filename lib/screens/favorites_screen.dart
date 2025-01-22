import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:hayya_al_salah/models/movie.dart';
import 'package:shimmer/shimmer.dart';

import '../helpers/shared_pref_helper.dart'; // Import the helper
import '../screens/video_screen.dart'; // Import the VideoScreen
import '../widgets/appBr.dart';
import '../widgets/button_styles.dart';

class FavoritesScreen extends StatefulWidget {
  const FavoritesScreen({Key? key}) : super(key: key);

  @override
  _FavoritesScreenState createState() => _FavoritesScreenState();
}

class _FavoritesScreenState extends State<FavoritesScreen> {
  List<Movie> favoriteMovies = [];
  final SharedPrefHelper _sharedPrefHelper =
      SharedPrefHelper(); // Initialize the helper

  @override
  void initState() {
    super.initState();
    loadFavoriteMovies();
  }

  Future<void> loadFavoriteMovies() async {
    final List<Movie> fetchedMovies = await _sharedPrefHelper
        .getMovieList(); // Use the helper to get the movie list

    setState(() {
      favoriteMovies = fetchedMovies;
    });
  }

  Future<void> removeFavoriteMovie(int movieID) async {
    await _sharedPrefHelper.removeMovie(movieID);
    loadFavoriteMovies(); // Reload the favorite movies after removal
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.transparent,
      appBar: const CustomAppBar(title: "Favorites"),
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
            image: DecorationImage(
                image: AssetImage('assets/images/background1.jpg'),
                fit: BoxFit.cover,
                opacity: 0.3)),
        child: favoriteMovies.isEmpty
            ? ListView.builder(
                itemCount: 5,
                itemBuilder: (context, index) => ListTile(
                  title: Shimmer.fromColors(
                    baseColor: Colors.grey[300]!,
                    highlightColor: Colors.grey[100]!,
                    child: Container(
                      padding: const EdgeInsets.only(top: 8, bottom: 8),
                      height: 50,
                      width: MediaQuery.of(context).size.width / 1.1,
                      color: Colors.white,
                    ),
                  ),
                  leading: Shimmer.fromColors(
                    baseColor: Colors.grey[300]!,
                    highlightColor: Colors.grey[100]!,
                    child: Container(
                      width: 50,
                      height: 50,
                      color: Colors.white,
                    ),
                  ),
                  trailing: Shimmer.fromColors(
                    baseColor: Colors.grey[300]!,
                    highlightColor: Colors.grey[100]!,
                    child: Container(
                      width: 50,
                      height: 50,
                      color: Colors.white,
                    ),
                  ),
                ),
              )
            : ListView.builder(
                itemCount: favoriteMovies.length,
                itemBuilder: (context, index) {
                  final movie = favoriteMovies[index];
                  return ListTile(
                    title: Container(
                        padding: const EdgeInsets.only(top: 8, bottom: 8),
                        height: 50,
                        width: MediaQuery.of(context).size.width / 1.1,
                        decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.3),
                            borderRadius: BorderRadius.circular(10)),
                        child: Text(
                          movie.title,
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                              fontSize: 16, fontWeight: FontWeight.bold),
                        )),
                    leading: GestureDetector(
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => VideoScreen(movie: movie),
                          ),
                        );
                      },
                      child: CachedNetworkImage(
                        imageUrl: movie.image,
                        width: 50,
                        height: 50,
                        placeholder: (context, url) =>
                            const CircularProgressIndicator(),
                        errorWidget: (context, url, error) =>
                            const Icon(Icons.error),
                      ),
                    ),
                    trailing: IconButton(
                      style: buttonStyle(false),
                      icon: const Icon(Icons.delete),
                      onPressed: () => removeFavoriteMovie(movie.movieID),
                    ),
                  );
                },
              ),
      ),
    );
  }
}
