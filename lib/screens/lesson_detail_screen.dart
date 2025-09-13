import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../models/topic.dart';
import 'video_player_screen.dart';

class LessonDetailScreen extends StatefulWidget {
  final Topic topic;

  const LessonDetailScreen({
    super.key,
    required this.topic,
  });

  @override
  State<LessonDetailScreen> createState() => _LessonDetailScreenState();
}

class _LessonDetailScreenState extends State<LessonDetailScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          widget.topic.title,
          style: GoogleFonts.inter(fontWeight: FontWeight.w600),
        ),
        backgroundColor: const Color(0xFF23514C),
      ),
      body: Column(
        children: [
          // Topic Header
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [
                  Color(0xFF1B5E20),
                  Color(0xFF23514C),
                ],
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  widget.topic.title,
                  style: GoogleFonts.inter(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  widget.topic.description,
                  style: GoogleFonts.inter(
                    fontSize: 16,
                    color: Colors.white70,
                  ),
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    _buildInfoChip(
                      Icons.video_library,
                      '${widget.topic.lessonsCount} lessons',
                    ),
                    const SizedBox(width: 12),
                    _buildInfoChip(
                      Icons.access_time,
                      widget.topic.duration,
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Lessons List
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _getSampleLessons().length,
              itemBuilder: (context, index) {
                final lesson = _getSampleLessons()[index];
                return _buildLessonCard(lesson, index);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoChip(IconData icon, String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.2),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            icon,
            size: 16,
            color: Colors.white,
          ),
          const SizedBox(width: 6),
          Text(
            text,
            style: GoogleFonts.inter(
              fontSize: 12,
              color: Colors.white,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLessonCard(Lesson lesson, int index) {
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
                relatedLessons: _getSampleLessons(),
              ),
            ),
          );
        },
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              // Lesson Number
              Container(
                width: 50,
                height: 50,
                decoration: BoxDecoration(
                  color: const Color(0xFF23514C),
                  borderRadius: BorderRadius.circular(25),
                ),
                child: Center(
                  child: Text(
                    '${index + 1}',
                    style: GoogleFonts.inter(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),

              const SizedBox(width: 16),

              // Lesson Content
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
                        if (lesson.referenceFileUrl != null) ...[
                          const SizedBox(width: 16),
                          Icon(
                            Icons.attach_file,
                            size: 16,
                            color: Colors.grey[600],
                          ),
                          const SizedBox(width: 4),
                          Text(
                            'Reference',
                            style: GoogleFonts.inter(
                              fontSize: 12,
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),

              // Play Button
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: const Color(0xFF23514C).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Icon(
                  Icons.play_arrow,
                  color: Color(0xFF23514C),
                  size: 24,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  List<Lesson> _getSampleLessons() {
    // Generate sample lessons based on topic
    return List.generate(widget.topic.lessonsCount, (index) {
      return Lesson(
        id: '${widget.topic.id}-${index + 1}',
        title: 'Lesson ${index + 1}: ${_getLessonTitle(index)}',
        description: _getLessonDescription(index),
        videoUrl:
            'https://sample-video-url.com/${widget.topic.id}-${index + 1}',
        thumbnailUrl: '',
        duration: _generateDuration(index),
        lessonNumber: index + 1,
        referenceFileUrl: index % 2 == 0
            ? 'https://sample-pdf.com/reference-${index + 1}.pdf'
            : null,
      );
    });
  }

  String _getLessonTitle(int index) {
    switch (widget.topic.id) {
      case '1': // Prayer Basics
        final prayerTitles = [
          'Introduction to Salah',
          'Importance of Prayer',
          'Times of Prayer',
          'Preparing for Prayer',
          'Steps of Prayer',
          'Common Mistakes',
          'Du\'a and Dhikr',
          'Prayer in Congregation',
        ];
        return prayerTitles[index % prayerTitles.length];

      case '2': // Quran Recitation
        final quranTitles = [
          'Arabic Alphabet',
          'Basic Pronunciation',
          'Tajweed Rules',
          'Short Surahs',
          'Reading Practice',
          'Memorization Tips',
          'Beautiful Recitation',
          'Understanding Meanings',
        ];
        return quranTitles[index % quranTitles.length];

      default:
        return 'Introduction and Basics';
    }
  }

  String _getLessonDescription(int index) {
    return 'Comprehensive lesson covering important aspects of ${widget.topic.title.toLowerCase()}. This lesson will help you understand the key concepts and practical applications.';
  }

  String _generateDuration(int index) {
    final durations = [
      '15:30',
      '12:45',
      '18:20',
      '22:10',
      '16:55',
      '20:30',
      '14:15',
      '25:40'
    ];
    return durations[index % durations.length];
  }
}
