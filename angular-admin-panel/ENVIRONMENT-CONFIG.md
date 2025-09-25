# Environment Configuration Guide

This Angular admin panel supports separate API URLs for different environments (development, staging, production). The environment configuration is automatically handled during build and serve processes.

## Environment Files

### Development (`src/environments/environment.ts`)
**Used for**: Local development and `ng serve`
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000'  // Local PHP backend server
};
```

### Staging (`src/environments/environment.staging.ts`)
**Used for**: Testing deployments and pre-production testing
```typescript
export const environment = {
  production: false,
  staging: true,
  apiUrl: 'https://staging-api.your-domain.com'  // Update to your staging server
};
```

### Production (`src/environments/environment.prod.ts`)
**Used for**: Live production deployments
```typescript
export const environment = {
  production: true,
  apiUrl: 'https://salah.imtiazkausar.org.pk/api'  // Production API URL
};
```

## Build Commands

### Development Build
```bash
# Default development build
ng build
# or explicitly specify development configuration
ng build --configuration=development
```
**API URL**: `http://localhost:8000`

### Staging Build
```bash
ng build --configuration=staging
```
**API URL**: `https://staging-api.your-domain.com`

### Production Build
```bash
ng build --configuration=production
```
**API URL**: `https://salah.imtiazkausar.org.pk/api`
**Deploy to**: Upload contents of `dist/my-app/` to `https://salah.imtiazkausar.org.pk/public/admin/`
**Access URL**: `https://salah.imtiazkausar.org.pk/admin/`

## Serve Commands (Development)

### Development Server (Default)
```bash
ng serve
# or explicitly specify development configuration  
ng serve --configuration=development
```
**API URL**: `http://localhost:8000`  
**Server**: http://localhost:4200

### Staging Server (for Testing)
```bash
ng serve --configuration=staging
```
**API URL**: `https://staging-api.your-domain.com`  
**Server**: http://localhost:4200

### Production Server (for Local Testing)
```bash
ng serve --configuration=production
```
**API URL**: `https://your-production-domain.com`  
**Server**: http://localhost:4200

## API Service Usage

The API service automatically uses the correct URL based on the environment:

```typescript
export class Api {
  private baseUrl = environment.apiUrl;  // Automatically switches based on build config
  
  // All API calls use the environment-specific URL
  login(credentials: LoginRequest): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.baseUrl}/api/auth/login`, credentials);
  }
}
```

## Configuration Steps for Your Environment

1. **Update Production URL**: Edit `src/environments/environment.prod.ts`
   ```typescript
   apiUrl: 'https://api.yourdomain.com'  // Your actual production API
   ```

2. **Update Staging URL** (optional): Edit `src/environments/environment.staging.ts`
   ```typescript
   apiUrl: 'https://staging.yourdomain.com'  // Your staging API
   ```

3. **Build for target environment**:
   ```bash
   # For production deployment
   ng build --configuration=production
   
   # For staging deployment
   ng build --configuration=staging
   ```

## Benefits

✅ **Environment-specific URLs**: Automatically uses correct API URL per environment
✅ **No code changes**: Switch environments without modifying source code  
✅ **Secure production**: No localhost URLs in production builds
✅ **Easy deployment**: Build once for each environment
✅ **Development friendly**: Keeps localhost for development

## Current Status

- ✅ Development: `http://localhost:8000` (for local PHP server)
- ⚠️ Staging: `https://staging-api.your-domain.com` (update this URL)  
- ✅ Production: `https://salah.imtiazkausar.org.pk/api` (configured for your domain)

## Troubleshooting

### Verify Build Environment
To check which environment is being used in a build, look at the browser's Network tab and verify the API calls are going to the expected URL.

### Common Issues
1. **CORS errors**: Ensure your production/staging servers have CORS configured to allow your frontend domain
2. **SSL/HTTPS**: Production and staging URLs should use HTTPS
3. **Authentication**: Make sure your backend servers support the same authentication flow

### Testing Different Configurations
```bash
# Test development build locally
ng build --configuration=development
# Check dist/my-app/main-*.js for localhost:8000

# Test staging build 
ng build --configuration=staging
# Check dist/my-app/main-*.js for staging URL

# Test production build
ng build --configuration=production  
# Check dist/my-app/main-*.js for production URL
```

## Deployment Instructions

### Production Deployment to salah.imtiazkausar.org.pk

1. **Build for Production**:
   ```bash
   ng build --configuration=production
   ```

2. **Upload Files**:
   - Copy all contents from `dist/my-app/` folder
   - Upload to server directory: `https://salah.imtiazkausar.org.pk/public/admin/`
   - Admin panel will be accessible at: `https://salah.imtiazkausar.org.pk/admin/`

3. **Backend Configuration**:
   - Ensure backend API is accessible at: `https://salah.imtiazkausar.org.pk/api`
   - Update backend `.env` with:
     ```properties
     APP_URL=https://salah.imtiazkausar.org.pk
     CORS_ORIGINS=https://salah.imtiazkausar.org.pk
     TIMEZONE=Asia/Karachi
     ```

4. **Verify Deployment**:
   - Visit `https://salah.imtiazkausar.org.pk/admin/`
   - Check that API calls go to `https://salah.imtiazkausar.org.pk/api/`
   - Test login and file uploads

### File Structure on Server
```
public/
├── admin/           # Angular app files (from dist/my-app/)
│   ├── index.html
│   ├── main-*.js
│   ├── polyfills-*.js
│   └── ...
└── api/            # PHP backend API endpoints
    └── index.php
```

## File Structure
```
src/
├── environments/
│   ├── environment.ts          # Development (default)
│   ├── environment.staging.ts  # Staging  
│   └── environment.prod.ts     # Production
└── app/
    └── services/
        └── api.ts              # Uses environment.apiUrl
```
