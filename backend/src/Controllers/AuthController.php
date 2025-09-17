<?php

namespace HayyaAlSalah\Controllers;

use HayyaAlSalah\Models\User;

class AuthController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function register(): void
    {
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

        // Validate email format
        if (!$this->validateEmail($email)) {
            $this->sendResponse(['error' => 'Invalid email format'], 400);
        }

        // Validate password strength
        if (strlen($password) < 6) {
            $this->sendResponse(['error' => 'Password must be at least 6 characters'], 400);
        }

        // Check if user already exists
        if ($this->userModel->emailExists($email)) {
            $this->sendResponse(['error' => 'Email already registered'], 409);
        }

        if ($this->userModel->usernameExists($username)) {
            $this->sendResponse(['error' => 'Username already taken'], 409);
        }

        // Create user
        try {
            $userId = $this->userModel->create([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => 'user'
            ]);

            $user = $this->userModel->getById($userId);
            $token = $this->generateJWT($user);

            $this->sendResponse([
                'message' => 'User registered successfully',
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            $this->sendResponse(['error' => 'Failed to create user'], 500);
        }
    }

    public function login(): void
    {
        $data = $this->getRequestData();
        
        // Validate required fields
        $required = ['email', 'password'];
        $missing = $this->validateRequired($data, $required);
        
        if (!empty($missing)) {
            $this->sendResponse([
                'error' => 'Missing required fields',
                'missing' => $missing
            ], 400);
        }

        $email = $this->sanitizeInput($data['email']);
        $password = $data['password'];

        // Get user by email
        $user = $this->userModel->getByEmail($email);
        
        if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
            $this->sendResponse(['error' => 'Invalid credentials'], 401);
        }

        // Update last login
        $this->userModel->updateLastLogin($user['id']);

        // Remove password hash from response
        unset($user['password_hash']);

        $token = $this->generateJWT($user);

        $this->sendResponse([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function refresh(): void
    {
        $user = $this->requireAuth();
        
        // Get fresh user data
        $freshUser = $this->userModel->getById($user['id']);
        
        if (!$freshUser) {
            $this->sendResponse(['error' => 'User not found'], 404);
        }

        $token = $this->generateJWT($freshUser);

        $this->sendResponse([
            'message' => 'Token refreshed',
            'user' => $freshUser,
            'token' => $token
        ]);
    }

    public function profile(): void
    {
        $user = $this->requireAuth();
        
        $userData = $this->userModel->getById($user['id']);
        
        if (!$userData) {
            $this->sendResponse(['error' => 'User not found'], 404);
        }

        $this->sendResponse([
            'user' => $userData
        ]);
    }

    public function updateProfile(): void
    {
        $user = $this->requireAuth();
        $data = $this->getRequestData();

        $allowedFields = ['username', 'email'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $this->sanitizeInput($data[$field]);
            }
        }

        // Validate email if provided
        if (isset($updateData['email']) && !$this->validateEmail($updateData['email'])) {
            $this->sendResponse(['error' => 'Invalid email format'], 400);
        }

        // Check for duplicates
        if (isset($updateData['email']) && $updateData['email'] !== $user['email']) {
            if ($this->userModel->emailExists($updateData['email'])) {
                $this->sendResponse(['error' => 'Email already registered'], 409);
            }
        }

        if (isset($updateData['username']) && $updateData['username'] !== $user['username']) {
            if ($this->userModel->usernameExists($updateData['username'])) {
                $this->sendResponse(['error' => 'Username already taken'], 409);
            }
        }

        if (empty($updateData)) {
            $this->sendResponse(['error' => 'No valid fields to update'], 400);
        }

        try {
            $success = $this->userModel->update($user['id'], $updateData);
            
            if ($success) {
                $updatedUser = $this->userModel->getById($user['id']);
                $this->sendResponse([
                    'message' => 'Profile updated successfully',
                    'user' => $updatedUser
                ]);
            } else {
                $this->sendResponse(['error' => 'Failed to update profile'], 500);
            }
        } catch (\Exception $e) {
            $this->sendResponse(['error' => 'Failed to update profile'], 500);
        }
    }
}
