import 'dart:convert';

class Movie {
  final int movieID;
  final String title;
  final String description;
  final int categoryID;
  final String pdfFile;
  final String animationFile;
  final String videoFile;
  final String image;
  final String genre;
  final String updated_on;

  Movie({
    required this.movieID,
    required this.title,
    required this.description,
    required this.categoryID,
    required this.pdfFile,
    required this.animationFile,
    required this.videoFile,
    required this.image, // Initialize the new field
    required this.genre, // Initialize the new field
    required this.updated_on, // Initialize the new field
  });

  Map<String, dynamic> toMap() {
    return {
      'movieID': movieID,
      'title': title,
      'description': description,
      'categoryID': categoryID,
      'pdfFile': pdfFile,
      'animationFile': animationFile,
      'videoFile': videoFile,
      'image': image,
      'genre': genre,
      'updated_on': updated_on,
    };
  }

  String toJson() => json.encode(toMap());

  factory Movie.fromJson(String source) => Movie.fromMap(json.decode(source));

  factory Movie.fromMap(Map<String, dynamic> map) {
    return Movie(
      movieID: map['movieID'],
      title: map['title'],
      description: map['description'],
      categoryID: map['categoryID'],
      pdfFile: map['pdfFile'],
      animationFile: map['animationFile'],
      videoFile: map['videoFile'],
      image: map['image'],
      genre: map['genre'],
      updated_on: map['updated_on'] ?? '',
    );
  }
}
