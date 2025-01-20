import 'package:flick_video_player/flick_video_player.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:video_player/video_player.dart';
import 'package:visibility_detector/visibility_detector.dart';

class DefaultPlayer extends StatefulWidget {
  final String videoUrl;
  DefaultPlayer({Key? key, required this.videoUrl}) : super(key: key);

  @override
  _DefaultPlayerState createState() => _DefaultPlayerState();
}

class _DefaultPlayerState extends State<DefaultPlayer> {
  late FlickManager flickManager;

  @override
  void initState() {
    super.initState();
    flickManager = FlickManager(
      videoPlayerController: VideoPlayerController.networkUrl(
        Uri.parse(widget.videoUrl),
      ),
    );
  }

  @override
  void dispose() {
    flickManager.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return PlayerWidget(flickManager: flickManager);
  }
}

class PlayerWidget extends StatelessWidget {
  final FlickManager flickManager;

  const PlayerWidget({Key? key, required this.flickManager}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return VisibilityDetector(
      key: ObjectKey(flickManager),
      onVisibilityChanged: (visibility) {
        if (visibility.visibleFraction == 0) {
          flickManager.flickControlManager?.autoPause();
        } else if (visibility.visibleFraction == 1) {
          flickManager.flickControlManager?.autoResume();
        }
      },
      child: Container(
        width: MediaQuery.of(context).size.width,
        child: FlickVideoPlayer(
          flickManager: flickManager,
          preferredDeviceOrientationFullscreen: const [
            DeviceOrientation.portraitUp,
          ],
          flickVideoWithControls: FlickVideoWithControls(
            controls: CustomPortraitControls(flickManager: flickManager),
          ),
          flickVideoWithControlsFullscreen: FlickVideoWithControls(
            controls: FlickLandscapeControls(),
          ),
        ),
      ),
    );
  }
}

class CustomPortraitControls extends StatefulWidget {
  final FlickManager flickManager;

  const CustomPortraitControls({Key? key, required this.flickManager})
      : super(key: key);

  @override
  _CustomPortraitControlsState createState() => _CustomPortraitControlsState();
}

class _CustomPortraitControlsState extends State<CustomPortraitControls> {
  @override
  Widget build(BuildContext context) {
    return FlickShowControlsAction(
      child: Center(
        child: FlickAutoHideChild(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  IconButton(
                    iconSize: 36.0, // Increased size
                    icon: const Icon(Icons.replay_10, color: Colors.white),
                    onPressed: () {
                      final currentPosition = widget
                          .flickManager
                          .flickVideoManager
                          ?.videoPlayerController
                          ?.value
                          .position;
                      if (currentPosition != null) {
                        widget.flickManager.flickControlManager?.seekTo(
                          currentPosition - const Duration(seconds: 10),
                        );
                      }
                    },
                  ),
                  IconButton(
                    iconSize: 36.0, // Increased size
                    icon: Icon(
                      widget.flickManager.flickVideoManager?.isPlaying == true
                          ? Icons.pause
                          : Icons.play_arrow,
                      color: Colors.white,
                    ),
                    onPressed: () {
                      setState(() {
                        widget.flickManager.flickControlManager?.togglePlay();
                      });
                    },
                  ),
                  IconButton(
                    iconSize: 36.0, // Increased size
                    icon: const Icon(Icons.forward_10, color: Colors.white),
                    onPressed: () {
                      final currentPosition = widget
                          .flickManager
                          .flickVideoManager
                          ?.videoPlayerController
                          ?.value
                          .position;
                      final duration = widget.flickManager.flickVideoManager
                          ?.videoPlayerController?.value.duration;
                      if (currentPosition != null && duration != null) {
                        widget.flickManager.flickControlManager?.seekTo(
                          currentPosition + const Duration(seconds: 10),
                        );
                      }
                    },
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
