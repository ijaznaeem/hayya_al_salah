import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../models/topic.dart';
import 'video_player_screen.dart';

class FavoritesScreen extends StatefulWidget {
  const FavoritesScreen({super.key});

  @override
  State<FavoritesScreen> createState() => _FavoritesScreenState();
}

class _FavoritesScreenState extends State<FavoritesScreen> {
  List<Lesson> _favoriteLessons = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadFavorites();
  }

  void _loadFavorites() {
    // Simulate loading favorites from storage
    Future.delayed(const Duration(seconds: 1), () {
      setState(() {
        _favoriteLessons = _getSampleFavorites();
        _isLoading = false;
      });
    });
  }

  void _removeFavorite(String lessonId) {
    setState(() {
      _favoriteLessons.removeWhere((lesson) => lesson.id == lessonId);
    });

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Removed from favorites'),
        backgroundColor: Color(0xFF23514C),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Favorites',
          style: GoogleFonts.inter(fontWeight: FontWeight.w600),
        ),
        backgroundColor: const Color(0xFF23514C),
        actions: [
          if (_favoriteLessons.isNotEmpty)
            IconButton(
              icon: const Icon(Icons.clear_all),
              onPressed: _clearAllFavorites,
              tooltip: 'Clear all favorites',
            ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(
                color: Color(0xFF23514C),
              ),
            )
          : _favoriteLessons.isEmpty
              ? _buildEmptyState()
              : _buildFavoritesList(),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.favorite_outline,
            size: 80,
            color: Colors.grey[400],
          ),
          const SizedBox(height: 16),
          Text(
            'No Favorites Yet',
            style: GoogleFonts.inter(
              fontSize: 20,
              fontWeight: FontWeight.w600,
              color: Colors.grey[600],
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Your favorite lessons will appear here',
            style: GoogleFonts.inter(
              fontSize: 16,
              color: Colors.grey[500],
            ),
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () {
              // Navigate to topics screen
              DefaultTabController.of(context).animateTo(1);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF23514C),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: const Text('Explore Topics'),
          ),
        ],
      ),
    );
  }

  Widget _buildFavoritesList() {
    return Column(
      children: [
        // Header
        Container(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Icon(
                Icons.favorite,
                color: Colors.red[400],
              ),
              const SizedBox(width: 8),
              Text(
                '${_favoriteLessons.length} Favorite Lessons',
                style: GoogleFonts.inter(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: Colors.black87,
                ),
              ),
            ],
          ),
        ),

        // Favorites List
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            itemCount: _favoriteLessons.length,
            itemBuilder: (context, index) {
              final lesson = _favoriteLessons[index];
              return _buildFavoriteCard(lesson);
            },
          ),
        ),
      ],
    );
  }

  Widget _buildFavoriteCard(Lesson lesson) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: InkWell(
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => VideoPlayerScreen(
                lesson: lesson,
                relatedLessons: _favoriteLessons,
              ),
            ),
          );
        },
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              // Thumbnail
              Container(
                width: 80,
                height: 60,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [
                      Color(0xFF1B5E20),
                      Color(0xFF23514C),
                    ],
                  ),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.play_circle_outline,
                  size: 30,
                  color: Colors.white,
                ),
              ),

              const SizedBox(width: 16),

              // Lesson Info
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      lesson.title,
                      style: GoogleFonts.inter(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: Colors.black87,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      lesson.description,
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        color: Colors.grey[600],
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(
                          Icons.play_circle_outline,
                          size: 16,
                          color: Colors.grey[600],
                        ),
                        const SizedBox(width: 4),
                        Text(
                          lesson.duration,
                          style: GoogleFonts.inter(
                            fontSize: 12,
                            color: Colors.grey[600],
                          ),
                        ),
                        const SizedBox(width: 16),
                        Icon(
                          Icons.format_list_numbered,
                          size: 16,
                          color: Colors.grey[600],
                        ),
                        const SizedBox(width: 4),
                        Text(
                          'Lesson ${lesson.lessonNumber}',
                          style: GoogleFonts.inter(
                            fontSize: 12,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              // Action Buttons
              Column(
                children: [
                  IconButton(
                    onPressed: () => _removeFavorite(lesson.id),
                    icon: Icon(
                      Icons.favorite,
                      color: Colors.red[400],
                    ),
                    tooltip: 'Remove from favorites',
                  ),
                  IconButton(
                    onPressed: () => _shareLesson(lesson),
                    icon: Icon(
                      Icons.share,
                      color: Colors.grey[600],
                    ),
                    tooltip: 'Share lesson',
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _clearAllFavorites() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: Text(
            'Clear All Favorites',
            style: GoogleFonts.inter(fontWeight: FontWeight.w600),
          ),
          content: Text(
            'Are you sure you want to remove all lessons from your favorites?',
            style: GoogleFonts.inter(),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('Cancel'),
            ),
            TextButton(
              onPressed: () {
                setState(() {
                  _favoriteLessons.clear();
                });
                Navigator.of(context).pop();
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('All favorites cleared'),
                    backgroundColor: Color(0xFF23514C),
                  ),
                );
              },
              child: const Text(
                'Clear All',
                style: TextStyle(color: Colors.red),
              ),
            ),
          ],
        );
      },
    );
  }

  void _shareLesson(Lesson lesson) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Sharing: ${lesson.title}'),
        backgroundColor: const Color(0xFF23514C),
      ),
    );
  }

  List<Lesson> _getSampleFavorites() {
    return [
      Lesson(
        id: 'fav-1',
        title: 'Introduction to Prayer',
        description:
            'Understanding the importance and significance of prayer in Islam',
        videoUrl: 'https://sample-video-url.com/prayer1',
        thumbnailUrl: '',
        duration: '15:30',
        lessonNumber: 1,
        referenceFileUrl: 'https://sample-pdf.com/prayer-basics.pdf',
      ),
      Lesson(
        id: 'fav-2',
        title: 'Arabic Alphabet',
        description: 'Learn the Arabic letters and their pronunciation',
        videoUrl: 'https://sample-video-url.com/quran1',
        thumbnailUrl: '',
        duration: '20:15',
        lessonNumber: 1,
        referenceFileUrl: 'https://sample-pdf.com/arabic-alphabet.pdf',
      ),
      Lesson(
        id: 'fav-3',
        title: 'Life of Prophet Muhammad',
        description: 'Early life and prophethood of Muhammad (PBUH)',
        videoUrl: 'https://sample-video-url.com/history1',
        thumbnailUrl: '',
        duration: '25:30',
        lessonNumber: 1,
        referenceFileUrl: 'https://sample-pdf.com/prophet-biography.pdf',
      ),
    ];
  }
}
