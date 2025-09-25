<?php

namespace HayyaAlSalah\Models;

use HayyaAlSalah\Database;

class Lecture
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT l.*, t.title as topic_title 
                FROM lectures l 
                LEFT JOIN topics t ON l.topic_id = t.id 
                ORDER BY l.lesson_number ASC, l.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        return $this->db->fetchAll($sql, [
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    public function getAllForPagination(): array
    {
        $sql = "SELECT l.*, t.title as topic_title 
                FROM lectures l 
                LEFT JOIN topics t ON l.topic_id = t.id 
                ORDER BY l.id ASC";
        
        return $this->db->fetchAll($sql);
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT l.*, t.title as topic_title 
                FROM lectures l 
                LEFT JOIN topics t ON l.topic_id = t.id 
                WHERE l.id = :id";
        
        $lecture = $this->db->fetch($sql, ['id' => $id]);
        
        if ($lecture) {
            $lecture['related_lessons'] = $this->getRelatedLectures($lecture['topic_id'], $id);
        }
        
        return $lecture;
    }

    public function getByTopicId(int $topicId): array
    {
        $sql = "SELECT * FROM lectures 
                WHERE topic_id = :topic_id 
                ORDER BY lesson_number ASC";
        
        return $this->db->fetchAll($sql, ['topic_id' => $topicId]);
    }

    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->insert('lectures', $data);
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('lectures', $data, ['id' => $id]);
    }

    public function delete(int $id): bool
    {
        // First delete from user favorites
        $this->db->delete('user_favorites', ['lecture_id' => $id]);
        
        return $this->db->delete('lectures', ['id' => $id]);
    }

    public function search(string $query): array
    {
        $sql = "SELECT l.*, t.title as topic_title 
                FROM lectures l 
                LEFT JOIN topics t ON l.topic_id = t.id 
                WHERE l.title LIKE :query 
                   OR l.description LIKE :query 
                   OR t.title LIKE :query 
                ORDER BY l.lesson_number ASC";
        
        $searchTerm = '%' . $query . '%';
        return $this->db->fetchAll($sql, ['query' => $searchTerm]);
    }

    private function getRelatedLectures(int $topicId, int $excludeId, int $limit = 5): array
    {
        $sql = "SELECT * FROM lectures 
                WHERE topic_id = :topic_id 
                  AND id != :exclude_id 
                ORDER BY lesson_number ASC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            'topic_id' => $topicId,
            'exclude_id' => $excludeId,
            'limit' => $limit
        ]);
    }

    public function getPopular(int $limit = 10): array
    {
        $sql = "SELECT l.*, t.title as topic_title, COUNT(uf.id) as favorite_count 
                FROM lectures l 
                LEFT JOIN topics t ON l.topic_id = t.id 
                LEFT JOIN user_favorites uf ON l.id = uf.lecture_id 
                GROUP BY l.id 
                ORDER BY favorite_count DESC, l.created_at DESC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, ['limit' => $limit]);
    }

    public function getRecent(int $limit = 10): array
    {
        $sql = "SELECT l.*, t.title as topic_title 
                FROM lectures l 
                LEFT JOIN topics t ON l.topic_id = t.id 
                ORDER BY l.created_at DESC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, ['limit' => $limit]);
    }

    public function getCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM lectures";
        $result = $this->db->fetch($sql);
        return (int) $result['count'];
    }
}
