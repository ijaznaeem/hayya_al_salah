<?php

namespace HayyaAlSalah\Models;

use HayyaAlSalah\Database;

class UserFavorite
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getUserFavorites(int $userId): array
    {
        $sql = "SELECT l.*, t.title as topic_title 
                FROM user_favorites uf 
                JOIN lectures l ON uf.lecture_id = l.id 
                LEFT JOIN topics t ON l.topic_id = t.id 
                WHERE uf.user_id = :user_id 
                ORDER BY uf.created_at DESC";
        
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }

    public function addFavorite(int $userId, int $lectureId): bool
    {
        // Check if already exists
        if ($this->isFavorite($userId, $lectureId)) {
            return false;
        }

        $data = [
            'user_id' => $userId,
            'lecture_id' => $lectureId,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert('user_favorites', $data) > 0;
    }

    public function removeFavorite(int $userId, int $lectureId): bool
    {
        return $this->db->delete('user_favorites', [
            'user_id' => $userId,
            'lecture_id' => $lectureId
        ]);
    }

    public function isFavorite(int $userId, int $lectureId): bool
    {
        $sql = "SELECT id FROM user_favorites 
                WHERE user_id = :user_id AND lecture_id = :lecture_id";
        
        return $this->db->fetch($sql, [
            'user_id' => $userId,
            'lecture_id' => $lectureId
        ]) !== null;
    }

    public function getFavoriteCount(int $lectureId): int
    {
        $sql = "SELECT COUNT(*) as count FROM user_favorites WHERE lecture_id = :lecture_id";
        $result = $this->db->fetch($sql, ['lecture_id' => $lectureId]);
        
        return (int) ($result['count'] ?? 0);
    }

    public function getUserFavoriteIds(int $userId): array
    {
        $sql = "SELECT lecture_id FROM user_favorites WHERE user_id = :user_id";
        $results = $this->db->fetchAll($sql, ['user_id' => $userId]);
        
        return array_column($results, 'lecture_id');
    }

    public function clearUserFavorites(int $userId): bool
    {
        return $this->db->delete('user_favorites', ['user_id' => $userId]);
    }

    public function getMostFavorited(int $limit = 10): array
    {
        $sql = "SELECT l.*, t.title as topic_title, COUNT(uf.id) as favorite_count 
                FROM lectures l 
                LEFT JOIN topics t ON l.topic_id = t.id 
                JOIN user_favorites uf ON l.id = uf.lecture_id 
                GROUP BY l.id 
                ORDER BY favorite_count DESC, l.created_at DESC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, ['limit' => $limit]);
    }
}
