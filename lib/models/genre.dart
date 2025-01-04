class Categories {
  final int categoryId;
  final String categoryName;

  Categories({required this.categoryId, required this.categoryName});

  factory Categories.fromJson(Map<String, dynamic> json) {
    return Categories(
      categoryId: int.tryParse(json['categoryID']) ?? 0,
      categoryName: json['categoryName'],
    );
  }
}
