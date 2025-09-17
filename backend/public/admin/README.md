# Hayya Al Salah Admin Panel

A comprehensive web-based admin panel for managing the Hayya Al Salah Islamic Learning Platform.

## Features

### 🎯 Dashboard
- Real-time statistics (users, topics, lectures, favorites)
- Recent users overview
- Popular lectures analytics
- Quick access to all management sections

### 👥 User Management
- View all registered users
- Create new admin/user accounts
- Edit user profiles and roles
- Delete user accounts (with safety checks)
- Search users by username or email
- User activity tracking (planned)

### 📚 Topics Management
- Create, edit, and delete learning topics
- View topic statistics (lesson count, duration)
- Bulk topic operations
- Image URL management for topics

### 🎥 Lectures Management
- Complete CRUD operations for lectures
- Filter lectures by topic
- Search lectures by title/description
- Video and thumbnail URL management
- Duration tracking and validation
- Lesson numbering system
- Reference file attachments

### 📊 Analytics & Reports
- Most favorited lectures
- User engagement metrics
- Content performance analytics
- Export capabilities (planned)

## Setup Instructions

### Prerequisites
- PHP 8.0+ with MySQL/MariaDB
- Composer for dependencies
- Web server (Apache/Nginx) or PHP built-in server

### Installation

1. **Ensure Backend is Running**
   ```bash
   cd backend
   composer install
   php -S localhost:8000 -t public
   ```

2. **Access Admin Panel**
   Open your browser and navigate to:
   ```
   http://localhost:8000/admin/
   ```

3. **Create Admin User**
   First, create an admin user via API or database:
   ```bash
   # Via API (register then update role in database)
   curl -X POST http://localhost:8000/api/auth/register \
     -H "Content-Type: application/json" \
     -d '{"username":"admin","email":"admin@example.com","password":"admin123"}'
   
   # Then update role in database
   UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';
   ```

4. **Login to Admin Panel**
   Use your admin credentials to access the panel.

## API Endpoints Used

### Authentication
- `POST /api/auth/login` - Admin login
- `GET /api/auth/profile` - Get current user profile
- `POST /api/auth/refresh` - Refresh JWT token

### Topics Management
- `GET /api/topics` - List all topics
- `GET /api/topics/{id}` - Get specific topic
- `POST /api/topics` - Create new topic (admin only)
- `PUT /api/topics/{id}` - Update topic (admin only)
- `DELETE /api/topics/{id}` - Delete topic (admin only)

### Lectures Management
- `GET /api/lectures` - List all lectures (with pagination/search)
- `GET /api/lectures/{id}` - Get specific lecture
- `GET /api/lectures/popular` - Get popular lectures
- `GET /api/topics/{id}/lectures` - Get lectures by topic
- `POST /api/lectures` - Create new lecture (admin only)
- `PUT /api/lectures/{id}` - Update lecture (admin only)
- `DELETE /api/lectures/{id}` - Delete lecture (admin only)

### User Management
- `GET /api/users` - List all users (admin only)
- `GET /api/admin/users/{id}` - Get specific user (admin only)
- `POST /api/admin/users` - Create new user (admin only)
- `PUT /api/admin/users/{id}` - Update user (admin only)
- `DELETE /api/admin/users/{id}` - Delete user (admin only)

### Analytics
- `GET /api/stats/most-favorited` - Get most favorited lectures
- `GET /api/admin/dashboard` - Get dashboard statistics

## Security Features

### Authentication & Authorization
- JWT-based authentication
- Role-based access control (admin/user)
- Session management with token refresh
- Secure logout functionality

### Input Validation
- Server-side validation for all forms
- XSS protection through input sanitization
- SQL injection prevention via prepared statements
- File upload validation (for URLs)

### UI Security
- CSRF protection considerations
- Secure modal handling
- Safe DOM manipulation
- Error message sanitization

## File Structure

```
backend/public/admin/
├── index.html          # Main admin interface
├── styles.css          # Complete styling system
├── script.js           # JavaScript functionality
└── README.md          # This documentation
```

## Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Responsive Design

The admin panel is fully responsive and works on:
- Desktop computers (1920px+)
- Laptops (1024px - 1919px)
- Tablets (768px - 1023px)
- Mobile devices (320px - 767px)

## Customization

### Theming
The CSS uses CSS custom properties (variables) for easy theming:
```css
:root {
    --primary-color: #059669;
    --secondary-color: #64748b;
    --success-color: #10b981;
    --error-color: #ef4444;
    /* ... more variables */
}
```

### Adding New Sections
1. Add navigation item in sidebar
2. Create content section in HTML
3. Add corresponding JavaScript handlers
4. Implement backend API endpoints if needed

## Troubleshooting

### Common Issues

**Login Issues**
- Ensure user has 'admin' role in database
- Check JWT_SECRET is set in .env file
- Verify API endpoints are accessible

**Data Not Loading**
- Check browser console for JavaScript errors
- Verify API endpoints return valid JSON
- Ensure CORS headers are properly configured

**Styling Issues**
- Clear browser cache
- Check for CSS conflicts
- Verify Font Awesome and Google Fonts are loading

### Debug Mode
Enable debug mode in backend `.env`:
```
APP_DEBUG=true
```

This will show detailed error messages in API responses.

## Contributing

When adding new features:
1. Follow existing code patterns
2. Add proper error handling
3. Update this documentation
4. Test on multiple screen sizes
5. Ensure security best practices

## License

This admin panel is part of the Hayya Al Salah project and follows the same licensing terms.

## Support

For issues or questions:
1. Check this documentation
2. Review browser console for errors
3. Check backend logs
4. Verify database connectivity and structure
