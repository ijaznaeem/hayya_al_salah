import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import '../models/topic.dart';

class ApiService {
  static const String baseUrl = 'http://localhost:8000/api';
  static const String tokenKey = 'auth_token';

  // Singleton pattern
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  String? _authToken;

  // Initialize with stored token
  Future<void> initialize() async {
    final prefs = await SharedPreferences.getInstance();
    _authToken = prefs.getString(tokenKey);
  }

  // Get headers with authentication if available
  Map<String, String> get _headers {
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (_authToken != null) {
      headers['Authorization'] = 'Bearer $_authToken';
    }

    return headers;
  }

  // Store auth token
  Future<void> _storeToken(String token) async {
    _authToken = token;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(tokenKey, token);
  }

  // Clear auth token
  Future<void> _clearToken() async {
    _authToken = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(tokenKey);
  }

  // Authentication Methods
  Future<Map<String, dynamic>> register({
    required String username,
    required String email,
    required String password,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/register'),
        headers: _headers,
        body: jsonEncode({
          'username': username,
          'email': email,
          'password': password,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 201 && data['token'] != null) {
        await _storeToken(data['token']);
        return {'success': true, 'user': data['user']};
      } else {
        return {
          'success': false,
          'error': data['error'] ?? 'Registration failed'
        };
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: $e'};
    }
  }

  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/login'),
        headers: _headers,
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['token'] != null) {
        await _storeToken(data['token']);
        return {'success': true, 'user': data['user']};
      } else {
        return {'success': false, 'error': data['error'] ?? 'Login failed'};
      }
    } catch (e) {
      return {'success': false, 'error': 'Network error: $e'};
    }
  }

  Future<void> logout() async {
    await _clearToken();
  }

  Future<bool> isLoggedIn() async {
    await initialize();
    return _authToken != null;
  }

  // Topics Methods
  Future<List<Topic>> getTopics() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/topics'),
        headers: _headers,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return (data['data'] as List)
              .map((topicJson) => Topic.fromApiJson(topicJson))
              .toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching topics: $e');
      return [];
    }
  }

  Future<Topic?> getTopic(int id) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/topics/$id'),
        headers: _headers,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return Topic.fromApiJson(data['data']);
        }
      }
      return null;
    } catch (e) {
      print('Error fetching topic: $e');
      return null;
    }
  }

  // Lectures Methods
  Future<List<Lesson>> getLectures({
    int page = 1,
    int perPage = 20,
    String? search,
  }) async {
    try {
      final queryParams = <String, String>{
        'page': page.toString(),
        'per_page': perPage.toString(),
      };

      if (search != null && search.isNotEmpty) {
        queryParams['search'] = search;
      }

      final uri =
          Uri.parse('$baseUrl/lectures').replace(queryParameters: queryParams);
      final response = await http.get(uri, headers: _headers);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return (data['data'] as List)
              .map((lectureJson) => Lesson.fromApiJson(lectureJson))
              .toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching lectures: $e');
      return [];
    }
  }

  Future<Lesson?> getLecture(int id) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/lectures/$id'),
        headers: _headers,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return Lesson.fromApiJson(data['data']);
        }
      }
      return null;
    } catch (e) {
      print('Error fetching lecture: $e');
      return null;
    }
  }

  Future<List<Lesson>> getLecturesByTopic(int topicId) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/topics/$topicId/lectures'),
        headers: _headers,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return (data['data'] as List)
              .map((lectureJson) => Lesson.fromApiJson(lectureJson))
              .toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching lectures by topic: $e');
      return [];
    }
  }

  Future<List<Lesson>> getPopularLectures({int limit = 10}) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/lectures/popular?limit=$limit'),
        headers: _headers,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return (data['data'] as List)
              .map((lectureJson) => Lesson.fromApiJson(lectureJson))
              .toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching popular lectures: $e');
      return [];
    }
  }

  Future<List<Lesson>> getRecentLectures({int limit = 10}) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/lectures/recent?limit=$limit'),
        headers: _headers,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return (data['data'] as List)
              .map((lectureJson) => Lesson.fromApiJson(lectureJson))
              .toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching recent lectures: $e');
      return [];
    }
  }

  // Favorites Methods (requires authentication)
  Future<List<Lesson>> getFavorites() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/user/favorites'),
        headers: _headers,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return (data['data'] as List)
              .map((lectureJson) => Lesson.fromApiJson(lectureJson))
              .toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching favorites: $e');
      return [];
    }
  }

  Future<List<int>> getFavoriteIds() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/user/favorites/ids'),
        headers: _headers,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return List<int>.from(data['data']);
        }
      }
      return [];
    } catch (e) {
      print('Error fetching favorite IDs: $e');
      return [];
    }
  }

  Future<bool> isFavorite(int lectureId) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/user/favorites/check/$lectureId'),
        headers: _headers,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['is_favorite'] ?? false;
      }
      return false;
    } catch (e) {
      print('Error checking favorite status: $e');
      return false;
    }
  }

  Future<bool> addToFavorites(int lectureId) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/user/favorites/$lectureId'),
        headers: _headers,
      );

      return response.statusCode == 201;
    } catch (e) {
      print('Error adding to favorites: $e');
      return false;
    }
  }

  Future<bool> removeFromFavorites(int lectureId) async {
    try {
      final response = await http.delete(
        Uri.parse('$baseUrl/user/favorites/$lectureId'),
        headers: _headers,
      );

      return response.statusCode == 200;
    } catch (e) {
      print('Error removing from favorites: $e');
      return false;
    }
  }

  Future<bool> clearFavorites() async {
    try {
      final response = await http.delete(
        Uri.parse('$baseUrl/user/favorites'),
        headers: _headers,
      );

      return response.statusCode == 200;
    } catch (e) {
      print('Error clearing favorites: $e');
      return false;
    }
  }
}
