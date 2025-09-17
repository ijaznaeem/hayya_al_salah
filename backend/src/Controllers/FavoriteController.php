<?php

namespace HayyaAlSalah\Controllers;

use HayyaAlSalah\Models\UserFavorite;

class FavoriteController extends BaseController
{
    private UserFavorite $favoriteModel;

    public function __construct()
    {
        $this->favoriteModel = new UserFavorite();
    }

    public function index(): void
    {
        $user = $this->requireAuth();

        try {
            $favorites = $this->favoriteModel->getUserFavorites($user['id']);
            
            $this->sendResponse([
                'success' => true,
                'data' => $favorites,
                'count' => count($favorites)
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch favorites',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function add(int $lectureId): void
    {
        $user = $this->requireAuth();

        try {
            $success = $this->favoriteModel->addFavorite($user['id'], $lectureId);
            
            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Lecture added to favorites'
                ], 201);
            } else {
                $this->sendResponse([
                    'error' => 'Lecture is already in favorites or does not exist'
                ], 409);
            }
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to add to favorites',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function remove(int $lectureId): void
    {
        $user = $this->requireAuth();

        try {
            $success = $this->favoriteModel->removeFavorite($user['id'], $lectureId);
            
            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Lecture removed from favorites'
                ]);
            } else {
                $this->sendResponse([
                    'error' => 'Lecture not found in favorites'
                ], 404);
            }
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to remove from favorites',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function check(int $lectureId): void
    {
        $user = $this->requireAuth();

        try {
            $isFavorite = $this->favoriteModel->isFavorite($user['id'], $lectureId);
            
            $this->sendResponse([
                'success' => true,
                'is_favorite' => $isFavorite,
                'lecture_id' => $lectureId
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to check favorite status',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function clear(): void
    {
        $user = $this->requireAuth();

        try {
            $success = $this->favoriteModel->clearUserFavorites($user['id']);
            
            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'All favorites cleared'
                ]);
            } else {
                $this->sendResponse([
                    'error' => 'No favorites to clear'
                ], 404);
            }
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to clear favorites',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function mostFavorited(): void
    {
        $limit = (int) ($_GET['limit'] ?? 10);

        try {
            $lectures = $this->favoriteModel->getMostFavorited($limit);
            
            $this->sendResponse([
                'success' => true,
                'data' => $lectures,
                'count' => count($lectures)
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch most favorited lectures',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function getFavoriteIds(): void
    {
        $user = $this->requireAuth();

        try {
            $favoriteIds = $this->favoriteModel->getUserFavoriteIds($user['id']);
            
            $this->sendResponse([
                'success' => true,
                'data' => $favoriteIds,
                'count' => count($favoriteIds)
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to fetch favorite IDs',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }
}
