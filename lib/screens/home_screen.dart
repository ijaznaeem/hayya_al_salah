import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../models/quran_verse.dart';
import '../models/topic.dart';
import '../services/islamic_calendar_service.dart';
import '../services/quran_api_service.dart';
import 'lesson_detail_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final QuranApiService _quranService = QuranApiService();
  final IslamicCalendarService _calendarService = IslamicCalendarService();

  QuranVerse? _currentVerse;
  bool _isLoadingVerse = true;
  Map<String, String> _currentDate = {};

  @override
  void initState() {
    super.initState();
    _loadCurrentDate();
    _loadRandomVerse();
  }

  void _loadCurrentDate() {
    setState(() {
      _currentDate = _calendarService.getCurrentDate();
    });
  }

  Future<void> _loadRandomVerse() async {
    setState(() {
      _isLoadingVerse = true;
    });

    try {
      final verse = await _quranService.getRandomVerse();
      setState(() {
        _currentVerse = verse;
        _isLoadingVerse = false;
      });
    } catch (e) {
      setState(() {
        _isLoadingVerse = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Failed to load verse')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Hayya Al Salah',
          style: GoogleFonts.inter(fontWeight: FontWeight.w600),
        ),
        backgroundColor: const Color(0xFF23514C),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadRandomVerse,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          _loadCurrentDate();
          await _loadRandomVerse();
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Date Banner
              _buildDateBanner(),

              // Quranic Verse Section
              _buildQuranVerseSection(),

              // Topics Section
              _buildTopicsSection(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDateBanner() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 16),
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Color(0xFF23514C),
              Color(0xFF4A7B74),
            ],
          ),
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 10,
              offset: const Offset(0, 5),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Today',
              style: GoogleFonts.inter(
                fontSize: 16,
                color: Colors.white70,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              _currentDate['gregorian'] ?? 'Loading...',
              style: GoogleFonts.inter(
                fontSize: 18,
                color: Colors.white,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              _currentDate['islamic'] ?? 'Loading...',
              style: GoogleFonts.inter(
                fontSize: 16,
                color: Colors.white70,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuranVerseSection() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      padding: const EdgeInsets.all(20),
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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(
                Icons.auto_stories,
                color: Color(0xFF23514C),
              ),
              const SizedBox(width: 8),
              Text(
                'Verse of the Day',
                style: GoogleFonts.inter(
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF23514C),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          if (_isLoadingVerse)
            const Center(
              child: CircularProgressIndicator(),
            )
          else if (_currentVerse != null) ...[
            // Arabic Text
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.grey[50],
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                _currentVerse!.arabicText,
                style: GoogleFonts.amiri(
                  fontSize: 20,
                  height: 1.8,
                  color: Colors.black87,
                ),
                textAlign: TextAlign.right,
                textDirection: TextDirection.rtl,
              ),
            ),
            const SizedBox(height: 12),

            // English Translation
            Text(
              'English Translation:',
              style: GoogleFonts.inter(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: Colors.grey[700],
              ),
            ),
            const SizedBox(height: 4),
            Text(
              _currentVerse!.englishTranslation,
              style: GoogleFonts.inter(
                fontSize: 16,
                height: 1.5,
                color: Colors.black87,
              ),
            ),
            const SizedBox(height: 12),

            // Urdu Translation
            Text(
              'Urdu Translation:',
              style: GoogleFonts.inter(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: Colors.grey[700],
              ),
            ),
            const SizedBox(height: 4),
            Text(
              _currentVerse!.urduTranslation,
              style: GoogleFonts.inter(
                fontSize: 16,
                height: 1.5,
                color: Colors.black87,
              ),
              textAlign: TextAlign.right,
            ),
            const SizedBox(height: 12),

            // Surah Info
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: const Color(0xFF23514C),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                '${_currentVerse!.surahNameEnglish} ${_currentVerse!.surah}:${_currentVerse!.ayah}',
                style: GoogleFonts.inter(
                  fontSize: 12,
                  color: Colors.white,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
          ] else
            const Text('Failed to load verse'),
        ],
      ),
    );
  }

  Widget _buildTopicsSection() {
    return Container(
      margin: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Islamic Topics',
            style: GoogleFonts.inter(
              fontSize: 20,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(
            height: 200,
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: _sampleTopics
                    .map((topic) => _buildTopicCard(topic))
                    .toList(),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTopicCard(Topic topic) {
    return Container(
      width: 160,
      margin: const EdgeInsets.only(right: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
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
        borderRadius: BorderRadius.circular(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              height: 100,
              decoration: const BoxDecoration(
                color: Color(0xFF23514C),
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(12),
                  topRight: Radius.circular(12),
                ),
              ),
              child: const Center(
                child: Icon(
                  Icons.play_circle_outline,
                  size: 40,
                  color: Colors.white,
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    topic.title,
                    style: GoogleFonts.inter(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Colors.black87,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${topic.lessonsCount} lessons',
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
    );
  }

  // Sample topics data
  List<Topic> get _sampleTopics => [
        Topic(
          id: '1',
          title: 'Prayer Basics',
          description: 'Learn the fundamentals of Islamic prayer',
          imageUrl: '',
          lessonsCount: 8,
          duration: '2 hours',
          lessons: [],
        ),
        Topic(
          id: '2',
          title: 'Quran Recitation',
          description: 'Master the art of Quranic recitation',
          imageUrl: '',
          lessonsCount: 12,
          duration: '3 hours',
          lessons: [],
        ),
        Topic(
          id: '3',
          title: 'Islamic History',
          description: 'Explore the rich history of Islam',
          imageUrl: '',
          lessonsCount: 15,
          duration: '4 hours',
          lessons: [],
        ),
      ];
}
