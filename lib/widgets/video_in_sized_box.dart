import 'package:chewie/chewie.dart';
import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';

class VideoInSizedBox extends StatefulWidget {
  final String videoUrl;
  final String bannerUrl;

  const VideoInSizedBox(
      {super.key, required this.videoUrl, required this.bannerUrl});
  @override
  _VideoInSizedBoxState createState() => _VideoInSizedBoxState();
}

class _VideoInSizedBoxState extends State<VideoInSizedBox> {
  late VideoPlayerController _videoPlayerController;
  ChewieController? _chewieController;

  @override
  void initState() {
    super.initState();
    // Initialize VideoPlayerController
    _videoPlayerController = VideoPlayerController.networkUrl(
      Uri.parse(widget.videoUrl),
    );

    // Initialize ChewieController
    _videoPlayerController.initialize().then((_) {
      setState(() {
        _chewieController = ChewieController(
          videoPlayerController: _videoPlayerController,
          autoPlay: true,
          looping: true,
        );
      });
    });
  }

  @override
  void dispose() {
    _videoPlayerController.dispose();
    _chewieController?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Center(
      child: _videoPlayerController.value.isInitialized &&
              _chewieController != null
          ? AspectRatio(
              aspectRatio: _videoPlayerController.value.aspectRatio,
              child: Chewie(
                controller: _chewieController!,
              ),
            )
          : Container(
              height: 200,
              padding: const EdgeInsets.all(10),
              width: MediaQuery.of(context).size.width / 1.05,
              decoration: BoxDecoration(
                  image: DecorationImage(
                    image: NetworkImage(widget.bannerUrl),
                    fit: BoxFit.cover,
                  ),
                  color: Colors.white.withOpacity(0.3),
                  borderRadius: BorderRadius.circular(5)),
              child: const Center(
                child: CircularProgressIndicator(),
              ),
            ),
    );
  }
}
