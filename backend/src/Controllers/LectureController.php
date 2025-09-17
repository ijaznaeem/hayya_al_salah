<?php

namespace HayyaAlSalah\Controllers;

use HayyaAlSalah\Models\Lecture;

class LectureController extends BaseController
{
    private Lecture $lectureModel;

    public function __construct()
    {
        $this->lectureModel = new Lecture();
    }

    public function index(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 20);
        $search = $_GET['search'] ?? '';

        try {
            if (!empty($search)) {
                $lectures = $this->lectureModel->search($search);
                $this->sendResponse([
                    'success' => true,
                    'data' => $lectures,
                    'count' => count($lectures),
                    'search_query' => $search
                ]);
            } else {
                $offset = ($page - 1) * $perPage;
                $lectures = $this->lectureModel->getAll($perPage, $offset);
                
                $response = $this->paginateResults($lectures, $page, $perPage);
                $response['success'] = true;
                
                $this->sendResponse($response);
            }
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch lectures',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function show(int $id): void
    {
        try {
            $lecture = $this->lectureModel->getById($id);
            
            if (!$lecture) {
                $this->sendResponse(['error' => 'Lecture not found'], 404);
            }

            $this->sendResponse([
                'success' => true,
                'data' => $lecture
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch lecture',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function byTopic(int $topicId): void
    {
        try {
            $lectures = $this->lectureModel->getByTopicId($topicId);
            
            $this->sendResponse([
                'success' => true,
                'data' => $lectures,
                'count' => count($lectures),
                'topic_id' => $topicId
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch lectures',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function store(): void
    {
        // Require admin access for creating lectures
        $this->requireAdmin();
        
        $data = $this->getRequestData();
        
        // Validate required fields
        $required = ['topic_id', 'title', 'description', 'video_url', 'duration', 'lesson_number'];
        $missing = $this->validateRequired($data, $required);
        
        if (!empty($missing)) {
            $this->sendResponse([
                'error' => 'Missing required fields',
                'missing' => $missing
            ], 400);
        }

        // Sanitize input
        $lectureData = [
            'topic_id' => (int) $data['topic_id'],
            'title' => $this->sanitizeInput($data['title']),
            'description' => $this->sanitizeInput($data['description']),
            'video_url' => $this->sanitizeInput($data['video_url']),
            'thumbnail_url' => isset($data['thumbnail_url']) ? $this->sanitizeInput($data['thumbnail_url']) : null,
            'duration' => $this->sanitizeInput($data['duration']),
            'lesson_number' => (int) $data['lesson_number'],
            'reference_file_url' => isset($data['reference_file_url']) ? $this->sanitizeInput($data['reference_file_url']) : null
        ];

        // Validate duration format (MM:SS or HH:MM:SS)
        if (!preg_match('/^(\d{1,2}:)?\d{1,2}:\d{2}$/', $lectureData['duration'])) {
            $this->sendResponse(['error' => 'Invalid duration format. Use MM:SS or HH:MM:SS'], 400);
        }

        try {
            $lectureId = $this->lectureModel->create($lectureData);
            $lecture = $this->lectureModel->getById($lectureId);

            $this->sendResponse([
                'success' => true,
                'message' => 'Lecture created successfully',
                'data' => $lecture
            ], 201);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to create lecture',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function update(int $id): void
    {
        // Require admin access for updating lectures
        $this->requireAdmin();
        
        $data = $this->getRequestData();
        
        // Check if lecture exists
        $existingLecture = $this->lectureModel->getById($id);
        if (!$existingLecture) {
            $this->sendResponse(['error' => 'Lecture not found'], 404);
        }

        // Sanitize input
        $updateData = [];
        $allowedFields = [
            'topic_id', 'title', 'description', 'video_url', 
            'thumbnail_url', 'duration', 'lesson_number', 'reference_file_url'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if (in_array($field, ['topic_id', 'lesson_number'])) {
                    $updateData[$field] = (int) $data[$field];
                } else {
                    $updateData[$field] = $this->sanitizeInput($data[$field]);
                }
            }
        }

        // Validate duration format if provided
        if (isset($updateData['duration']) && !preg_match('/^(\d{1,2}:)?\d{1,2}:\d{2}$/', $updateData['duration'])) {
            $this->sendResponse(['error' => 'Invalid duration format. Use MM:SS or HH:MM:SS'], 400);
        }

        if (empty($updateData)) {
            $this->sendResponse(['error' => 'No valid fields to update'], 400);
        }

        try {
            $success = $this->lectureModel->update($id, $updateData);
            
            if ($success) {
                $lecture = $this->lectureModel->getById($id);
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Lecture updated successfully',
                    'data' => $lecture
                ]);
            } else {
                $this->sendResponse(['error' => 'Failed to update lecture'], 500);
            }
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to update lecture',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function delete(int $id): void
    {
        // Require admin access for deleting lectures
        $this->requireAdmin();
        
        // Check if lecture exists
        $existingLecture = $this->lectureModel->getById($id);
        if (!$existingLecture) {
            $this->sendResponse(['error' => 'Lecture not found'], 404);
        }

        try {
            $success = $this->lectureModel->delete($id);
            
            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Lecture deleted successfully'
                ]);
            } else {
                $this->sendResponse(['error' => 'Failed to delete lecture'], 500);
            }
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to delete lecture',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function popular(): void
    {
        $limit = (int) ($_GET['limit'] ?? 10);

        try {
            $lectures = $this->lectureModel->getPopular($limit);
            
            $this->sendResponse([
                'success' => true,
                'data' => $lectures,
                'count' => count($lectures)
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch popular lectures',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function recent(): void
    {
        $limit = (int) ($_GET['limit'] ?? 10);

        try {
            $lectures = $this->lectureModel->getRecent($limit);
            
            $this->sendResponse([
                'success' => true,
                'data' => $lectures,
                'count' => count($lectures)
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch recent lectures',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }
}
