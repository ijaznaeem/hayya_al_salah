import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../models/topic.dart';
import 'lesson_detail_screen.dart';

class TopicsScreen extends StatefulWidget {
  const TopicsScreen({super.key});

  @override
  State<TopicsScreen> createState() => _TopicsScreenState();
}

class _TopicsScreenState extends State<TopicsScreen> {
  final TextEditingController _searchController = TextEditingController();
  List<Topic> _filteredTopics = [];
  List<Topic> _allTopics = [];

  @override
  void initState() {
    super.initState();
    _loadTopics();
  }

  void _loadTopics() {
    _allTopics = _getSampleTopics();
    _filteredTopics = _allTopics;
  }

  void _filterTopics(String query) {
    setState(() {
      if (query.isEmpty) {
        _filteredTopics = _allTopics;
      } else {
        _filteredTopics = _allTopics
            .where((topic) =>
                topic.title.toLowerCase().contains(query.toLowerCase()) ||
                topic.description.toLowerCase().contains(query.toLowerCase()))
            .toList();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Islamic Topics',
          style: GoogleFonts.inter(fontWeight: FontWeight.w600),
        ),
        backgroundColor: const Color(0xFF23514C),
      ),
      body: Column(
        children: [
          // Search Bar
          Container(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              onChanged: _filterTopics,
              decoration: InputDecoration(
                hintText: 'Search topics...',
                prefixIcon: const Icon(Icons.search),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
                filled: true,
                fillColor: Colors.white,
                contentPadding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
            ),
          ),

          // Topics List
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              itemCount: _filteredTopics.length,
              itemBuilder: (context, index) {
                final topic = _filteredTopics[index];
                return _buildTopicCard(topic);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTopicCard(Topic topic) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
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
              builder: (context) => LessonDetailScreen(topic: topic),
            ),
          );
        },
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              // Topic Image/Icon
              Container(
                width: 80,
                height: 80,
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
                  size: 40,
                  color: Colors.white,
                ),
              ),

              const SizedBox(width: 16),

              // Topic Info
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      topic.title,
                      style: GoogleFonts.inter(
                        fontSize: 18,
                        fontWeight: FontWeight.w600,
                        color: Colors.black87,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      topic.description,
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
                          Icons.video_library,
                          size: 16,
                          color: Colors.grey[600],
                        ),
                        const SizedBox(width: 4),
                        Text(
                          '${topic.lessonsCount} lessons',
                          style: GoogleFonts.inter(
                            fontSize: 12,
                            color: Colors.grey[600],
                          ),
                        ),
                        const SizedBox(width: 16),
                        Icon(
                          Icons.access_time,
                          size: 16,
                          color: Colors.grey[600],
                        ),
                        const SizedBox(width: 4),
                        Text(
                          topic.duration,
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

              // Arrow Icon
              const Icon(
                Icons.arrow_forward_ios,
                size: 16,
                color: Colors.grey,
              ),
            ],
          ),
        ),
      ),
    );
  }

  List<Topic> _getSampleTopics() {
    return [
      Topic(
        id: '1',
        title: 'Prayer (Salah) Basics',
        description:
            'Learn the fundamental principles and steps of Islamic prayer including ablution, prayer times, and proper techniques.',
        imageUrl: '',
        lessonsCount: 8,
        duration: '2 hours 30 minutes',
        lessons: _getPrayerLessons(),
      ),
      Topic(
        id: '2',
        title: 'Quran Recitation (Tajweed)',
        description:
            'Master the art of beautiful Quranic recitation with proper pronunciation and tajweed rules.',
        imageUrl: '',
        lessonsCount: 12,
        duration: '4 hours 15 minutes',
        lessons: _getQuranLessons(),
      ),
      Topic(
        id: '3',
        title: 'Islamic History',
        description:
            'Explore the rich history of Islam from the time of Prophet Muhammad to the modern era.',
        imageUrl: '',
        lessonsCount: 15,
        duration: '6 hours',
        lessons: _getHistoryLessons(),
      ),
      Topic(
        id: '4',
        title: 'Islamic Ethics (Akhlaq)',
        description:
            'Learn about Islamic moral principles and how to apply them in daily life.',
        imageUrl: '',
        lessonsCount: 10,
        duration: '3 hours 45 minutes',
        lessons: _getEthicsLessons(),
      ),
      Topic(
        id: '5',
        title: 'Prophetic Traditions (Hadith)',
        description:
            'Study the sayings and actions of Prophet Muhammad and their significance.',
        imageUrl: '',
        lessonsCount: 14,
        duration: '5 hours 20 minutes',
        lessons: _getHadithLessons(),
      ),
      Topic(
        id: '6',
        title: 'Islamic Jurisprudence (Fiqh)',
        description:
            'Understanding Islamic law and its practical applications in modern life.',
        imageUrl: '',
        lessonsCount: 18,
        duration: '7 hours 30 minutes',
        lessons: _getFiqhLessons(),
      ),
    ];
  }

  List<Lesson> _getPrayerLessons() {
    return [
      Lesson(
        id: '1-1',
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
        id: '1-2',
        title: 'Ablution (Wudu)',
        description: 'Step-by-step guide to performing ablution correctly',
        videoUrl: 'https://sample-video-url.com/prayer2',
        thumbnailUrl: '',
        duration: '12:45',
        lessonNumber: 2,
        referenceFileUrl: 'https://sample-pdf.com/wudu-guide.pdf',
      ),
      // Add more lessons...
    ];
  }

  List<Lesson> _getQuranLessons() {
    return [
      Lesson(
        id: '2-1',
        title: 'Arabic Alphabet',
        description: 'Learn the Arabic letters and their pronunciation',
        videoUrl: 'https://sample-video-url.com/quran1',
        thumbnailUrl: '',
        duration: '20:15',
        lessonNumber: 1,
        referenceFileUrl: 'https://sample-pdf.com/arabic-alphabet.pdf',
      ),
      // Add more lessons...
    ];
  }

  List<Lesson> _getHistoryLessons() {
    return [
      Lesson(
        id: '3-1',
        title: 'Life of Prophet Muhammad',
        description: 'Early life and prophethood of Muhammad (PBUH)',
        videoUrl: 'https://sample-video-url.com/history1',
        thumbnailUrl: '',
        duration: '25:30',
        lessonNumber: 1,
        referenceFileUrl: 'https://sample-pdf.com/prophet-biography.pdf',
      ),
      // Add more lessons...
    ];
  }

  List<Lesson> _getEthicsLessons() {
    return [
      Lesson(
        id: '4-1',
        title: 'Good Character in Islam',
        description: 'Understanding the importance of good character',
        videoUrl: 'https://sample-video-url.com/ethics1',
        thumbnailUrl: '',
        duration: '18:20',
        lessonNumber: 1,
        referenceFileUrl: 'https://sample-pdf.com/islamic-ethics.pdf',
      ),
      // Add more lessons...
    ];
  }

  List<Lesson> _getHadithLessons() {
    return [
      Lesson(
        id: '5-1',
        title: 'Introduction to Hadith',
        description: 'Understanding the science of Hadith',
        videoUrl: 'https://sample-video-url.com/hadith1',
        thumbnailUrl: '',
        duration: '22:10',
        lessonNumber: 1,
        referenceFileUrl: 'https://sample-pdf.com/hadith-science.pdf',
      ),
      // Add more lessons...
    ];
  }

  List<Lesson> _getFiqhLessons() {
    return [
      Lesson(
        id: '6-1',
        title: 'Introduction to Islamic Law',
        description: 'Basic principles of Islamic jurisprudence',
        videoUrl: 'https://sample-video-url.com/fiqh1',
        thumbnailUrl: '',
        duration: '28:45',
        lessonNumber: 1,
        referenceFileUrl: 'https://sample-pdf.com/fiqh-basics.pdf',
      ),
      // Add more lessons...
    ];
  }
}
