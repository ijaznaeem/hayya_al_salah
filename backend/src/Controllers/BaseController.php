<?php

namespace HayyaAlSalah\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

abstract class BaseController
{
    protected function sendResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function getRequestData(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    protected function validateRequired(array $data, array $required): array
    {
        $missing = [];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        return $missing;
    }

    protected function getCurrentUser(): ?array
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }

        try {
            $token = $matches[1];
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], $_ENV['JWT_ALGORITHM']));
            
            return [
                'id' => $decoded->user_id,
                'username' => $decoded->username,
                'email' => $decoded->email,
                'role' => $decoded->role
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function requireAuth(): array
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->sendResponse(['error' => 'Authentication required'], 401);
        }
        return $user;
    }

    protected function requireAdmin(): array
    {
        $user = $this->requireAuth();
        if ($user['role'] !== 'admin') {
            $this->sendResponse(['error' => 'Admin access required'], 403);
        }
        return $user;
    }

    protected function generateJWT(array $user): string
    {
        $payload = [
            'iss' => $_ENV['APP_URL'],
            'aud' => $_ENV['APP_URL'],
            'iat' => time(),
            'exp' => time() + (int)$_ENV['JWT_EXPIRY'],
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        return JWT::encode($payload, $_ENV['JWT_SECRET'], $_ENV['JWT_ALGORITHM']);
    }

    protected function sanitizeInput(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    protected function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function paginateResults(array $items, int $page = 1, int $perPage = 20): array
    {
        $total = count($items);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $paginatedItems = array_slice($items, $offset, $perPage);
        
        return [
            'data' => $paginatedItems,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
    }

    /**
     * Convert relative file path to absolute URL
     */
    protected function convertToAbsoluteUrl(?string $relativePath): ?string
    {
        if (empty($relativePath)) {
            return null;
        }

        // If it's already an absolute URL, return as-is
        if (preg_match('/^https?:\/\//', $relativePath)) {
            return $relativePath;
        }

        // Get base URL from environment
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/');
        
        // Remove leading slash from relative path if present
        $relativePath = ltrim($relativePath, '/');
        
        return $baseUrl . '/' . $relativePath;
    }

    /**
     * Process lecture data to convert file paths to absolute URLs
     */
    protected function processLectureUrls(array $lecture): array
    {
        if (isset($lecture['video_url'])) {
            $lecture['video_url'] = $this->convertToAbsoluteUrl($lecture['video_url']);
        }
        
        if (isset($lecture['thumbnail_url'])) {
            $lecture['thumbnail_url'] = $this->convertToAbsoluteUrl($lecture['thumbnail_url']);
        }
        
        if (isset($lecture['reference_file_url'])) {
            $lecture['reference_file_url'] = $this->convertToAbsoluteUrl($lecture['reference_file_url']);
        }

        return $lecture;
    }

    /**
     * Process multiple lectures to convert file paths to absolute URLs
     */
    protected function processLecturesUrls(array $lectures): array
    {
        return array_map([$this, 'processLectureUrls'], $lectures);
    }

    /**
     * Process topic data to convert image path to absolute URL
     */
    protected function processTopicUrls(array $topic): array
    {
        if (isset($topic['image_url'])) {
            $topic['image_url'] = $this->convertToAbsoluteUrl($topic['image_url']);
        }

        return $topic;
    }

    /**
     * Process multiple topics to convert image paths to absolute URLs
     */
    protected function processTopicsUrls(array $topics): array
    {
        return array_map([$this, 'processTopicUrls'], $topics);
    }
}
