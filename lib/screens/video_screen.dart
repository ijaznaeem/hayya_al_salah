import 'package:flutter/material.dart';
import 'package:hayya_al_salah/models/movie.dart';
import 'package:hayya_al_salah/screens/player_widget.dart';
import 'package:hayya_al_salah/widgets/appBr.dart';

class VideoScreen extends StatefulWidget {
  final Movie movie;

  const VideoScreen({Key? key, required this.movie}) : super(key: key);

  @override
  _VideoScreenState createState() => _VideoScreenState();
}

class _VideoScreenState extends State<VideoScreen> {
  bool isPlaying = false;
  bool isFavorite = false;

  @override
  void initState() {
    super.initState();

    // _controller =
    //     VideoPlayerController.networkUrl(Uri.parse(widget.movie.videoFile))
    //       ..initialize().then((_) {
    //         setState(() {
    //           _controller.play();
    //           _onVideoStarted();
    //         });
    //       });
  }

  @override
  void dispose() {
    // _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: widget.movie.title),
      body: SingleChildScrollView(
        child: Container(
          color: const Color(0xFFD5D594),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Stack(
                alignment: Alignment.center,
                children: [
                  AspectRatio(
                    aspectRatio: 1, // Set aspect ratio to 1:1
                    child: DefaultPlayer(videoUrl: widget.movie.videoFile),
                  ),
                  // if (!isPlaying)
                  //   Image.network(
                  //     widget.movie.image, // Assuming movie has a image property
                  //     fit: BoxFit.cover,
                  //     width: double.infinity,
                  //     height: 392.7, // Set a finite height
                  //     alignment: Alignment.center,
                  //     filterQuality: FilterQuality.medium,
                  //   ),
                ],
              ),
              Padding(
                padding: const EdgeInsets.all(8.0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      flex: 5,
                      child: Text(
                        widget.movie.title,
                        style: const TextStyle(
                            fontSize: 24, fontWeight: FontWeight.bold),
                      ),
                    ),
                    Expanded(
                      flex: 2,
                      child: IconButton(
                        icon: const Icon(Icons.download_rounded),
                        color: Colors.red,
                        onPressed: () {
                          // Add share functionality here
                        },
                      ),
                    ),
                    Expanded(
                      flex: 2,
                      child: IconButton(
                        icon: const Icon(Icons.run_circle_rounded),
                        color: Color(0xFF656500),
                        onPressed: () {
                          // Add download functionality here
                        },
                      ),
                    ),
                  ],
                ),
              ),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 8.0),
                child: Text(widget.movie.description),
              ),
              Padding(
                padding: const EdgeInsets.all(8.0),
                child: IconButton(
                  icon: Icon(
                    isFavorite ? Icons.favorite : Icons.favorite_border,
                    color: isFavorite ? Colors.red : null,
                  ),
                  onPressed: () {
                    setState(() {
                      isFavorite = !isFavorite;
                    });
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
//https://github.com/GeekyAnts/flick-video-player-demo-videos/blob/master/example/the_valley_compressed.mp4?raw=true