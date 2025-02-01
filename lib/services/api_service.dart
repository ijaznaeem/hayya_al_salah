// ignore: depend_on_referenced_packages
import 'package:dio/dio.dart';
import 'package:dio_cache_interceptor/dio_cache_interceptor.dart';
import 'package:dio_cache_interceptor_hive_store/dio_cache_interceptor_hive_store.dart';
// ignore: depend_on_referenced_packages
import 'package:path_provider/path_provider.dart';

class ApiService {
  final Dio _dio;

  ApiService()
      : _dio = Dio(BaseOptions(
          baseUrl: 'https://salah.pakperegrine.com/apis/index.php/apis/',
        )) {
    _initializeCache();
  }

  Future<void> _initializeCache() async {
    final directory = await getApplicationDocumentsDirectory();
    final cacheOptions = CacheOptions(
      store: HiveCacheStore(directory.path),
      policy: CachePolicy.request,
      hitCacheOnErrorExcept: [401, 403],
      priority: CachePriority.normal,
      maxStale: const Duration(days: 7),
    );

    _dio.interceptors.add(DioCacheInterceptor(options: cacheOptions));
  }

  Future<Response> get(String endpoint, {Map<String, dynamic>? params}) async {
    return await _dio.get(
      endpoint,
      queryParameters: params,
      options: Options(extra: {'cache_policy': CachePolicy.forceCache}),
    );
  }
}
