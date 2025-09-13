import 'package:intl/intl.dart';

class IslamicCalendarService {
  /// Get current date in both Gregorian and Islamic calendar
  Map<String, String> getCurrentDate() {
    final now = DateTime.now();

    // Format Gregorian date
    final gregorianFormatter = DateFormat('EEEE, MMMM d, yyyy');
    final gregorianDate = gregorianFormatter.format(now);

    // Simple Islamic date calculation (approximate)
    final islamicDate = _getApproximateIslamicDate(now);

    return {
      'gregorian': gregorianDate,
      'islamic': islamicDate,
      'dayName': DateFormat('EEEE').format(now),
    };
  }

  /// Simple approximation of Islamic date
  String _getApproximateIslamicDate(DateTime gregorianDate) {
    // This is a simplified calculation for demonstration
    // In a real app, you would use a proper Islamic calendar library

    // Approximate calculation (not accurate, just for demo)
    const epochYear = 622; // Year of Hijra
    final gregorianYear = gregorianDate.year;
    final islamicYear = ((gregorianYear - epochYear) * 1.030684).round() + 1;

    final monthNames = [
      'Muharram',
      'Safar',
      'Rabi\' al-awwal',
      'Rabi\' al-thani',
      'Jumada al-awwal',
      'Jumada al-thani',
      'Rajab',
      'Sha\'ban',
      'Ramadan',
      'Shawwal',
      'Dhu al-Qi\'dah',
      'Dhu al-Hijjah',
    ];

    // Simple month approximation
    final monthIndex = (gregorianDate.month + 1) % 12;
    final monthName = monthNames[monthIndex];

    // Approximate day (this is not accurate)
    final day = (gregorianDate.day + 10) % 30 + 1;

    return '$day $monthName, $islamicYear AH';
  }

  /// Get Islamic month name in English
  String _getIslamicMonthName(int month) {
    const months = [
      'Muharram',
      'Safar',
      'Rabi\' al-awwal',
      'Rabi\' al-thani',
      'Jumada al-awwal',
      'Jumada al-thani',
      'Rajab',
      'Sha\'ban',
      'Ramadan',
      'Shawwal',
      'Dhu al-Qi\'dah',
      'Dhu al-Hijjah',
    ];

    if (month >= 1 && month <= 12) {
      return months[month - 1];
    }
    return 'Unknown';
  }

  /// Get Islamic month name in Arabic
  String _getIslamicMonthNameArabic(int month) {
    const months = [
      'محرم',
      'صفر',
      'ربيع الأول',
      'ربيع الثاني',
      'جمادى الأولى',
      'جمادى الثانية',
      'رجب',
      'شعبان',
      'رمضان',
      'شوال',
      'ذو القعدة',
      'ذو الحجة',
    ];

    if (month >= 1 && month <= 12) {
      return months[month - 1];
    }
    return 'غير معروف';
  }

  /// Check if current month is Ramadan (approximate)
  bool isRamadan() {
    // This is approximate - in a real app use proper Islamic calendar
    final now = DateTime.now();
    final approximateIslamicMonth = (now.month + 1) % 12;
    return approximateIslamicMonth == 8; // Ramadan is the 9th month (index 8)
  }

  /// Get formatted Islamic date with Arabic month name
  String getIslamicDateArabic() {
    final now = DateTime.now();
    final approximateIslamicMonth = (now.month + 1) % 12 + 1;
    final day = (now.day + 10) % 30 + 1;
    final year = ((now.year - 622) * 1.030684).round() + 1;

    return '$day ${_getIslamicMonthNameArabic(approximateIslamicMonth)} $year هـ';
  }
}
