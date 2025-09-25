# Hayya Al Salah - Angular Admin Panel

A modern Angular 18+ admin panel application for managing the Hayya Al Salah Islamic prayer learning app. This admin interface provides comprehensive tools for managing users, topics, lectures, video processing, and analytics with a beautiful Material Design UI.

## Features

- **🔐 Authentication**: Secure admin login with JWT token support
- **📊 Dashboard**: Interactive overview with statistics and recent activity
- **👥 User Management**: View and manage app users
- **📚 Topics Management**: Complete CRUD operations for learning topics
- **🎥 Lectures Management**: Advanced lecture management with pagination
- **⚡ Video Processing**: Automated video duration analysis using FFmpeg
- **📈 Analytics**: Comprehensive system analytics and reporting
- **📱 Responsive Design**: Mobile-friendly Material Design interface

## Technology Stack

- **Framework**: Angular 18+ (with standalone components)
- **UI Library**: Angular Material with Cyan/Orange theme
- **Language**: TypeScript
- **Build Tool**: Angular CLI
- **Styling**: SCSS with Angular Material theming
- **HTTP Client**: Angular HttpClient for API communication
- **Backend**: PHP REST API integration

## Prerequisites

- Node.js 18+ 
- npm or yarn
- Angular CLI 18+
- Backend API running on `http://localhost:8000`

## Installation

1. Clone the repository
2. Navigate to the admin panel directory:
   ```bash
   cd angular-admin-panel
   ```

3. Install dependencies:
   ```bash
   npm install
   ```

## Development Server

To start a local development server, run:

```bash
ng serve
```

Once the server is running, open your browser and navigate to `http://localhost:4200/`. The application will automatically reload whenever you modify any of the source files.

## Default Credentials

Use the following credentials to login to the admin panel:
- **Username**: admin
- **Password**: admin

## Project Structure

```
src/app/
├── components/
│   ├── dashboard/              # Dashboard with statistics
│   ├── layout/                 # Main layout with sidebar navigation
│   ├── login/                  # Authentication component
│   ├── lectures-management/    # Lectures CRUD with pagination
│   ├── user-management/        # User management interface
│   ├── topics-management/      # Topics CRUD operations
│   ├── video-processing/       # Video duration processing UI
│   └── analytics/              # Analytics dashboard
├── services/
│   ├── auth.ts                 # Authentication service
│   └── api.ts                  # API communication service
├── models/
│   ├── user.model.ts           # User-related interfaces
│   ├── lecture.model.ts        # Lecture-related interfaces
│   └── topic.model.ts          # Topic and processing interfaces
├── guards/
│   └── auth-guard.ts           # Route authentication guard
└── app.routes.ts               # Application routing configuration
```

## Key Components

### Authentication
- JWT token-based authentication
- Automatic token storage and retrieval
- Route protection with auth guards
- Automatic logout on token expiration

### Lectures Management
- Paginated table view with sorting
- CRUD operations (Create, Read, Update, Delete)
- Video duration display and management
- Topic association
- Bulk operations support

### Video Processing
- Real-time processing status monitoring
- Progress tracking with visual indicators
- Error reporting and logging
- Dry-run mode for testing
- Force update options

### Dashboard
- Real-time statistics display
- Recent activity feed
- Quick navigation cards
- Responsive grid layout

## Building

To build the project for production, run:

```bash
ng build
```

This will create a `dist/` directory with the compiled application ready for deployment.

## Code Scaffolding

Angular CLI includes powerful code scaffolding tools. To generate a new component, run:

```bash
ng generate component component-name
```

For a complete list of available schematics (such as `components`, `directives`, or `pipes`), run:

```bash
ng generate --help
```

To build the project run:

```bash
ng build
```

This will compile your project and store the build artifacts in the `dist/` directory. By default, the production build optimizes your application for performance and speed.

## Running unit tests

To execute unit tests with the [Karma](https://karma-runner.github.io) test runner, use the following command:

```bash
ng test
```

## Running end-to-end tests

For end-to-end (e2e) testing, run:

```bash
ng e2e
```

Angular CLI does not come with an end-to-end testing framework by default. You can choose one that suits your needs.

## Additional Resources

For more information on using the Angular CLI, including detailed command references, visit the [Angular CLI Overview and Command Reference](https://angular.dev/tools/cli) page.
