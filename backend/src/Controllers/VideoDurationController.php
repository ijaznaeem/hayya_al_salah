<?php

namespace HayyaAlSalah\Controllers;

use HayyaAlSalah\Database;
use Exception;

class VideoDurationController
{
    private Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Process video durations via web interface
     */
    public function process(): void
    {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $dryRun = $input['dry_run'] ?? false;
            $limit = (int) ($input['limit'] ?? 0);
            $force = $input['force'] ?? false;
            
            $analyzer = new VideoDurationAnalyzer($this->db);
            $result = $analyzer->analyze($dryRun, $limit, $force);
            
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get processing status and statistics
     */
    public function status(): void
    {
        header('Content-Type: application/json');
        
        try {
            $analyzer = new VideoDurationAnalyzer($this->db);
            $status = $analyzer->getStatus();
            
            echo json_encode([
                'success' => true,
                'data' => $status
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Manually set video duration for a specific lecture
     */
    public function setDuration(): void
    {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $lectureId = (int) ($input['lecture_id'] ?? 0);
            $duration = trim($input['duration'] ?? '');
            
            if ($lectureId <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid lecture ID'
                ]);
                return;
            }
            
            if (empty($duration)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Duration is required'
                ]);
                return;
            }
            
            // Validate duration format (MM:SS or HH:MM:SS)
            if (!$this->isValidDurationFormat($duration)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid duration format. Use MM:SS or HH:MM:SS format (e.g., 5:30 or 1:05:30)'
                ]);
                return;
            }
            
            // Update the duration
            $analyzer = new VideoDurationAnalyzer($this->db);
            if ($analyzer->updateVideoDurationPublic($lectureId, $duration)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Duration updated successfully',
                    'data' => [
                        'lecture_id' => $lectureId,
                        'duration' => $duration
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to update duration in database'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Validate duration format
     */
    private function isValidDurationFormat(string $duration): bool
    {
        // Check MM:SS format
        if (preg_match('/^\d{1,2}:\d{2}$/', $duration)) {
            return true;
        }
        
        // Check HH:MM:SS format
        if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $duration)) {
            return true;
        }
        
        return false;
    }
}

/**
 * Video Duration Analyzer Class
 * Extracted from the CLI script for web usage
 */
class VideoDurationAnalyzer
{
    private Database $db;
    private array $stats = [
        'processed' => 0,
        'updated' => 0,
        'errors' => 0,
        'skipped' => 0
    ];

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Main analysis method for web interface (No FFmpeg version)
     */
    public function analyze(bool $dryRun = false, int $limit = 0, bool $force = false): array
    {
        // Get videos that need duration analysis
        $videos = $this->getVideosToProcess($force, $limit);
        
        if (empty($videos)) {
            return [
                'message' => 'No videos found that need duration analysis.',
                'stats' => $this->stats,
                'videos' => [],
                'note' => 'FFmpeg not available - manual duration entry required'
            ];
        }

        $processedVideos = [];

        // Process each video (without FFmpeg - manual processing needed)
        foreach ($videos as $index => $video) {
            $result = $this->processVideoWithoutFFmpeg($video, $index + 1, count($videos), $dryRun);
            $processedVideos[] = $result;
        }

        return [
            'message' => sprintf('Processed %d video(s)', count($videos)),
            'stats' => $this->stats,
            'videos' => $processedVideos,
            'dry_run' => $dryRun,
            'note' => 'FFmpeg not available - using alternative methods'
        ];
    }

    /**
     * Get processing status and statistics
     */
    public function getStatus(): array
    {
        $totalVideos = $this->db->fetch("SELECT COUNT(*) as count FROM lectures WHERE video_url IS NOT NULL AND video_url != ''")['count'];
        
        $needsProcessing = $this->db->fetch("
            SELECT COUNT(*) as count 
            FROM lectures 
            WHERE video_url IS NOT NULL 
            AND video_url != '' 
            AND (duration IS NULL OR duration = '' OR duration NOT LIKE '%:%' OR duration LIKE 'N/A%' OR duration LIKE 'Unknown%')
        ")['count'];
        
        $withDuration = $this->db->fetch("
            SELECT COUNT(*) as count 
            FROM lectures 
            WHERE video_url IS NOT NULL 
            AND video_url != '' 
            AND duration IS NOT NULL 
            AND duration != '' 
            AND duration LIKE '%:%'
        ")['count'];

        return [
            'total_videos' => (int) $totalVideos,
            'needs_processing' => (int) $needsProcessing,
            'with_duration' => (int) $withDuration,
            'ffmpeg_available' => false, // Set to false since server doesn't have FFmpeg
            'alternative_methods' => true,
            'note' => 'Using alternative methods - FFmpeg not required'
        ];
    }

    /**
     * Check if FFmpeg is available
     */
    private function isFFmpegAvailable(): bool
    {
        $output = [];
        $returnCode = 0;
        exec('ffmpeg -version 2>&1', $output, $returnCode);
        
        return $returnCode === 0 && !empty($output);
    }

    /**
     * Get videos that need duration processing
     */
    private function getVideosToProcess(bool $force = false, int $limit = 0): array
    {
        $whereClause = $force 
            ? "WHERE video_url IS NOT NULL AND video_url != ''" 
            : "WHERE video_url IS NOT NULL AND video_url != '' AND (duration IS NULL OR duration = '' OR duration NOT LIKE '%:%' OR duration LIKE 'N/A%' OR duration LIKE 'Unknown%')";
        
        $limitClause = $limit > 0 ? "LIMIT {$limit}" : "";
        
        $sql = "SELECT id, title, video_url, duration 
                FROM lectures 
                {$whereClause} 
                ORDER BY id ASC 
                {$limitClause}";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Process a single video
     */
    private function processVideo(array $video, int $current, int $total, bool $dryRun): array
    {
        $this->stats['processed']++;
        
        $result = [
            'id' => $video['id'],
            'title' => $video['title'],
            'current_duration' => $video['duration'] ?: null,
            'video_url' => $video['video_url'],
            'status' => '',
            'new_duration' => null,
            'error' => null
        ];

        // Skip if not a valid video URL
        if (!$this->isValidVideoUrl($video['video_url'])) {
            $result['status'] = 'skipped';
            $result['error'] = 'Invalid or unsupported video URL';
            $this->stats['skipped']++;
            return $result;
        }

        // Get video duration
        $duration = $this->getVideoDuration($video['video_url']);
        
        if ($duration === null) {
            $result['status'] = 'error';
            $result['error'] = 'Could not retrieve video duration';
            $this->stats['errors']++;
            return $result;
        }

        $result['new_duration'] = $duration;

        // Update database
        if (!$dryRun) {
            if ($this->updateVideoDuration($video['id'], $duration)) {
                $result['status'] = 'updated';
                $this->stats['updated']++;
            } else {
                $result['status'] = 'error';
                $result['error'] = 'Failed to update database';
                $this->stats['errors']++;
            }
        } else {
            $result['status'] = 'dry_run';
            $this->stats['updated']++;
        }

        return $result;
    }

    /**
     * Process a single video without FFmpeg
     */
    private function processVideoWithoutFFmpeg(array $video, int $current, int $total, bool $dryRun): array
    {
        $this->stats['processed']++;
        
        $result = [
            'id' => $video['id'],
            'title' => $video['title'],
            'current_duration' => $video['duration'] ?: null,
            'video_url' => $video['video_url'],
            'status' => '',
            'new_duration' => null,
            'error' => null,
            'method' => 'no_ffmpeg'
        ];

        // Skip if not a valid video URL
        if (!$this->isValidVideoUrl($video['video_url'])) {
            $result['status'] = 'skipped';
            $result['error'] = 'Invalid or unsupported video URL';
            $this->stats['skipped']++;
            return $result;
        }

        // Try alternative methods to get duration
        $duration = $this->getVideoDurationAlternativeMethods($video['video_url']);
        
        if ($duration === null) {
            $result['status'] = 'needs_manual';
            $result['error'] = 'Cannot determine duration without FFmpeg - manual entry required';
            $this->stats['errors']++;
            return $result;
        }

        $result['new_duration'] = $duration;

        // Update database
        if (!$dryRun) {
            if ($this->updateVideoDuration($video['id'], $duration)) {
                $result['status'] = 'updated';
                $this->stats['updated']++;
            } else {
                $result['status'] = 'error';
                $result['error'] = 'Failed to update database';
                $this->stats['errors']++;
            }
        } else {
            $result['status'] = 'dry_run';
            $this->stats['updated']++;
        }

        return $result;
    }

    /**
     * Alternative methods to get video duration without FFmpeg
     */
    private function getVideoDurationAlternativeMethods(string $videoUrl): ?string
    {
        // Method 1: Try to parse filename for duration hints
        $filenameHint = $this->getDurationFromFilename($videoUrl);
        if ($filenameHint) {
            return $filenameHint;
        }

        // Method 2: Check if it's a platform URL that might have metadata
        $platformDuration = $this->getDurationFromPlatform($videoUrl);
        if ($platformDuration) {
            return $platformDuration;
        }

        // Method 3: Try basic cURL head request to get content-length and estimate
        $estimatedDuration = $this->estimateDurationFromSize($videoUrl);
        if ($estimatedDuration) {
            return $estimatedDuration;
        }

        return null;
    }

    /**
     * Try to extract duration from filename
     */
    private function getDurationFromFilename(string $url): ?string
    {
        $filename = basename(parse_url($url, PHP_URL_PATH));
        
        // Look for duration patterns in filename like "5min30s", "10m30s", "2h30m", etc.
        if (preg_match('/(\d+)h(\d+)m(\d+)s/i', $filename, $matches)) {
            $hours = (int)$matches[1];
            $minutes = (int)$matches[2];
            $seconds = (int)$matches[3];
            $total = $hours * 3600 + $minutes * 60 + $seconds;
            return $this->formatDuration($total);
        }
        
        if (preg_match('/(\d+)m(\d+)s/i', $filename, $matches)) {
            $minutes = (int)$matches[1];
            $seconds = (int)$matches[2];
            $total = $minutes * 60 + $seconds;
            return $this->formatDuration($total);
        }
        
        if (preg_match('/(\d+)min/i', $filename, $matches)) {
            $minutes = (int)$matches[1];
            return $this->formatDuration($minutes * 60);
        }
        
        return null;
    }

    /**
     * Try to get duration from platform metadata (placeholder)
     */
    private function getDurationFromPlatform(string $url): ?string
    {
        // This is a placeholder - in reality, you'd need API keys for platforms
        // For now, return null to indicate manual entry is needed
        return null;
    }

    /**
     * Estimate duration from file size (very rough estimate)
     */
    private function estimateDurationFromSize(string $url): ?string
    {
        // Skip this for remote URLs as it's not reliable
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        // For local files, try to get file size
        $localPath = $this->resolveLocalPath($url);
        if (!file_exists($localPath)) {
            return null;
        }

        $fileSize = filesize($localPath);
        if ($fileSize === false || $fileSize < 1024) {
            return null;
        }

        // Very rough estimate: assume 1MB = 1 minute for average quality video
        // This is highly inaccurate but better than nothing
        $estimatedMinutes = round($fileSize / (1024 * 1024));
        
        if ($estimatedMinutes < 1) {
            $estimatedMinutes = 1;
        }
        
        return $this->formatDuration($estimatedMinutes * 60);
    }

    /**
     * Check if URL is a valid video URL
     */
    private function isValidVideoUrl(string $url): bool
    {
        // Skip example URLs
        if (strpos($url, 'example.com') !== false) {
            return false;
        }

        // Check for video file extensions or known video platforms
        $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', 'm4v', '3gp', 'ogv'];
        $videoPlatforms = [
            'youtube.com', 'youtu.be', 'vimeo.com', 'dailymotion.com',
            'wistia.com', 'wistia.net', 'twitch.tv', 'facebook.com',
            'instagram.com', 'tiktok.com', 'streamable.com', 'cloudinary.com'
        ];
        
        $urlLower = strtolower($url);
        
        // Check for direct video file URLs
        foreach ($videoExtensions as $ext) {
            if (strpos($urlLower, '.' . $ext) !== false) {
                return true;
            }
        }
        
        // Check for video platform URLs
        foreach ($videoPlatforms as $platform) {
            if (strpos($urlLower, $platform) !== false) {
                return true;
            }
        }

        // Check if it's a local file path
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $resolvedPath = $this->resolveLocalPath($url);
            return file_exists($resolvedPath);
        }
        
        return false;
    }

    /**
     * Get video duration using FFmpeg
     */
    private function getVideoDuration(string $videoUrl): ?string
    {
        // For local files, check if file exists
        if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
            // Assume it's a local file path
            $filePath = $this->resolveLocalPath($videoUrl);
            if (!file_exists($filePath)) {
                return null;
            }
            $videoUrl = $filePath;
        }

        // Use FFprobe (part of FFmpeg) to get duration
        $command = sprintf(
            'ffprobe -v quiet -show_entries format=duration -of csv=p=0 %s 2>&1',
            escapeshellarg($videoUrl)
        );
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0 || empty($output)) {
            // Try alternative method with FFmpeg
            return $this->getVideoDurationAlternative($videoUrl);
        }
        
        $durationSeconds = (float) trim($output[0]);
        
        if ($durationSeconds <= 0) {
            return null;
        }
        
        return $this->formatDuration($durationSeconds);
    }

    /**
     * Alternative method to get video duration
     */
    private function getVideoDurationAlternative(string $videoUrl): ?string
    {
        $command = sprintf(
            'ffmpeg -i %s 2>&1 | grep "Duration"',
            escapeshellarg($videoUrl)
        );
        
        $output = [];
        exec($command, $output);
        
        foreach ($output as $line) {
            if (preg_match('/Duration: (\d{2}):(\d{2}):(\d{2}\.\d{2})/', $line, $matches)) {
                $hours = (int) $matches[1];
                $minutes = (int) $matches[2];
                $seconds = (float) $matches[3];
                
                $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
                return $this->formatDuration($totalSeconds);
            }
        }
        
        return null;
    }

    /**
     * Resolve local file path
     */
    private function resolveLocalPath(string $path): string
    {
        // If path starts with /uploads/, prepend the public directory
        if (strpos($path, '/uploads/') === 0) {
            return __DIR__ . '/../../public' . $path;
        }
        
        // If it's already an absolute path, return as-is
        if (strpos($path, '/') === 0 || preg_match('/^[A-Z]:/i', $path)) {
            return $path;
        }
        
        // Otherwise, assume it's relative to the backend directory
        return __DIR__ . '/../../' . ltrim($path, '/');
    }

    /**
     * Format duration from seconds to MM:SS or HH:MM:SS
     */
    private function formatDuration(float $totalSeconds): string
    {
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = floor($totalSeconds % 60);
        
        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        } else {
            return sprintf('%d:%02d', $minutes, $seconds);
        }
    }

    /**
     * Update video duration in database
     */
    private function updateVideoDuration(int $lectureId, string $duration): bool
    {
        $sql = "UPDATE lectures SET duration = :duration, updated_at = NOW() WHERE id = :id";
        
        try {
            $stmt = $this->db->query($sql, [
                'duration' => $duration,
                'id' => $lectureId
            ]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Public method to update video duration (for manual updates)
     */
    public function updateVideoDurationPublic(int $lectureId, string $duration): bool
    {
        return $this->updateVideoDuration($lectureId, $duration);
    }
}
