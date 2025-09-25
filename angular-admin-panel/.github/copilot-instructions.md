# Copilot Instructions for Hayya Al Salah Angular Admin Panel

<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

## Project Overview
This is an Angular 18+ admin panel application for managing the Hayya Al Salah Islamic prayer learning app. The admin panel provides a modern web interface for managing users, topics, lectures, video processing, and analytics.

## Technology Stack
- **Framework**: Angular 18 (with standalone components)
- **UI Library**: Angular Material
- **Language**: TypeScript
- **Build Tool**: Angular CLI
- **Styling**: SCSS with Angular Material theming
- **HTTP Client**: Angular HttpClient for API communication

## Project Structure
```
src/
├── app/
│   ├── components/           # UI Components
│   │   ├── dashboard/        # Dashboard overview
│   │   ├── layout/           # Main layout with sidebar
│   │   ├── login/            # Authentication
│   │   ├── lectures-management/  # Lectures CRUD with pagination
│   │   ├── user-management/      # User management
│   │   ├── topics-management/    # Topics CRUD
│   │   ├── video-processing/     # Video duration processing
│   │   └── analytics/            # Analytics dashboard
│   ├── services/             # Business logic services
│   │   ├── auth.ts           # Authentication service
│   │   └── api.ts            # API communication service
│   ├── models/               # TypeScript interfaces
│   │   ├── user.model.ts     # User-related models
│   │   ├── lecture.model.ts  # Lecture-related models
│   │   └── topic.model.ts    # Topic and processing models
│   ├── guards/               # Route guards
│   │   └── auth-guard.ts     # Authentication guard
│   └── app.routes.ts         # Application routing
```

## Key Features
1. **Authentication**: Admin login with JWT token support
2. **Dashboard**: Overview with statistics and recent activity
3. **User Management**: View and manage app users
4. **Topics Management**: CRUD operations for learning topics
5. **Lectures Management**: CRUD operations with pagination for video lectures
6. **Video Processing**: Analyze and update video durations using FFmpeg backend
7. **Analytics**: System analytics and reporting
8. **Responsive Design**: Mobile-friendly Material Design interface

## Backend API Integration
The admin panel communicates with a PHP backend API located at `http://localhost:8000`. Key endpoints include:
- `/admin/login` - Authentication
- `/users` - User management
- `/topics` - Topic management  
- `/lectures` - Lecture management with pagination
- `/admin/video-processing/*` - Video processing endpoints
- `/admin/analytics` - Analytics data

## Development Guidelines

### Component Architecture
- Use Angular 18 standalone components (no NgModule)
- Import required Angular Material modules directly in components
- Use Angular's new control flow syntax (`@if`, `@for`, etc.) instead of structural directives
- Implement proper error handling and loading states

### Styling
- Use Angular Material components and theming
- Follow Material Design principles
- Use SCSS for component-specific styles
- Maintain responsive design for mobile compatibility

### State Management
- Use services for shared state and business logic
- Implement proper error handling in HTTP requests
- Use RxJS Observables for reactive programming
- Store authentication state in localStorage

### Code Quality
- Use TypeScript strict mode
- Implement proper type definitions for all API responses
- Follow Angular style guide conventions
- Use meaningful variable and method names
- Add proper error handling and user feedback

## Common Patterns

### API Service Usage
```typescript
// Always handle both success and error cases
this.api.getLectures(page, perPage).subscribe({
  next: (response) => {
    // Handle success
  },
  error: (error) => {
    // Handle error with user feedback
    this.snackBar.open('Error message', 'Close', { duration: 3000 });
  }
});
```

### Loading States
```typescript
// Show loading indicators for better UX
this.isLoading = true;
this.api.someOperation().subscribe({
  next: (response) => {
    this.isLoading = false;
    // Handle success
  },
  error: (error) => {
    this.isLoading = false;
    // Handle error
  }
});
```

### Angular Material Table with Pagination
```typescript
// Implement proper pagination handling
onPageChange(event: PageEvent) {
  this.currentPage = event.pageIndex;
  this.pageSize = event.pageSize;
  this.loadData();
}
```

## Important Notes
- Always validate user permissions for admin operations
- Implement proper error boundaries and fallback UI
- Use Angular Material's accessibility features
- Follow security best practices for authentication
- Maintain consistent naming conventions across the application
- Test components with different screen sizes and devices

When working on this project, prioritize user experience, security, and maintainability.
