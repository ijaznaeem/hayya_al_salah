<?php

namespace HayyaAlSalah\Controllers;

use HayyaAlSalah\Models\User;
use HayyaAlSalah\Models\Topic;
use HayyaAlSalah\Models\Lecture;

class AdminController extends BaseController
{
    private User $userModel;
    private Topic $topicModel;
    private Lecture $lectureModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->topicModel = new Topic();
        $this->lectureModel = new Lecture();
    }

    public function dashboard(): void
    {
        // Require admin access
        $this->requireAdmin();

        try {
            // Get basic statistics
            $stats = [
                'total_users' => $this->userModel->getCount(),
                'total_topics' => $this->topicModel->getCount(),
                'total_lectures' => $this->lectureModel->getCount(),
                'total_favorites' => $this->userModel->getFavoritesCount()
            ];

            $this->sendResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch dashboard data',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function getUsers(): void
    {
        // Require admin access
        $this->requireAdmin();

        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 20);
        $search = $_GET['search'] ?? '';

        try {
            if (!empty($search)) {
                $users = $this->userModel->search($search);
                $this->sendResponse([
                    'success' => true,
                    'data' => $users,
                    'count' => count($users),
                    'search_query' => $search
                ]);
            } else {
                $offset = ($page - 1) * $perPage;
                $users = $this->userModel->getAll($perPage, $offset);
                
                $response = $this->paginateResults($users, $page, $perPage);
                $response['success'] = true;
                
                $this->sendResponse($response);
            }
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch users',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function getUser(int $id): void
    {
        // Require admin access
        $this->requireAdmin();

        try {
            $user = $this->userModel->getById($id);
            
            if (!$user) {
                $this->sendResponse(['error' => 'User not found'], 404);
            }

            $this->sendResponse([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch user',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function createUser(): void
    {
        // Require admin access
        $this->requireAdmin();
        
        $data = $this->getRequestData();
        
        // Validate required fields
        $required = ['username', 'email', 'password'];
        $missing = $this->validateRequired($data, $required);
        
        if (!empty($missing)) {
            $this->sendResponse([
                'error' => 'Missing required fields',
                'missing' => $missing
            ], 400);
        }

        // Sanitize input
        $username = $this->sanitizeInput($data['username']);
        $email = $this->sanitizeInput($data['email']);
        $password = $data['password'];
        $role = $this->sanitizeInput($data['role'] ?? 'user');

        // Validate email format
        if (!$this->validateEmail($email)) {
            $this->sendResponse(['error' => 'Invalid email format'], 400);
        }

        // Validate password strength
        if (strlen($password) < 6) {
            $this->sendResponse(['error' => 'Password must be at least 6 characters'], 400);
        }

        // Validate role
        if (!in_array($role, ['user', 'admin'])) {
            $this->sendResponse(['error' => 'Invalid role. Must be user or admin'], 400);
        }

        // Check if user already exists
        if ($this->userModel->emailExists($email)) {
            $this->sendResponse(['error' => 'Email already registered'], 409);
        }

        if ($this->userModel->usernameExists($username)) {
            $this->sendResponse(['error' => 'Username already taken'], 409);
        }

        try {
            $userId = $this->userModel->create([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => $role
            ]);

            $user = $this->userModel->getById($userId);

            $this->sendResponse([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to create user',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function updateUser(int $id): void
    {
        // Require admin access
        $this->requireAdmin();
        
        $data = $this->getRequestData();
        
        // Check if user exists
        $existingUser = $this->userModel->getById($id);
        if (!$existingUser) {
            $this->sendResponse(['error' => 'User not found'], 404);
        }

        // Sanitize input
        $updateData = [];
        $allowedFields = ['username', 'email', 'role'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $this->sanitizeInput($data[$field]);
            }
        }

        // Validate email if provided
        if (isset($updateData['email']) && !$this->validateEmail($updateData['email'])) {
            $this->sendResponse(['error' => 'Invalid email format'], 400);
        }

        // Validate role if provided
        if (isset($updateData['role']) && !in_array($updateData['role'], ['user', 'admin'])) {
            $this->sendResponse(['error' => 'Invalid role. Must be user or admin'], 400);
        }

        // Check for duplicates
        if (isset($updateData['email']) && $updateData['email'] !== $existingUser['email']) {
            if ($this->userModel->emailExists($updateData['email'])) {
                $this->sendResponse(['error' => 'Email already registered'], 409);
            }
        }

        if (isset($updateData['username']) && $updateData['username'] !== $existingUser['username']) {
            if ($this->userModel->usernameExists($updateData['username'])) {
                $this->sendResponse(['error' => 'Username already taken'], 409);
            }
        }

        if (empty($updateData)) {
            $this->sendResponse(['error' => 'No valid fields to update'], 400);
        }

        try {
            $success = $this->userModel->update($id, $updateData);
            
            if ($success) {
                $user = $this->userModel->getById($id);
                $this->sendResponse([
                    'success' => true,
                    'message' => 'User updated successfully',
                    'data' => $user
                ]);
            } else {
                $this->sendResponse(['error' => 'Failed to update user'], 500);
            }
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to update user',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function deleteUser(int $id): void
    {
        // Require admin access
        $currentUser = $this->requireAdmin();
        
        // Prevent admin from deleting themselves
        if ($currentUser['id'] == $id) {
            $this->sendResponse(['error' => 'Cannot delete your own account'], 400);
        }

        // Check if user exists
        $existingUser = $this->userModel->getById($id);
        if (!$existingUser) {
            $this->sendResponse(['error' => 'User not found'], 404);
        }

        try {
            $success = $this->userModel->delete($id);
            
            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);
            } else {
                $this->sendResponse(['error' => 'Failed to delete user'], 500);
            }
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to delete user',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function getRecentUsers(): void
    {
        // Require admin access
        $this->requireAdmin();

        $limit = (int) ($_GET['limit'] ?? 10);

        try {
            $users = $this->userModel->getRecent($limit);
            
            $this->sendResponse([
                'success' => true,
                'data' => $users,
                'count' => count($users)
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch recent users',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function getUserActivity(): void
    {
        // Require admin access
        $this->requireAdmin();

        try {
            // This would require additional database tables to track user activity
            // For now, return a placeholder response
            $this->sendResponse([
                'success' => true,
                'data' => [],
                'message' => 'User activity tracking not implemented yet'
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch user activity',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }
}
