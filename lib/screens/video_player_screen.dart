import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

// import 'package:video_player/video_player.dart';
// import 'package:chewie/chewie.dart';
import '../models/topic.dart';
import '../services/favorites_service.dart';

class VideoPlayerScreen extends StatefulWidget {
  final Lesson lesson;
  final List<Lesson> relatedLessons;

  const VideoPlayerScreen({
    super.key,
    required this.lesson,
    required this.relatedLessons,
  });

  @override
  State<VideoPlayerScreen> createState() => _VideoPlayerScreenState();
}

class _VideoPlayerScreenState extends State<VideoPlayerScreen> {
  // VideoPlayerController? _videoController;
  // ChewieController? _chewieController;
  bool _isPlayerReady = false;
  bool _isFavorite = false;
  final FavoritesService _favoritesService = FavoritesService.instance;

  @override
  void initState() {
    super.initState();
    _initializePlayer();
    _checkFavoriteStatus();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    // Refresh favorite status when returning to this screen
    _checkFavoriteStatus();
  }

  void _checkFavoriteStatus() async {
    final isFavorite = await _favoritesService.isFavorite(widget.lesson.id);
    setState(() {
      _isFavorite = isFavorite;
    });
  }

  void _toggleFavorite() async {
    if (_isFavorite) {
      // Remove from favorites
      final success =
          await _favoritesService.removeFromFavorites(widget.lesson.id);
      if (success) {
        setState(() {
          _isFavorite = false;
        });
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Removed from favorites'),
              backgroundColor: Color(0xFF23514C),
            ),
          );
        }
      }
    } else {
      // Add to favorites
      final success = await _favoritesService.addToFavorites(widget.lesson);
      if (success) {
        setState(() {
          _isFavorite = true;
        });
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Added to favorites'),
              backgroundColor: Color(0xFF23514C),
            ),
          );
        }
      }
    }
  }

  void _initializePlayer() {
    // For now, we'll simulate video player initialization
    // In a real app, you would initialize the video player here
    setState(() {
      _isPlayerReady = true;
    });

    // Uncomment when video_player is available:
    // _videoController = VideoPlayerController.network(widget.lesson.videoUrl)
    //   ..initialize().then((_) {
    //     _chewieController = ChewieController(
    //       videoPlayerController: _videoController!,
    //       autoPlay: true,
    //       looping: false,
    //       materialProgressColors: ChewieProgressColors(
    //         playedColor: const Color(0xFF23514C),
    //         handleColor: const Color(0xFF23514C),
    //         backgroundColor: Colors.grey,
    //       ),
    //     );
    //     setState(() {
    //       _isPlayerReady = true;
    //     });
    //   });
  }

  @override
  void dispose() {
    // _chewieController?.dispose();
    // _videoController?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        iconTheme: const IconThemeData(color: Colors.white),
        title: Text(
          widget.lesson.title,
          style: GoogleFonts.inter(
            color: Colors.white,
            fontWeight: FontWeight.w600,
          ),
        ),
        actions: [
          if (widget.lesson.referenceFileUrl != null)
            IconButton(
              icon: const Icon(Icons.download, color: Colors.white),
              onPressed: _downloadReference,
            ),
          IconButton(
            icon: const Icon(Icons.share, color: Colors.white),
            onPressed: _shareLesson,
          ),
        ],
      ),
      body: Column(
        children: [
          // Video Player Section
          Container(
            width: double.infinity,
            height: 250,
            color: Colors.black,
            child: _isPlayerReady
                ? _buildVideoPlayer()
                : const Center(
                    child: CircularProgressIndicator(
                      color: Color(0xFF23514C),
                    ),
                  ),
          ),

          // Content Section
          Expanded(
            child: Container(
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(20),
                  topRight: Radius.circular(20),
                ),
              ),
              child: Column(
                children: [
                  // Lesson Info
                  _buildLessonInfo(),

                  // Related Lessons
                  Expanded(
                    child: _buildRelatedLessons(),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildVideoPlayer() {
    // For demo purposes, show a placeholder
    // In a real app, return Chewie(controller: _chewieController!)
    return Container(
      color: Colors.black,
      child: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.play_circle_outline,
              size: 80,
              color: Colors.white,
            ),
            const SizedBox(height: 16),
            Text(
              'Video Player',
              style: GoogleFonts.inter(
                color: Colors.white,
                fontSize: 18,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Duration: ${widget.lesson.duration}',
              style: GoogleFonts.inter(
                color: Colors.white70,
                fontSize: 14,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLessonInfo() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Lesson Title
          Text(
            widget.lesson.title,
            style: GoogleFonts.inter(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 8),

          // Lesson Description
          Text(
            widget.lesson.description,
            style: GoogleFonts.inter(
              fontSize: 16,
              color: Colors.grey[700],
              height: 1.5,
            ),
          ),
          const SizedBox(height: 16),

          // Lesson Meta Info
          Row(
            children: [
              _buildMetaInfo(Icons.play_circle_outline, widget.lesson.duration),
              const SizedBox(width: 20),
              _buildMetaInfo(Icons.format_list_numbered,
                  'Lesson ${widget.lesson.lessonNumber}'),
              if (widget.lesson.referenceFileUrl != null) ...[
                const SizedBox(width: 20),
                _buildMetaInfo(Icons.attach_file, 'Reference'),
              ],
              const Spacer(),
              // Add to Favorites Button
              IconButton(
                onPressed: _toggleFavorite,
                icon: Icon(
                  _isFavorite ? Icons.favorite : Icons.favorite_border,
                  color: _isFavorite ? Colors.red : Colors.grey[600],
                ),
                tooltip:
                    _isFavorite ? 'Remove from Favorites' : 'Add to Favorites',
              ),
            ],
          ),

          // Download Reference Button
          if (widget.lesson.referenceFileUrl != null) ...[
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _downloadReference,
                icon: const Icon(Icons.download),
                label: const Text('Download Reference Material'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF23514C),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildMetaInfo(IconData icon, String text) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(
          icon,
          size: 16,
          color: Colors.grey[600],
        ),
        const SizedBox(width: 4),
        Text(
          text,
          style: GoogleFonts.inter(
            fontSize: 12,
            color: Colors.grey[600],
          ),
        ),
      ],
    );
  }

  Widget _buildRelatedLessons() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Related Lessons',
            style: GoogleFonts.inter(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 16),
          Expanded(
            child: ListView.builder(
              itemCount: widget.relatedLessons.length
                  .clamp(0, 5), // Show max 5 related lessons
              itemBuilder: (context, index) {
                final lesson = widget.relatedLessons[index];
                if (lesson.id == widget.lesson.id) {
                  return const SizedBox.shrink();
                }
                return _buildRelatedLessonCard(lesson);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRelatedLessonCard(Lesson lesson) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.grey[50],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[200]!),
      ),
      child: InkWell(
        onTap: () {
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(
              builder: (context) => VideoPlayerScreen(
                lesson: lesson,
                relatedLessons: widget.relatedLessons,
              ),
            ),
          );
        },
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              // Thumbnail
              Container(
                width: 60,
                height: 45,
                decoration: BoxDecoration(
                  color: const Color(0xFF23514C),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(
                  Icons.play_arrow,
                  color: Colors.white,
                  size: 24,
                ),
              ),

              const SizedBox(width: 12),

              // Lesson Info
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      lesson.title,
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                        color: Colors.black87,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      lesson.duration,
                      style: GoogleFonts.inter(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _downloadReference() {
    // Implement reference file download
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Downloading reference material...'),
        backgroundColor: Color(0xFF23514C),
      ),
    );
  }

  void _shareLesson() {
    // Implement lesson sharing
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Sharing lesson...'),
        backgroundColor: Color(0xFF23514C),
      ),
    );
  }
}
