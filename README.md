# rehman

A new Flutter project.

## Getting Started

This project is a starting point for a Flutter application.

A few resources to get you started if this is your first Flutter project:

- [Lab: Write your first Flutter app](https://flutter.dev/docs/get-started/codelab)
- [Cookbook: Useful Flutter samples](https://flutter.dev/docs/cookbook)

For help getting started with Flutter, view our
[online documentation](https://flutter.dev/docs), which offers tutorials,
samples, guidance on mobile development, and a full API reference.

## Features

### Favorites System
The app includes a comprehensive favorites system that allows users to:
- **Add lessons to favorites** from lesson detail screens and video player
- **View all favorite lessons** in a dedicated favorites screen
- **Remove individual lessons** from favorites with a single tap
- **Clear all favorites** with a confirmation dialog
- **Visual feedback** with heart icons and snackbar notifications
- **Persistent storage** using SharedPreferences for offline access

The favorites are stored locally and persist between app sessions.

### Reference Material Downloads
Users can access supplementary materials for lessons:
- **One-tap download** of reference materials (PDFs, documents, etc.)
- **External browser opening** - materials open in user's preferred apps
- **Smart detection** - download buttons only appear when materials are available
- **Multiple access points** - available in lesson details, favorites, and video player
- **Error handling** - graceful handling of invalid or unavailable materials
- **No storage permissions** - uses external apps, no local storage required

## Keytool Command
keytool -genkey -v -keystore hayya-alal-salah.keystore -alias hayya-alal-salah -keyalg RSA -keysize 2048 -validity 10000

## signing Password
Naeem123*
## Flutter build 
flutter build appbundle --build-name=1.2.4 --build-number=17