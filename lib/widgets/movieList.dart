import 'package:flutter/material.dart';
import 'package:hayya_al_salah/models/movie.dart';

import '../screens/video_screen.dart';
import 'custom_button.dart';

class MovieList extends StatelessWidget {
  final List<Movie> movies;

  const MovieList({Key? key, required this.movies}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      itemCount: movies.length,
      itemBuilder: (context, index) {
        final movie = movies[index];
        return Card(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              GestureDetector(
                onTap: () {
                  showModalBottomSheet(
                    context: context,
                    isScrollControlled: true,
                    builder: (context) => VideoScreen(movie: movie),
                  );
                },
                child: Image.network(movie.image,
                    width: double.infinity, height: 200, fit: BoxFit.cover),
              ),
              Container(
                  padding: const EdgeInsets.only(top: 20, bottom: 20),
                  width: MediaQuery.of(context).size.width / 1.1,
                  decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.3),
                      borderRadius: BorderRadius.circular(20)),
                  child: Text(
                    movie.title,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                        fontSize: 16, fontWeight: FontWeight.bold),
                  )),
              Container(
                  padding: const EdgeInsets.only(top: 20, bottom: 20),
                  width: MediaQuery.of(context).size.width / 1.1,
                  decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.3),
                      borderRadius: BorderRadius.circular(20)),
                  child: Text(
                    'Topics: ${movie.genre}',
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                        fontSize: 12, fontWeight: FontWeight.normal),
                  )),
              if (movie.videoFile.isNotEmpty)
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 8.0),
                  child: Center(
                    child: CustomButton(
                      isNormal: true,
                      icon: Icons.play_arrow,
                      onPressed: () {
                        showModalBottomSheet(
                          context: context,
                          isScrollControlled: true,
                          builder: (context) => VideoScreen(movie: movie),
                        );
                      },
                      label: 'Watch Video',
                    ),
                  ),
                ),
              const SizedBox(height: 8.0),
            ],
          ),
        );
      },
    );
  }
}
