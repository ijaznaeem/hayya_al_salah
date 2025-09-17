<?php

namespace HayyaAlSalah\Controllers;

use HayyaAlSalah\Models\Topic;

class TopicController extends BaseController
{
    private Topic $topicModel;

    public function __construct()
    {
        $this->topicModel = new Topic();
    }

    public function index(): void
    {
        try {
            $topics = $this->topicModel->getAll();
            
            $this->sendResponse([
                'success' => true,
                'data' => $topics,
                'count' => count($topics)
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch topics',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function show(int $id): void
    {
        try {
            $topic = $this->topicModel->getById($id);
            
            if (!$topic) {
                $this->sendResponse(['error' => 'Topic not found'], 404);
            }

            $this->sendResponse([
                'success' => true,
                'data' => $topic
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch topic',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function store(): void
    {
        // Require admin access for creating topics
        $this->requireAdmin();
        
        $data = $this->getRequestData();
        
        // Validate required fields
        $required = ['title', 'description'];
        $missing = $this->validateRequired($data, $required);
        
        if (!empty($missing)) {
            $this->sendResponse([
                'error' => 'Missing required fields',
                'missing' => $missing
            ], 400);
        }

        // Sanitize input
        $topicData = [
            'title' => $this->sanitizeInput($data['title']),
            'description' => $this->sanitizeInput($data['description']),
            'image_url' => isset($data['image_url']) ? $this->sanitizeInput($data['image_url']) : null
        ];

        try {
            $topicId = $this->topicModel->create($topicData);
            $topic = $this->topicModel->getById($topicId);

            $this->sendResponse([
                'success' => true,
                'message' => 'Topic created successfully',
                'data' => $topic
            ], 201);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to create topic',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function update(int $id): void
    {
        // Require admin access for updating topics
        $this->requireAdmin();
        
        $data = $this->getRequestData();
        
        // Check if topic exists
        $existingTopic = $this->topicModel->getById($id);
        if (!$existingTopic) {
            $this->sendResponse(['error' => 'Topic not found'], 404);
        }

        // Sanitize input
        $updateData = [];
        $allowedFields = ['title', 'description', 'image_url'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $this->sanitizeInput($data[$field]);
            }
        }

        if (empty($updateData)) {
            $this->sendResponse(['error' => 'No valid fields to update'], 400);
        }

        try {
            $success = $this->topicModel->update($id, $updateData);
            
            if ($success) {
                $topic = $this->topicModel->getById($id);
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Topic updated successfully',
                    'data' => $topic
                ]);
            } else {
                $this->sendResponse(['error' => 'Failed to update topic'], 500);
            }
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to update topic',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function delete(int $id): void
    {
        // Require admin access for deleting topics
        $this->requireAdmin();
        
        // Check if topic exists
        $existingTopic = $this->topicModel->getById($id);
        if (!$existingTopic) {
            $this->sendResponse(['error' => 'Topic not found'], 404);
        }

        try {
            $success = $this->topicModel->delete($id);
            
            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Topic deleted successfully'
                ]);
            } else {
                $this->sendResponse(['error' => 'Failed to delete topic'], 500);
            }
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to delete topic',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }
}
