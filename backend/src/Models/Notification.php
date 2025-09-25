<?php

namespace HayyaAlSalah\Models;

use HayyaAlSalah\Database;

class Notification
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(int $limit = 50, int $offset = 0, ?string $userId = null): array
    {
        $sql = "SELECT id, title, message, timestamp, is_read, read_at, user_id, created_at, updated_at 
                FROM notifications ";
        $params = [
            'limit' => $limit,
            'offset' => $offset
        ];
        
        if ($userId !== null) {
            $sql .= "WHERE user_id IS NULL OR user_id = :user_id ";
            $params['user_id'] = $userId;
        }
        
        $sql .= "ORDER BY timestamp DESC, created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->query($sql, $params);
        
        return $stmt->fetchAll();
    }

    public function getById(string $id): ?array
    {
        $sql = "SELECT id, title, message, timestamp, is_read, read_at, user_id, created_at, updated_at 
                FROM notifications 
                WHERE id = :id";
        
        $stmt = $this->db->query($sql, ['id' => $id]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    public function create(array $data): string
    {
        $id = $this->generateUuid();
        
        $sql = "INSERT INTO notifications (id, title, message, timestamp, user_id, is_read) 
                VALUES (:id, :title, :message, :timestamp, :user_id, :is_read)";
        
        $this->db->query($sql, [
            'id' => $id,
            'title' => $data['title'],
            'message' => $data['message'],
            'timestamp' => $data['timestamp'] ?? date('Y-m-d H:i:s'),
            'user_id' => $data['user_id'] ?? null,
            'is_read' => $data['is_read'] ?? false
        ]);
        
        return $id;
    }

    public function update(string $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];
        
        if (isset($data['title'])) {
            $fields[] = 'title = :title';
            $params['title'] = $data['title'];
        }
        
        if (isset($data['message'])) {
            $fields[] = 'message = :message';
            $params['message'] = $data['message'];
        }
        
        if (isset($data['timestamp'])) {
            $fields[] = 'timestamp = :timestamp';
            $params['timestamp'] = $data['timestamp'];
        }
        
        if (isset($data['is_read'])) {
            $fields[] = 'is_read = :is_read';
            $params['is_read'] = $data['is_read'];
            
            // If marking as read, set read_at timestamp
            if ($data['is_read']) {
                $fields[] = 'read_at = NOW()';
            }
        }
        
        if (isset($data['user_id'])) {
            $fields[] = 'user_id = :user_id';
            $params['user_id'] = $data['user_id'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE notifications SET " . implode(', ', $fields) . " WHERE id = :id";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }

    public function delete(string $id): bool
    {
        $sql = "DELETE FROM notifications WHERE id = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function getRecent(int $days = 30, ?string $userId = null): array
    {
        $sql = "SELECT id, title, message, timestamp, is_read, read_at, user_id, created_at, updated_at 
                FROM notifications 
                WHERE timestamp >= DATE_SUB(NOW(), INTERVAL :days DAY)";
        $params = ['days' => $days];
        
        if ($userId !== null) {
            $sql .= " AND (user_id IS NULL OR user_id = :user_id)";
            $params['user_id'] = $userId;
        }
        
        $sql .= " ORDER BY timestamp DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) as total FROM notifications";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return (int) $result['total'];
    }

    public function getUnreadCount(?string $userId = null): int
    {
        $sql = "SELECT COUNT(*) as total FROM notifications WHERE is_read = FALSE";
        $params = [];
        
        if ($userId !== null) {
            $sql .= " AND (user_id IS NULL OR user_id = :user_id)";
            $params['user_id'] = $userId;
        }
        
        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return (int) $result['total'];
    }

    public function markAsRead(string $id): bool
    {
        return $this->update($id, ['is_read' => true]);
    }

    public function markAllAsRead(?string $userId = null): bool
    {
        $sql = "UPDATE notifications SET is_read = TRUE, read_at = NOW() WHERE is_read = FALSE";
        $params = [];
        
        if ($userId !== null) {
            $sql .= " AND (user_id IS NULL OR user_id = :user_id)";
            $params['user_id'] = $userId;
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
