<?php
/**
 * Video Duration Analyzer Script
 * 
 * This script analyzes video files from their URLs and updates the database
 * with accurate video durations. It only processes videos with empty or
 * placeholder durations to avoid overwriting existing accurate data.
 * 
 * Requirements:
 * - FFmpeg installed on the server
 * - PHP exec() function enabled
 * - Network access to video URLs
 * 
 * Usage:
 * php analyze_video_durations.php [--dry-run] [--limit=N] [--force]
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Database.php';

use Dotenv\Dotenv;
use HayyaAlSalah\Database;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

class VideoDurationAnalyzer
{
    private Database $db;
    private bool $dryRun = false;
    private int $limit = 0;
    private bool $force = false;
    private array $stats = [
        'processed' => 0,
        'updated' => 0,
        'errors' => 0,
        'skipped' => 0
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Main execution method
     */
    public function run(array $options = []): void
    {
        $this->parseOptions($options);
        
        echo "=== Video Duration Analyzer ===\n";
        echo "Mode: " . ($this->dryRun ? "DRY RUN" : "LIVE") . "\n";
        echo "Force update: " . ($this->force ? "YES" : "NO") . "\n";
        if ($this->limit > 0) {
            echo "Limit: {$this->limit} videos\n";
        }
        echo "\n";

        // Check if FFmpeg is available
        if (!$this->isFFmpegAvailable()) {
            echo "ERROR: FFmpeg is not available. Please install FFmpeg.\n";
            return;
        }

        // Get videos that need duration analysis
        $videos = $this->getVideosToProcess();
        
        if (empty($videos)) {
            echo "No videos found that need duration analysis.\n";
            return;
        }

        echo "Found " . count($videos) . " video(s) to process.\n\n";

        // Process each video
        foreach ($videos as $index => $video) {
            $this->processVideo($video, $index + 1, count($videos));
            
            // Add small delay to avoid overwhelming the server
            usleep(500000); // 0.5 seconds
        }

        $this->printSummary();
    }

    /**
     * Parse command line options
     */
    private function parseOptions(array $options): void
    {
        foreach ($options as $option) {
            if ($option === '--dry-run') {
                $this->dryRun = true;
            } elseif ($option === '--force') {
                $this->force = true;
            } elseif (strpos($option, '--limit=') === 0) {
                $this->limit = (int) substr($option, 8);
            }
        }
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
    private function getVideosToProcess(): array
    {
        $whereClause = $this->force 
            ? "WHERE video_url IS NOT NULL AND video_url != ''" 
            : "WHERE video_url IS NOT NULL AND video_url != '' AND (duration IS NULL OR duration = '' OR duration NOT LIKE '%:%' OR duration LIKE 'N/A%' OR duration LIKE 'Unknown%')";
        
        $limitClause = $this->limit > 0 ? "LIMIT {$this->limit}" : "";
        
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
    private function processVideo(array $video, int $current, int $total): void
    {
        $this->stats['processed']++;
        
        echo "[$current/$total] Processing: {$video['title']}\n";
        echo "  Current duration: " . ($video['duration'] ?: 'NULL') . "\n";
        echo "  Video URL: {$video['video_url']}\n";

        // Skip if not a valid video URL
        if (!$this->isValidVideoUrl($video['video_url'])) {
            echo "  SKIPPED: Invalid or unsupported video URL\n\n";
            $this->stats['skipped']++;
            return;
        }

        // Get video duration
        $duration = $this->getVideoDuration($video['video_url']);
        
        if ($duration === null) {
            echo "  ERROR: Could not retrieve video duration\n\n";
            $this->stats['errors']++;
            return;
        }

        echo "  Detected duration: {$duration}\n";

        // Update database
        if (!$this->dryRun) {
            if ($this->updateVideoDuration($video['id'], $duration)) {
                echo "  SUCCESS: Database updated\n";
                $this->stats['updated']++;
            } else {
                echo "  ERROR: Failed to update database\n";
                $this->stats['errors']++;
            }
        } else {
            echo "  DRY RUN: Would update duration to {$duration}\n";
            $this->stats['updated']++;
        }
        
        echo "\n";
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
            return __DIR__ . '/public' . $path;
        }
        
        // If it's already an absolute path, return as-is
        if (strpos($path, '/') === 0 || preg_match('/^[A-Z]:/i', $path)) {
            return $path;
        }
        
        // Otherwise, assume it's relative to the backend directory
        return __DIR__ . '/' . ltrim($path, '/');
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
            echo "  Database error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Print execution summary
     */
    private function printSummary(): void
    {
        echo "=== SUMMARY ===\n";
        echo "Videos processed: {$this->stats['processed']}\n";
        echo "Successfully updated: {$this->stats['updated']}\n";
        echo "Errors: {$this->stats['errors']}\n";
        echo "Skipped: {$this->stats['skipped']}\n";
        echo "\n";
        
        if (!$this->dryRun && $this->stats['updated'] > 0) {
            echo "Database has been updated with new video durations.\n";
        } elseif ($this->dryRun) {
            echo "This was a dry run - no changes were made to the database.\n";
            echo "Run without --dry-run to apply changes.\n";
        }
    }
}

// Command line execution
if (php_sapi_name() === 'cli') {
    $options = array_slice($argv, 1);
    $analyzer = new VideoDurationAnalyzer();
    $analyzer->run($options);
} else {
    echo "This script must be run from the command line.\n";
    echo "Usage: php analyze_video_durations.php [--dry-run] [--limit=N] [--force]\n";
    echo "\nOptions:\n";
    echo "  --dry-run    Show what would be done without making changes\n";
    echo "  --limit=N    Process only N videos (for testing)\n";
    echo "  --force      Process all videos, even those with existing durations\n";
}
