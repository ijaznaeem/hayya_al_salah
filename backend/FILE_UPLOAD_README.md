# File Upload Feature

## Overview

The admin panel now supports uploading video files, thumbnail images, reference documents, and topic images directly through the interface.

## Features

### Supported File Types

1. **Videos** (for lectures)
   - Formats: MP4, WebM, OGG
   - Max size: 500MB
   - Stored in: `/storage/uploads/videos/`

2. **Images** (for thumbnails and topic images)
   - Formats: JPEG, JPG, PNG, WebP
   - Max size: 5MB
   - Stored in: `/storage/uploads/images/`

3. **Documents** (for reference materials)
   - Formats: PDF, DOC, DOCX
   - Max size: 20MB
   - Stored in: `/storage/uploads/documents/`

### API Endpoints

- `POST /api/upload/video` - Upload video files
- `POST /api/upload/image` - Upload image files
- `POST /api/upload/document` - Upload document files
- `DELETE /api/upload/file` - Delete uploaded files
- `GET /api/upload/files` - List uploaded files
- `GET /uploads/{folder}/{filename}` - Serve uploaded files

### Admin Panel Usage

1. **Adding a New Lecture**:
   - Click "Add Lecture" button
   - Fill in the lecture details
   - Use the "Upload Video" button to upload a video file
   - Use the "Upload Image" button to upload a thumbnail
   - Use the "Upload Document" button to upload reference materials
   - The URLs will be automatically filled in the respective fields

2. **Adding a New Topic**:
   - Click "Add Topic" button
   - Fill in the topic details
   - Use the "Upload Image" button to upload a topic image
   - The URL will be automatically filled in the image URL field

### Security Features

- Admin authentication required for all upload operations
- File type validation based on MIME types
- File size limits enforced
- Secure file serving with path traversal protection
- Unique filename generation to prevent conflicts

### Technical Implementation

#### Upload Controller (`src/Controllers/UploadController.php`)
- Handles file uploads for different types
- Validates file types and sizes
- Generates unique filenames
- Manages file deletion

#### Frontend Integration
- File upload buttons integrated into forms
- Progress indication during uploads
- Image preview functionality
- Error handling and user feedback

#### File Storage Structure
```
storage/uploads/
├── videos/
│   ├── {unique_id}_{timestamp}.mp4
│   └── ...
├── images/
│   ├── {unique_id}_{timestamp}.jpg
│   └── ...
└── documents/
    ├── {unique_id}_{timestamp}.pdf
    └── ...
```

### Configuration

File upload settings can be configured in the `.env` file:

```env
# File Upload Configuration
MAX_UPLOAD_SIZE=524288000  # 500MB in bytes
UPLOAD_PATH=storage/uploads
```

### Testing the Upload Functionality

1. **Start the server**: `php -S localhost:8000 -t public`
2. **Access admin panel**: http://localhost:8000/admin
3. **Login with**: admin@hayyaalsalah.com / admin123
4. **Test uploads**:
   - Go to "Add Lecture" and test video/image/document uploads
   - Go to "Add Topic" and test image upload
   - Verify files are saved in the storage directory
   - Verify URLs are automatically filled in form fields

### Error Handling

The system handles various error conditions:
- Invalid file types
- File size exceeded
- Upload failures
- Authentication errors
- File not found errors

All errors are displayed to the user with appropriate messages.

### Future Enhancements

Potential improvements could include:
- Drag-and-drop upload interface
- Multiple file selection
- Upload progress bars
- File compression for images
- Cloud storage integration (AWS S3, etc.)
- Bulk file management
- File metadata storage in database
