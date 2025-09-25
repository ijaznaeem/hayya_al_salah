# Video Duration Analyzer Documentation

## Overview

The `analyze_video_durations.php` script automatically analyzes video files from their URLs and updates the database with accurate video durations. It uses FFmpeg to extract duration information from both local and remote video files.

## Features

- **Smart Processing**: Only processes videos with empty, null, or placeholder durations by default
- **Dry Run Mode**: Test the script without making actual database changes
- **Batch Processing**: Process videos in batches with configurable limits
- **Multiple Platforms**: Supports various video platforms and file formats
- **Error Handling**: Robust error handling with detailed logging
- **Force Mode**: Option to reprocess all videos regardless of existing duration

## Requirements

- **FFmpeg**: Must be installed and accessible via command line
- **PHP**: Version 7.4 or higher
- **Database**: MySQL database with lectures table
- **Network Access**: For processing remote video URLs
- **PHP Extensions**: PDO, exec() function enabled

## Installation & Setup

1. Ensure FFmpeg is installed:
   ```bash
   ffmpeg -version
   ```

2. Verify PHP exec() function is enabled in php.ini

3. Make sure the `.env` file contains database credentials

## Usage

### Basic Commands

```bash
# Dry run - see what would be processed without making changes
php analyze_video_durations.php --dry-run

# Process all videos that need duration analysis
php analyze_video_durations.php

# Process only first 10 videos
php analyze_video_durations.php --limit=10

# Force reprocess all videos (even those with existing durations)
php analyze_video_durations.php --force

# Combine options
php analyze_video_durations.php --dry-run --limit=5 --force
```

### Command Options

- `--dry-run`: Show what would be done without making database changes
- `--limit=N`: Process only N videos (useful for testing)
- `--force`: Process all videos, even those with existing durations

## How It Works

### Video Selection Logic

**Default Mode** (without --force):
- Videos with `duration IS NULL`
- Videos with empty duration (`duration = ''`)
- Videos with invalid duration format (`duration NOT LIKE '%:%'`)
- Videos with placeholder values (`duration LIKE 'N/A%'` or `'Unknown%'`)

**Force Mode** (with --force):
- All videos with valid video URLs

### Duration Extraction Process

1. **URL Validation**: Checks if the URL is a valid video URL
2. **FFprobe Method**: Uses `ffprobe` to extract duration (primary method)
3. **FFmpeg Fallback**: Falls back to `ffmpeg` if ffprobe fails
4. **Duration Formatting**: Converts seconds to `MM:SS` or `HH:MM:SS` format

### Supported Video Sources

**File Extensions**:
- mp4, avi, mov, wmv, flv, webm, mkv, m4v, 3gp, ogv

**Video Platforms**:
- YouTube (youtube.com, youtu.be)
- Vimeo (vimeo.com)
- Dailymotion (dailymotion.com)
- Wistia (wistia.com, wistia.net)
- Twitch (twitch.tv)
- Facebook (facebook.com)
- Instagram (instagram.com)
- TikTok (tiktok.com)
- Streamable (streamable.com)
- Cloudinary (cloudinary.com)

**Local Files**:
- Absolute paths
- Relative paths (resolved relative to backend directory)
- Upload directory paths (`/uploads/...`)

## Example Output

```
=== Video Duration Analyzer ===
Mode: DRY RUN
Force update: NO
Limit: 5 videos

Found 3 video(s) to process.

[1/3] Processing: Introduction to Prayer
  Current duration: NULL
  Video URL: https://example.com/video.mp4
  Detected duration: 15:30
  DRY RUN: Would update duration to 15:30

[2/3] Processing: Advanced Tajweed
  Current duration: 
  Video URL: /uploads/videos/tajweed.mp4
  Detected duration: 25:45
  DRY RUN: Would update duration to 25:45

[3/3] Processing: Prayer Positions
  Current duration: Unknown
  Video URL: https://youtube.com/watch?v=example
  ERROR: Could not retrieve video duration

=== SUMMARY ===
Videos processed: 3
Successfully updated: 2
Errors: 1
Skipped: 0

This was a dry run - no changes were made to the database.
Run without --dry-run to apply changes.
```

## Database Schema

The script works with the `lectures` table and expects these columns:

```sql
- id (PRIMARY KEY)
- title (VARCHAR)
- video_url (VARCHAR) - The video URL to analyze
- duration (VARCHAR) - Where the calculated duration is stored
- updated_at (TIMESTAMP) - Updated when duration is changed
```

## Error Handling

The script handles various error scenarios:

- **FFmpeg Not Available**: Checks for FFmpeg installation before starting
- **Invalid URLs**: Skips example.com URLs and invalid formats
- **Network Issues**: Handles connection timeouts for remote videos
- **File Not Found**: Validates local file existence
- **Database Errors**: Catches and reports SQL errors
- **Video Processing Errors**: Handles cases where duration cannot be extracted

## Performance Considerations

- **Rate Limiting**: 0.5-second delay between video processing to avoid overwhelming servers
- **Batch Processing**: Use `--limit` option for large datasets
- **Dry Run First**: Always test with `--dry-run` before processing large batches
- **Memory Usage**: Processes one video at a time to minimize memory usage

## Troubleshooting

### Common Issues

1. **FFmpeg Not Found**:
   ```
   ERROR: FFmpeg is not available. Please install FFmpeg.
   ```
   Solution: Install FFmpeg and ensure it's in the system PATH

2. **Database Connection Issues**:
   ```
   Database connection failed: SQLSTATE[...]
   ```
   Solution: Check `.env` file and database credentials

3. **Permission Errors**:
   ```
   exec() has been disabled for security reasons
   ```
   Solution: Enable exec() function in php.ini

4. **Video Processing Errors**:
   ```
   ERROR: Could not retrieve video duration
   ```
   Solutions:
   - Check if video URL is accessible
   - Verify video format is supported
   - Check network connectivity

### Debug Tips

1. Start with `--dry-run --limit=1` to test a single video
2. Use `--force --limit=5` to test with known working videos
3. Check FFmpeg installation: `ffmpeg -version`
4. Test video URL manually: `ffprobe "your-video-url"`

## Security Considerations

- **URL Validation**: Only processes whitelisted video platforms and extensions
- **Path Traversal**: Prevents directory traversal attacks for local files
- **Shell Injection**: Uses `escapeshellarg()` for all shell commands
- **Example URLs**: Automatically skips example.com URLs

## Maintenance

- **Regular Monitoring**: Check script output for errors
- **Database Cleanup**: Periodically verify duration accuracy
- **URL Updates**: Monitor for broken video URLs
- **Platform Support**: Update platform list as needed

## Advanced Usage

### Custom Video Platforms

To add support for new video platforms, update the `$videoPlatforms` array in the `isValidVideoUrl()` method:

```php
$videoPlatforms = [
    'youtube.com', 'youtu.be', 'vimeo.com',
    'your-new-platform.com'  // Add new platforms here
];
```

### Custom Duration Format

The script outputs duration in `MM:SS` or `HH:MM:SS` format. To modify this, update the `formatDuration()` method.

### Integration with Cron

For automated processing, add to crontab:

```bash
# Run every day at 2 AM
0 2 * * * cd /path/to/backend && php analyze_video_durations.php --limit=50
```

## License

This script is part of the Hayya Al Salah project and follows the same licensing terms.
