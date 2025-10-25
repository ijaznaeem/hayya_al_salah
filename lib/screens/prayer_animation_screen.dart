import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:google_fonts/google_fonts.dart';

import '../models/preyer_model.dart';
import '../models/topic.dart';

class PrayerAnimationScreen extends StatefulWidget {
  final Topic topic;

  const PrayerAnimationScreen({
    super.key,
    required this.topic,
  });

  @override
  State<PrayerAnimationScreen> createState() => _PrayerAnimationScreenState();
}

class _PrayerAnimationScreenState extends State<PrayerAnimationScreen> {
  final PageController _pageController = PageController();
  int _currentSliderIndex = 0;

  // Slider content data with SVG icons and descriptions
  final List<PrayerSliderItem> _sliderItems = [
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/takbeer.svg',
      title: 'Takbeer',
      urdu: 'دونوں ہاتھ کانوں کے قریب اٹھا کر کہنا "اللہ اکبر"',
      arabic: 'تكبير رفع اليدين بالقرب من الأذنين (قول "الله أكبر")',
      english: 'Standing with both hands raised near ears (saying “Allahu Akbar”)',
      banner: 'assets/images/banners/takbeer.png',
      action: 'takbeer',
      audio: 'assets/audio/takbeer.mp3',
      verses: [
       ''
      ],
    ),
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/qeyam.svg',
      title: 'Qeyam',
      urdu: 'سینے پر ہاتھ باندھ کر کھڑا ہونا',
      arabic: 'الوقوف مع طي اليدين على الصدر',
      english: 'Standing with hands folded on the chest',
      banner: 'assets/images/banners/qeyam.png',
      action: 'qeyam',
      audio: 'assets/audio/qeyam.mp3',
      verses: [
        'سُبْحَانَكَ اللَّهُمَّ وَبِحَمْدِكَ وَتَبَارَكَ اسْمُكَ وَتَعَالَى جَدُّكَ وَلَا إِلَهَ غَيْرُكَ',
         'اعوذ بالله من الشيطان الرجيم',
        'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ',
        'الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ',
        'الرَّحْمَٰنِ الرَّحِيمِ',
        'مَالِكِ يَوْمِ الدِّينِ',
        'إِيَّاكَ نَعْبُدُ وَإِيَّاكَ نَسْتَعِينُ',
        'اهْدِنَا الصِّرَاطَ الْمُسْتَقِيمَ',
        'صِرَاطَ الَّذِينَ أَنْعَمْتَ عَلَيْهِمْ غَيْرِ الْمَغْضُوبِ عَلَيْهِمْ وَلَا الضَّالِّينَ',
        'آمِينَ',
        'قُلْ هُوَ اللَّهُ أَحَدٌ',
        'اللَّهُ الصَّمَدُ',
        'لَمْ يَلِدْ وَلَمْ يُولَدْ',
        'وَلَمْ يَكُن لَّهُ كُفُوًا أَحَدٌ'
      ],
    ),
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/allah-akbar.svg',
      title: 'Allah Akbar',
      urdu: 'ہاتھ چھوڑ کر رکوع میں جانا',
      arabic: 'إسقاط اليدين والانحناء للركوع',
      english: 'Releasing hands and going into bowing position',
      banner: 'assets/images/banners/allah-akbar.png',
      action: 'allah-akbar',
      audio: 'assets/audio/allah-akbar.mp3',
      verses: [
       'الله أكبر'
      ],
    ),
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/rakuh.svg',
      title: 'Rakuh',
      urdu: 'دونوں ہاتھ گھٹنوں پر رکھ کر جھکنا (رکوع)',
      arabic: 'الركوع مع وضع اليدين على الركبتين',
      english: 'Bowing (bending at the waist)',
      banner: 'assets/images/banners/rakuh.png',
      action: 'rakuh',
      audio: 'assets/audio/rakuh.mp3',
      verses: [
        'سُبْحَانَ رَبِّيَ الْعَظِيمِ ',
        'سُبْحَانَ رَبِّيَ الْعَظِيمِ',
        'سُبْحَانَ رَبِّيَ الْعَظِيمِ'
      ],
    ),
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/allah-akbar.svg',
      title: 'Allah Akbar',
      urdu: 'رکوع سے سیدھا کھڑا ہونا',
      arabic: 'القيام من الركوع',
      english: 'Standing up straight after bowing',
      banner: 'assets/images/banners/allah-akbar.png',
      action: 'allah-akbar',
      audio: 'assets/audio/allah-akbar.mp3',
      verses: [
       'الله أكبر'
      ],
    ),
     PrayerSliderItem(
      svgPath: 'assets/namaz/svg/qauma.svg',
      title: 'Qauma',
      urdu: 'سیدھا کھڑا ہونا، ہاتھ آرام سے اطراف میں (قیام سے مختلف)',
      arabic: 'القيام بعد الركوع، اليدين مرتخيتين بجانب الجسم (مختلف عن القيام)',
      english: 'Standing straight again after bowing, hands relaxed at sides (distinct from Qiyam)',
      banner: 'assets/images/banners/qauma.png',
      action: 'qauma',
      audio: 'assets/audio/qauma.mp3',
      verses: ['رَبَّنَا وَلَكَ الْحَمْدُ ']
    ),
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/allah-akbar.svg',
      title: 'Allah Akbar',
      urdu: 'اللہ اکبر کہ کر سجدے میں جانا',
      arabic: 'قول "الله أكبر" والذهاب إلى السجود',
      english: 'Saying "Allahu Akbar" and going into prostration (Sajdah)',
      banner: 'assets/images/banners/allah-akbar.png',
      action: 'allah-akbar',
      audio: 'assets/audio/allah-akbar.mp3',
      verses: [
       'الله أكبر'
      ],
    ),
   
    
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/sajda.svg',
      title: 'First Sajda',
      urdu: 'پہلا سجدہ',
      arabic: 'السجدہ الأول',
      english: 'First full prostration on the ground',
      banner: 'assets/images/banners/sajda.png',
      action: 'sajda',
      audio: 'assets/audio/sajda.mp3',
      verses: [
        'سُبْحَانَ رَبِّيَ الْأَعْلَى ',
        'سُبْحَانَ رَبِّيَ الْأَعْلَى',
        'سُبْحَانَ رَبِّيَ الْأَعْلَى'
      ],
    ),
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/allah-akbar.svg',
      title: 'Allah Akbar',
      urdu: 'اللہ اکبر کہ کر سیدھا بیٹھنا',
      arabic: 'قول "الله أكبر" والجلوس مستقيماً',
      english: 'Saying "Allahu Akbar" and sitting straight',
      banner: 'assets/images/banners/allah-akbar.png',
      action: 'allah-akbar',
      audio: 'assets/audio/allah-akbar.mp3',
      verses: [
       'الله أكبر'
      ],
    ),
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/jalsa.svg',
      title: 'Jalsa',
      urdu: ' دو سجدوں کے درمیان بیٹھنا',
      arabic: 'الجلوس بين السجدتين',
      english: 'Sitting between two Sajdas',
      banner: 'assets/images/banners/jalsa.png',
      action: 'jalsa',
      audio: 'assets/audio/jalsa.mp3',
      verses: [
       ''
      ],
    ),
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/allah-akbar.svg',
      title: 'Allah Akbar',
      urdu: 'اللہ اکبر کہ دوسرے سجدے میں جانا',
      arabic: 'قول "الله أكبر" والذهاب إلى السجدة الثانية',
      english: 'Saying "Allahu Akbar" and going into the second prostration',
      banner: 'assets/images/banners/allah-akbar.png',
      action: 'allah-akbar',
      audio: 'assets/audio/allah-akbar.mp3',
      verses: [
       'الله أكبر'
      ],
    ),
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/sajda.svg',
      title: 'Second Sajdah',
      urdu: ' دوسرا سجدہ',
      arabic: 'السجدہ الثانية',
      english: 'Second full prostration on the ground',
      banner: 'assets/images/banners/sajda.png',
      action: 'sajda2',
      audio: 'assets/audio/sajda.mp3',
      verses: [
        'سُبْحَانَ رَبِّيَ الْأَعْلَى ',
        'سُبْحَانَ رَبِّيَ الْأَعْلَى',
        'سُبْحَانَ رَبِّيَ الْأَعْلَى'
      ],
    ),
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/allah-akbar.svg',
      title: 'Allah Akbar',
      urdu: 'اللہ اکبر کہ کر تشہد میں بیٹھ جائیں',
      arabic: 'قول "الله أكبر" والجلوس في التشهد',
      english: 'Saying "Allahu Akbar" and sitting in Tashahhud',
      banner: 'assets/images/banners/allah-akbar.png',
      action: 'allah-akbar',
      audio: 'assets/audio/allah-akbar.mp3',
      verses: [
       'الله أكبر'
      ],
    ),
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/tashud.svg',
      title: 'Tashahhud ',
      urdu: 'دو سجدوں کے بعد بیٹھ کر مخصوص دعا پڑھنا ',
      arabic: 'الجلوس بعد السجدتين وقراءة التشهد',
      english: 'Sits after performing two prostrations and recites the Tashahhud',
      banner: 'assets/images/banners/tashud.png',
      action: 'tashud',
      audio: 'assets/audio/tashud.mp3',
      verses: [
        'التَّحِيَّاتُ لِلَّهِ وَالصَّلَوَاتُ وَالطَّيِّبَاتُ',
        'السَّلَامُ عَلَيْكَ أَيُّهَا النَّبِيُّ وَرَحْمَةُ اللَّهِ وَبَرَكَاتُهُ',
        'السَّلَامُ عَلَيْنَا وَعَلَى عِبَادِ اللَّهِ الصَّالِحِينَ',
        'أَشْهَدُ أَنْ لَا إِلَهَ إِلَّا اللَّهُ',
        'وَأَشْهَدُ أَنَّ مُحَمَّدًا عَبْدُهُ وَرَسُولُهُ',
        'اللَّهُمَّ صَلِّ عَلَى مُحَمَّدٍ وَعَلَى آلِ مُحَمَّدٍ',
        'كَمَا صَلَّيْتَ عَلَى إِبْرَاهِيمَ وَعَلَى آلِ إِبْرَاهِيمَ',
        'إِنَّكَ حَمِيدٌ مَجِيدٌ',
        'اللَّهُمَّ بَارِكْ عَلَى مُحَمَّدٍ وَعَلَى آلِ مُحَمَّدٍ',
        'كَمَا بَارَكْتَ عَلَى إِبْرَاهِيمَ وَعَلَى آلِ إِبْرَاهِيمَ',
        'إِنَّكَ حَمِيدٌ مَجِيدٌ',
        'رَبِّ اجْعَلْنِي مُقِيمَ الصَّلَاةِ وَمِن ذُرِّيَّتِي رَبَّنَا وَتَقَبَّلْ دُعَاءِ',
        'رَبَّنَا اغْفِرْ لِي وَلِوَالِدَيَّ وَلِلْمُؤْمِنِينَ يَوْمَ يَقُومُ الْحِسَابُ',
        
      ],
    ),
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/salam-right.svg',
      title: 'Salam Right ',
      urdu: 'دائیں جانب سلام کرنا',
      arabic: 'السلام عليكم ورحمة الله وبركاته',
      english: 'Salam Right side',
      banner: 'assets/images/banners/salam-right.png',
      action: 'salam-right',
      audio: 'assets/audio/salam-right.mp3',
      verses: [
        'السلام عليكم ورحمة الله وبركاته'
      ],
    ),
    PrayerSliderItem(
      svgPath: 'assets/namaz/svg/salam-left.svg',
      title: 'Salam Left ',
      urdu: 'بائیں جانب سلام کرنا',
      arabic: 'السلام عليكم ورحمة الله وبركاته',
      english: 'Salam Left side',
      banner: 'assets/images/banners/salam-left.png',
      action: 'salam-left',
      audio: 'assets/audio/salam-left.mp3',
      verses: [
        'السلام عليكم ورحمة الله وبركاته'
      ]
    ),
  ];

  @override
  void initState() {
    super.initState();
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  /// Helper method to determine text direction based on content
  TextDirection _getTextDirection(String text) {
    if (text.isEmpty) return TextDirection.ltr;

    // Check if text contains Arabic, Urdu, or other RTL characters
    final rtlRegex = RegExp(
        r'[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF\u200F\u202B\u202E]');
    if (rtlRegex.hasMatch(text)) {
      return TextDirection.rtl;
    }
    return TextDirection.ltr;
  }

  /// Create a text widget with proper Unicode support
  Widget buildUnicodeText(
    String text, {
    TextStyle? style,
    int? maxLines,
    TextOverflow? overflow,
    TextAlign? textAlign,
  }) {
    final isRtl = _getTextDirection(text) == TextDirection.rtl;

    // Define fallback fonts for different scripts
    List<String> fontFallbacks = [
      if (isRtl) ...[
        'Noto Sans Arabic',
        'Noto Nastaliq Urdu',
        'Arabic Typesetting',
        'Traditional Arabic',
        'Segoe UI Historic',
      ],
      'Roboto',
      'Arial',
      'sans-serif',
    ];

    return Directionality(
      textDirection: _getTextDirection(text),
      child: Text(
        text,
        style: TextStyle(
          fontFamilyFallback: fontFallbacks,
          fontSize: style?.fontSize ?? 16,
          fontWeight: style?.fontWeight,
          color: style?.color ?? Colors.black87,
          height: style?.height ??
              (isRtl ? 1.8 : 1.5), // Better line height for Urdu
        ),
        maxLines: maxLines,
        overflow: overflow,
        textAlign: textAlign ?? (isRtl ? TextAlign.right : TextAlign.left),
        textDirection: _getTextDirection(text),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      appBar: AppBar(
        title: buildUnicodeText(
          'Topic: ${widget.topic.title}',
          style: GoogleFonts.inter(fontWeight: FontWeight.w600),
        ),
        backgroundColor: const Color(0xFF1B5E20),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Topic Header (separated from slider)
          Container(
            width: double.infinity,
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  Color(0xFF1B5E20),
                  Color(0xFF23514C),
                ],
              ),
            ),
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  buildUnicodeText(
                    widget.topic.title,
                    style: GoogleFonts.inter(
                      fontSize: 24,
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  buildUnicodeText(
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
                        '${_sliderItems.length} slides',
                      ),
                      
                    ],
                  ),
                ],
              ),
            ),
          ),

          // White Background Slider Section (detached)
          Expanded(
            child: Container(
              color: Colors.white,
              child: Column(
                children: [
                  // Dots Indicator at Top
                  Padding(
                    padding: const EdgeInsets.only(top: 16, bottom: 8),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: List.generate(
                        _sliderItems.length,
                        (index) => _buildDotIndicator(index),
                      ),
                    ),
                  ),

                  // Slider
                  Expanded(
                    flex: 3,
                    child: PageView.builder(
                      controller: _pageController,
                      onPageChanged: (index) {
                        setState(() {
                          _currentSliderIndex = index;
                        });
                      },
                      itemCount: _sliderItems.length,
                      itemBuilder: (context, index) {
                        final item = _sliderItems[index];
                        return _buildSliderItem(item);
                      },
                    ),
                  ),

                  // Verses Section
                  Expanded(
                    flex: 2,
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.grey[50],
                        border: Border(
                          top: BorderSide(
                            color: Colors.grey[300]!,
                            width: 1,
                          ),
                        ),
                      ),
                      child: Column(
                        children: [
                          Padding(
                            padding: const EdgeInsets.all(4),
                            child: Text(
                              'قراءة الصلاة',
                              style: GoogleFonts.inter(
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                                color: const Color(0xFF1B5E20),
                              ),
                            ),
                          ),
                          Expanded(
                            child: (_sliderItems[_currentSliderIndex].verses?.isEmpty ?? true)
                                ? Center(
                                    child: Text(
                                      'No verses available',
                                      style: GoogleFonts.inter(
                                        fontSize: 14,
                                        color: Colors.grey[600],
                                      ),
                                    ),
                                  )
                                : ListView.builder(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 8,
                                      vertical: 4,
                                    ),
                                    itemCount: _sliderItems[_currentSliderIndex].verses?.length ?? 0,
                                    itemBuilder: (context, index) {
                                      final verse = _sliderItems[_currentSliderIndex].verses![index];
                                      if (verse.isEmpty) return const SizedBox.shrink();
                                      return Container(
                                        margin: const EdgeInsets.only(bottom:2),
                                        padding: const EdgeInsets.all(2),
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius: BorderRadius.circular(8),
                                          border: Border.all(
                                            color: const Color(0xFF1B5E20).withOpacity(0.2),
                                            width: 1,
                                          ),
                                        ),
                                        child: buildUnicodeText(
                                          verse,
                                          style: GoogleFonts.inter(
                                            fontSize: 14,
                                            fontWeight: FontWeight.w500,
                                            color: Colors.black87,
                                            height: 1.2,
                                          ),
                                          textAlign: TextAlign.center,
                                        ),
                                      );
                                    },
                                  ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSliderItem(PrayerSliderItem item) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 0),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.start,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          // SVG Icon
          Container(
            width: 200 ,
            height: 200,
            padding: const EdgeInsets.all(1),
            decoration: BoxDecoration(
              // color: const Color(0xFF1B5E20).withOpacity(0.1),
              borderRadius: BorderRadius.circular(50),
              border: Border.all(
                color: const Color(0xFF1B5E20).withOpacity(0.3),
                width: 2,
              ),
            ),
            child: SvgPicture.asset(
              item.svgPath,
              color: const Color(0xFF1B5E20),
              width: 100,
              height: 100,
            ),
          ),

          const SizedBox(height: 8),

          // Main Title
          Text(
            item.title,
            style: GoogleFonts.inter(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: const Color(0xFF1B5E20),
            ),
            textAlign: TextAlign.center,
          ),

          const SizedBox(height: 4),
          // Main Title
          Text(
            item.arabic,
            style: GoogleFonts.inter(
              fontSize: 12,
              fontWeight: FontWeight.bold,
              color: const Color(0xFF1B5E20),
            ),
            textAlign: TextAlign.center,
          ),

          const SizedBox(height: 4),

          // Urdu
            Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: Colors.orange[50],
              borderRadius: BorderRadius.circular(8),
            ),
            child: buildUnicodeText(
              item.urdu,
              style: GoogleFonts.inter(
              fontSize: 12,
              color: Colors.black87,
              height: 1.2,
              ),
              textAlign: TextAlign.right,
            ),
            ),

          const SizedBox(height: 4),

          // english
            Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: Colors.green[50],
              borderRadius: BorderRadius.circular(8),
            ),
            child: buildUnicodeText(
              item.english,
              style: GoogleFonts.inter(
              fontSize: 12,
              color: Colors.black87,
              height: 1.0,
              ),
              textAlign: TextAlign.right,
            ),
            ),
        ],
      ),
    );
  }

  Widget _buildDotIndicator(int index) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      margin: const EdgeInsets.symmetric(horizontal: 4),
      width: _currentSliderIndex == index ? 24 : 8,
      height: 8,
      decoration: BoxDecoration(
        color: _currentSliderIndex == index
            ? const Color(0xFF1B5E20)
            : Colors.grey[300],
        borderRadius: BorderRadius.circular(4),
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
}
