import 'package:flutter/material.dart';
import 'package:hayya_al_salah/models/movie.dart';
import 'package:hayya_al_salah/services/api_service.dart'; // Add this import
import 'package:hayya_al_salah/widgets/movieList.dart';
import 'package:shimmer/shimmer.dart';

import '../widgets/appBr.dart';

class CategoryVideoScreen extends StatefulWidget {
  final int categoryId;
  final String categoryName;

  const CategoryVideoScreen(
      {Key? key, required this.categoryId, required this.categoryName})
      : super(key: key);

  @override
  _CategoryVideoScreenState createState() => _CategoryVideoScreenState();
}

class _CategoryVideoScreenState extends State<CategoryVideoScreen> {
  List<Movie> movies = [];
  int currentPage = 1;
  int pageLimit = 10;
  bool isLoading = false;
  bool hasMore = true;
  Map<int, String> genreMap = {};
  final ApiService _apiService = ApiService(); // Add this line

  @override
  void initState() {
    super.initState();
    ftechByCategory();
  }

  Future<void> ftechByCategory() async {
    if (isLoading) return;

    setState(() {
      isLoading = true;
    });

    final response = await _apiService.get(
      'videos',
      params: {'limit': pageLimit, 'offset': (currentPage - 1) * pageLimit},
    );

    if (response.statusCode == 200) {
      final data = response.data;
      final List<Movie> fetchedMovies =
          await Future.wait((data as List).map((movieData) async {
        return Movie(
          movieID: int.tryParse(movieData['videoID']) ?? 0,
          title: movieData['title'],
          description: movieData['description'],
          categoryID: int.tryParse(movieData['categoryID']) ?? 0,
          pdfFile: '',
          animationFile: movieData['animationFile'] ?? "",
          videoFile: movieData['videoFile'] ?? "",
          image:
              'https://salah.pakperegrine.com/apis/uploads/${movieData['image']}',
          genre: movieData['genre'] ?? '',
          updated_on: movieData['updated_on'] ?? '',
        );
      }).toList());

      setState(() {
        currentPage++;
        isLoading = false;
        hasMore = fetchedMovies.isNotEmpty;
        movies.addAll(fetchedMovies);
      });
    } else {
      setState(() {
        isLoading = false;
      });
      throw Exception('Failed to load trending movies');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.transparent,
      appBar: CustomAppBar(title: widget.categoryName),
      body: Container(
        width: double.infinity,
        height: double.infinity,
        color: Colors.greenAccent[100],
        child: Column(
          children: [
            Expanded(
              child: NotificationListener<ScrollNotification>(
                onNotification: (ScrollNotification scrollInfo) {
                  if (!isLoading &&
                      hasMore &&
                      scrollInfo.metrics.pixels ==
                          scrollInfo.metrics.maxScrollExtent) {}
                  return false;
                },
                child: movies.isEmpty
                    ? ListView.builder(
                        itemCount: 2,
                        itemBuilder: (context, index) => Card(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Shimmer.fromColors(
                                baseColor: Colors.grey[300]!,
                                highlightColor: Colors.grey[100]!,
                                child: Container(
                                  width: double.infinity,
                                  height: 200,
                                  color: Colors.white,
                                ),
                              ),
                              Container(
                                padding:
                                    const EdgeInsets.only(top: 20, bottom: 20),
                                width: MediaQuery.of(context).size.width / 1.1,
                                decoration: BoxDecoration(
                                  color: Colors.white.withOpacity(0.3),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Shimmer.fromColors(
                                  baseColor: Colors.grey[300]!,
                                  highlightColor: Colors.grey[100]!,
                                  child: Container(
                                    width: double.infinity,
                                    height: 20,
                                    color: Colors.white,
                                  ),
                                ),
                              ),
                              Container(
                                padding:
                                    const EdgeInsets.only(top: 20, bottom: 20),
                                width: MediaQuery.of(context).size.width / 1.1,
                                decoration: BoxDecoration(
                                  color: Colors.white.withOpacity(0.3),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Shimmer.fromColors(
                                  baseColor: Colors.grey[300]!,
                                  highlightColor: Colors.grey[100]!,
                                  child: Container(
                                    width: double.infinity,
                                    height: 20,
                                    color: Colors.white,
                                  ),
                                ),
                              ),
                              const SizedBox(height: 8.0),
                            ],
                          ),
                        ),
                      )
                    : MovieList(movies: movies), // Use the reusable widget
              ),
            ),
          ],
        ),
      ),
    );
  }
}
