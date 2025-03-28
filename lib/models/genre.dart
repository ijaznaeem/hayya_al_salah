class Categories {
  final int categoryId;
  final String categoryName;
  final String image;

  Categories(
      {required this.categoryId,
      required this.categoryName,
      required this.image});

  factory Categories.fromJson(Map<String, dynamic> json) {
    return Categories(
      categoryId: int.tryParse(json['categoryID']) ?? 0,
      categoryName: json['categoryName'],
      image: json['image'],
    );
  }
}
