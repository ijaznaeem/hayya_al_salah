import 'package:flutter/material.dart';
import 'package:hayya_al_salah/models/movie.dart';
import 'package:hayya_al_salah/widgets/appBr.dart';
import 'package:hayya_al_salah/widgets/video_in_sized_box.dart';
import 'package:url_launcher/url_launcher.dart'; // Import url_launcher package

import '../helpers/shared_pref_helper.dart';
import '../utilities/snack_util.dart';
import '../widgets/custom_button.dart';

class VideoScreen extends StatefulWidget {
  final Movie movie;

  const VideoScreen({Key? key, required this.movie}) : super(key: key);

  @override
  _VideoScreenState createState() => _VideoScreenState();
}

class _VideoScreenState extends State<VideoScreen> {
  bool isPlaying = false;
  bool isFavorite = false;
  final SharedPrefHelper _prefHelper = SharedPrefHelper();

  @override
  void initState() {
    super.initState();
    checkIfFavorite();
  }

  Future<void> checkIfFavorite() async {
    final List<Movie> favoriteMovies = await _prefHelper.getMovieList();
    setState(() {
      isFavorite =
          favoriteMovies.any((movie) => movie.movieID == widget.movie.movieID);
    });
  }

  Future<void> _addObject(Movie m) async {
    await _prefHelper.addMovie(m);
  }

  Future<void> _removeObject(int id) async {
    await _prefHelper.removeMovie(id);
  }

  Future<void> _downloadFile(String url, String fileName) async {
    final Uri uri = Uri.parse(url);
    await launchUrl(
      uri,
      mode: LaunchMode.externalApplication,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
        appBar: CustomAppBar(title: widget.movie.title),
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
                VideoInSizedBox(
                  videoUrl: widget.movie.videoFile,
                  bannerUrl: widget.movie.image,
                ),
                const SizedBox(height: 10),
                Container(
                    padding: const EdgeInsets.only(top: 20, bottom: 20),
                    width: MediaQuery.of(context).size.width / 1.1,
                    decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.3),
                        borderRadius: BorderRadius.circular(20)),
                    child: Text(
                      widget.movie.title,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                          fontSize: 16, fontWeight: FontWeight.bold),
                    )),
                const SizedBox(height: 6),
                Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: CustomButton(
                            icon: Icons.favorite_rounded,
                            label: "Favorite",
                            isNormal: !isFavorite,
                            onPressed: () {
                              setState(() {
                                isFavorite = !isFavorite;
                                if (isFavorite) {
                                  _addObject(widget.movie);
                                  showSnack(context, 'Added to favorites');
                                } else {
                                  _removeObject(widget.movie.movieID);
                                  showSnack(context, 'Removed from favorites');
                                }
                              });
                            }),
                      ),
                      const SizedBox(width: 10), // Adds 5 pixel space
                      Expanded(
                        child: CustomButton(
                            icon: Icons.download_rounded,
                            label: "Download PDF",
                            isNormal: true,
                            onPressed: () {
                              _downloadFile(
                                  "https://salah.pakperegrine.com/apis/uploads/${widget.movie.pdfFile}",
                                  '${widget.movie.title}.pdf');
                            }),
                      ),
                    ],
                  ),
                ),
                if (widget.movie.animationFile.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.fromLTRB(8, 0, 8, 8),
                    child: Row(
                      children: [
                        Expanded(
                          child: CustomButton(
                            icon: Icons.animation_rounded,
                            label: "Animation",
                            isNormal: true,
                            onPressed: () {
                              // Add animation functionality here
                            },
                          ),
                        ),
                      ],
                    ),
                  ),
                Container(
                  padding: const EdgeInsets.all(8),
                  // _formKey!.currentState!.validate() ? 200 : 600,
                  // height: isEmailCorrect ? 260 : 182,
                  width: MediaQuery.of(context).size.width / 1.1,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    widget.movie.description,
                    style: const TextStyle(fontSize: 12),
                  ),
                ),
              ],
            )));
  }
}
//https://github.com/GeekyAnts/flick-video-player-demo-videos/blob/master/example/the_valley_compressed.mp4?raw=true

