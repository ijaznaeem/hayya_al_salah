# Hayya Al Salah Backend API

A PHP backend API for the Hayya Al Salah Islamic Learning Flutter application.

## Features

- **Topics Management**: CRUD operations for video topics
- **Lectures API**: Manage lectures with relations to topics
- **Optional Authentication**: JWT-based login system
- **RESTful API**: Clean and well-documented endpoints

## Requirements

- PHP 8.0+
- MySQL 5.7+ or MariaDB
- Composer

## Installation

1. Install dependencies:
```bash
composer install
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Configure your database settings in `.env`

4. Run database migrations:
```bash
php scripts/migrate.php
```

5. Start the development server:
```bash
php -S localhost:8000 -t public
```

## API Endpoints

### Authentication (Optional)
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `POST /api/auth/refresh` - Refresh JWT token

### Topics
- `GET /api/topics` - Get all topics
- `GET /api/topics/{id}` - Get specific topic
- `POST /api/topics` - Create new topic (admin)
- `PUT /api/topics/{id}` - Update topic (admin)
- `DELETE /api/topics/{id}` - Delete topic (admin)

### Lectures
- `GET /api/lectures` - Get all lectures
- `GET /api/lectures/{id}` - Get specific lecture
- `GET /api/topics/{id}/lectures` - Get lectures by topic
- `POST /api/lectures` - Create new lecture (admin)
- `PUT /api/lectures/{id}` - Update lecture (admin)
- `DELETE /api/lectures/{id}` - Delete lecture (admin)

### User Favorites (Requires Auth)
- `GET /api/user/favorites` - Get user's favorite lectures
- `POST /api/user/favorites/{lectureId}` - Add lecture to favorites
- `DELETE /api/user/favorites/{lectureId}` - Remove from favorites

## Database Schema

### Topics
- id (Primary Key)
- title
- description
- image_url
- created_at
- updated_at

### Lectures
- id (Primary Key)
- topic_id (Foreign Key)
- title
- description
- video_url
- thumbnail_url
- duration
- lesson_number
- reference_file_url
- created_at
- updated_at

### Users (Optional)
- id (Primary Key)
- username
- email
- password_hash
- role (user/admin)
- created_at
- updated_at

### User Favorites (Optional)
- id (Primary Key)
- user_id (Foreign Key)
- lecture_id (Foreign Key)
- created_at
