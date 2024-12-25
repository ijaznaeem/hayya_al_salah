class Movie {
  final int movieID;
  final String title;
  final String description;
  final int categoryID;
  final String pdfFile;
  final String animationFile;
  final String image; // New field for movie thumbnail
  final String genre; // New field for movie genre

  Movie({
    required this.movieID,
    required this.title,
    required this.description,
    required this.categoryID,
    required this.pdfFile,
    required this.animationFile,
    required this.image, // Initialize the new field
    required this.genre, // Initialize the new field
  });

  Map<String, dynamic> toMap() {
    return {
      'movieID': movieID,
      'title': title,
      'description': description,
      'categoryID': categoryID,
      'pdfFile': pdfFile,
      'animationFile': animationFile,
      'image': image, // Add the new field to the map
      'genre': genre, // Add the new field to the map
    };
  }
}
