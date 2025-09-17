# Hayya Al Salah API Documentation

Base URL: `http://localhost:8000/api`

## Authentication

### Register User
**POST** `/auth/register`

**Request Body:**
```json
{
    "username": "johndoe",
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "username": "johndoe",
        "email": "john@example.com",
        "role": "user",
        "created_at": "2024-01-01 12:00:00",
        "updated_at": "2024-01-01 12:00:00"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

### Login User
**POST** `/auth/login`

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "username": "johndoe",
        "email": "john@example.com",
        "role": "user"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

### Refresh Token
**POST** `/auth/refresh`

**Headers:**
```
Authorization: Bearer <token>
```

**Response:**
```json
{
    "message": "Token refreshed",
    "user": {...},
    "token": "new_jwt_token"
}
```

### Get Profile
**GET** `/auth/profile`

**Headers:**
```
Authorization: Bearer <token>
```

**Response:**
```json
{
    "user": {
        "id": 1,
        "username": "johndoe",
        "email": "john@example.com",
        "role": "user",
        "created_at": "2024-01-01 12:00:00",
        "updated_at": "2024-01-01 12:00:00"
    }
}
```

## Topics

### Get All Topics
**GET** `/topics`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Islamic Prayer (Salah)",
            "description": "Learn the fundamentals of Islamic prayer...",
            "image_url": "https://example.com/prayer.jpg",
            "lessons_count": 3,
            "duration": "46:35",
            "created_at": "2024-01-01 12:00:00",
            "updated_at": "2024-01-01 12:00:00"
        }
    ],
    "count": 1
}
```

### Get Topic by ID
**GET** `/topics/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Islamic Prayer (Salah)",
        "description": "Learn the fundamentals of Islamic prayer...",
        "image_url": "https://example.com/prayer.jpg",
        "lessons_count": 3,
        "duration": "46:35",
        "lessons": [
            {
                "id": 1,
                "title": "Introduction to Prayer",
                "description": "Understanding the importance...",
                "video_url": "https://example.com/video1.mp4",
                "duration": "15:30",
                "lesson_number": 1
            }
        ],
        "created_at": "2024-01-01 12:00:00",
        "updated_at": "2024-01-01 12:00:00"
    }
}
```

### Create Topic (Admin Only)
**POST** `/topics`

**Headers:**
```
Authorization: Bearer <admin_token>
```

**Request Body:**
```json
{
    "title": "New Topic",
    "description": "Topic description",
    "image_url": "https://example.com/image.jpg"
}
```

### Update Topic (Admin Only)
**PUT** `/topics/{id}`

**Headers:**
```
Authorization: Bearer <admin_token>
```

**Request Body:**
```json
{
    "title": "Updated Topic Title",
    "description": "Updated description"
}
```

### Delete Topic (Admin Only)
**DELETE** `/topics/{id}`

**Headers:**
```
Authorization: Bearer <admin_token>
```

## Lectures

### Get All Lectures
**GET** `/lectures`

**Query Parameters:**
- `page` (optional): Page number for pagination (default: 1)
- `per_page` (optional): Items per page (default: 20)
- `search` (optional): Search term for title/description

**Examples:**
- `/lectures?page=1&per_page=10`
- `/lectures?search=prayer`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "topic_id": 1,
            "title": "Introduction to Prayer",
            "description": "Understanding the importance...",
            "video_url": "https://example.com/video1.mp4",
            "thumbnail_url": "https://example.com/thumb1.jpg",
            "duration": "15:30",
            "lesson_number": 1,
            "reference_file_url": "https://example.com/ref1.pdf",
            "topic_title": "Islamic Prayer (Salah)",
            "created_at": "2024-01-01 12:00:00"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 20,
        "total": 50,
        "total_pages": 3,
        "has_next": true,
        "has_prev": false
    }
}
```

### Get Lecture by ID
**GET** `/lectures/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "topic_id": 1,
        "title": "Introduction to Prayer",
        "description": "Understanding the importance...",
        "video_url": "https://example.com/video1.mp4",
        "thumbnail_url": "https://example.com/thumb1.jpg",
        "duration": "15:30",
        "lesson_number": 1,
        "reference_file_url": "https://example.com/ref1.pdf",
        "topic_title": "Islamic Prayer (Salah)",
        "related_lessons": [
            {
                "id": 2,
                "title": "Wudu (Ablution)",
                "duration": "12:45"
            }
        ],
        "created_at": "2024-01-01 12:00:00"
    }
}
```

### Get Lectures by Topic
**GET** `/topics/{id}/lectures`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Introduction to Prayer",
            "description": "Understanding the importance...",
            "video_url": "https://example.com/video1.mp4",
            "duration": "15:30",
            "lesson_number": 1
        }
    ],
    "count": 1,
    "topic_id": 1
}
```

### Get Popular Lectures
**GET** `/lectures/popular`

**Query Parameters:**
- `limit` (optional): Number of results (default: 10)

### Get Recent Lectures
**GET** `/lectures/recent`

**Query Parameters:**
- `limit` (optional): Number of results (default: 10)

### Create Lecture (Admin Only)
**POST** `/lectures`

**Headers:**
```
Authorization: Bearer <admin_token>
```

**Request Body:**
```json
{
    "topic_id": 1,
    "title": "New Lecture",
    "description": "Lecture description",
    "video_url": "https://example.com/video.mp4",
    "thumbnail_url": "https://example.com/thumb.jpg",
    "duration": "15:30",
    "lesson_number": 1,
    "reference_file_url": "https://example.com/ref.pdf"
}
```

### Update Lecture (Admin Only)
**PUT** `/lectures/{id}`

**Headers:**
```
Authorization: Bearer <admin_token>
```

### Delete Lecture (Admin Only)
**DELETE** `/lectures/{id}`

**Headers:**
```
Authorization: Bearer <admin_token>
```

## User Favorites

### Get User Favorites
**GET** `/user/favorites`

**Headers:**
```
Authorization: Bearer <token>
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "topic_id": 1,
            "title": "Introduction to Prayer",
            "description": "Understanding the importance...",
            "video_url": "https://example.com/video1.mp4",
            "duration": "15:30",
            "topic_title": "Islamic Prayer (Salah)"
        }
    ],
    "count": 1
}
```

### Get Favorite IDs
**GET** `/user/favorites/ids`

**Headers:**
```
Authorization: Bearer <token>
```

**Response:**
```json
{
    "success": true,
    "data": [1, 3, 5, 7],
    "count": 4
}
```

### Check if Lecture is Favorite
**GET** `/user/favorites/check/{lecture_id}`

**Headers:**
```
Authorization: Bearer <token>
```

**Response:**
```json
{
    "success": true,
    "is_favorite": true,
    "lecture_id": 1
}
```

### Add Lecture to Favorites
**POST** `/user/favorites/{lecture_id}`

**Headers:**
```
Authorization: Bearer <token>
```

**Response:**
```json
{
    "success": true,
    "message": "Lecture added to favorites"
}
```

### Remove Lecture from Favorites
**DELETE** `/user/favorites/{lecture_id}`

**Headers:**
```
Authorization: Bearer <token>
```

**Response:**
```json
{
    "success": true,
    "message": "Lecture removed from favorites"
}
```

### Clear All Favorites
**DELETE** `/user/favorites`

**Headers:**
```
Authorization: Bearer <token>
```

**Response:**
```json
{
    "success": true,
    "message": "All favorites cleared"
}
```

## Statistics

### Get Most Favorited Lectures
**GET** `/stats/most-favorited`

**Query Parameters:**
- `limit` (optional): Number of results (default: 10)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Introduction to Prayer",
            "description": "Understanding the importance...",
            "video_url": "https://example.com/video1.mp4",
            "duration": "15:30",
            "topic_title": "Islamic Prayer (Salah)",
            "favorite_count": 25
        }
    ],
    "count": 1
}
```

## Error Responses

### 400 Bad Request
```json
{
    "error": "Missing required fields",
    "missing": ["title", "description"]
}
```

### 401 Unauthorized
```json
{
    "error": "Authentication required"
}
```

### 403 Forbidden
```json
{
    "error": "Admin access required"
}
```

### 404 Not Found
```json
{
    "error": "Resource not found"
}
```

### 409 Conflict
```json
{
    "error": "Email already registered"
}
```

### 500 Internal Server Error
```json
{
    "error": "Internal server error",
    "message": "Database connection failed"
}
```

## Rate Limiting

The API has built-in rate limiting to prevent abuse:
- **Public endpoints**: 100 requests per minute
- **Authenticated endpoints**: 300 requests per minute
- **Admin endpoints**: 500 requests per minute

## Data Validation

### Duration Format
Duration should be in one of these formats:
- `MM:SS` (e.g., "15:30")
- `HH:MM:SS` (e.g., "1:15:30")

### File URLs
All file URLs should be valid HTTP/HTTPS URLs and publicly accessible.

### Pagination
- Maximum `per_page` value: 100
- Default `per_page` value: 20
- Minimum `page` value: 1

## Security

- All passwords are hashed using PHP's `password_hash()` function
- JWT tokens expire after 1 hour (configurable)
- CORS headers are properly configured
- Input sanitization is applied to all user data
- SQL injection protection through prepared statements
