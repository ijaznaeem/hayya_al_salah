<?php

namespace HayyaAlSalah\Models;

use HayyaAlSalah\Database;

class Topic
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $sql = "SELECT t.*, COUNT(l.id) as lessons_count 
                FROM topics t 
                LEFT JOIN lectures l ON t.id = l.topic_id 
                GROUP BY t.id 
                ORDER BY t.created_at DESC";
        
        $topics = $this->db->fetchAll($sql);
        
        // Add duration calculation
        foreach ($topics as &$topic) {
            $topic['duration'] = $this->calculateTopicDuration($topic['id']);
        }
        
        return $topics;
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT t.*, COUNT(l.id) as lessons_count 
                FROM topics t 
                LEFT JOIN lectures l ON t.id = l.topic_id 
                WHERE t.id = :id 
                GROUP BY t.id";
        
        $topic = $this->db->fetch($sql, ['id' => $id]);
        
        if ($topic) {
            $topic['duration'] = $this->calculateTopicDuration($id);
            $topic['lessons'] = $this->getLecturesByTopicId($id);
        }
        
        return $topic;
    }

    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->insert('topics', $data);
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('topics', $data, ['id' => $id]);
    }

    public function delete(int $id): bool
    {
        // First delete all related lectures
        $this->db->delete('lectures', ['topic_id' => $id]);
        
        return $this->db->delete('topics', ['id' => $id]);
    }

    private function calculateTopicDuration(int $topicId): string
    {
        $sql = "SELECT SUM(
                    TIME_TO_SEC(STR_TO_DATE(duration, '%i:%s'))
                ) as total_seconds 
                FROM lectures 
                WHERE topic_id = :topic_id";
        
        $result = $this->db->fetch($sql, ['topic_id' => $topicId]);
        $totalSeconds = $result['total_seconds'] ?? 0;
        
        if ($totalSeconds == 0) {
            return '0:00';
        }
        
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        
        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        } else {
            return sprintf('%02d:%02d', $minutes, $seconds);
        }
    }

    private function getLecturesByTopicId(int $topicId): array
    {
        $lectureModel = new Lecture();
        return $lectureModel->getByTopicId($topicId);
    }

    public function getCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM topics";
        $result = $this->db->fetch($sql);
        return (int) $result['count'];
    }
}
