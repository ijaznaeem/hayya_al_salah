<?php

namespace HayyaAlSalah\Models;

use HayyaAlSalah\Database;

class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        
        $data['role'] = $data['role'] ?? 'user';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->insert('users', $data);
    }

    public function getByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        return $this->db->fetch($sql, ['email' => $email]);
    }

    public function getByUsername(string $username): ?array
    {
        $sql = "SELECT * FROM users WHERE username = :username";
        return $this->db->fetch($sql, ['username' => $username]);
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT id, username, email, role, created_at, updated_at FROM users WHERE id = :id";
        return $this->db->fetch($sql, ['id' => $id]);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function updateLastLogin(int $userId): bool
    {
        $sql = "UPDATE users SET last_login = :last_login WHERE id = :id";
        return $this->db->query($sql, [
            'last_login' => date('Y-m-d H:i:s'),
            'id' => $userId
        ])->rowCount() > 0;
    }

    public function emailExists(string $email): bool
    {
        return $this->getByEmail($email) !== null;
    }

    public function usernameExists(string $username): bool
    {
        return $this->getByUsername($username) !== null;
    }

    public function update(int $id, array $data): bool
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('users', $data, ['id' => $id]);
    }

    public function delete(int $id): bool
    {
        // First delete all user favorites
        $this->db->delete('user_favorites', ['user_id' => $id]);
        
        return $this->db->delete('users', ['id' => $id]);
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT id, username, email, role, created_at, updated_at, last_login 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        return $this->db->fetchAll($sql, [
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    public function getCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM users";
        $result = $this->db->fetch($sql);
        return (int) $result['count'];
    }

    public function getFavoritesCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM user_favorites";
        $result = $this->db->fetch($sql);
        return (int) $result['count'];
    }

    public function search(string $query): array
    {
        $sql = "SELECT id, username, email, role, created_at, updated_at, last_login 
                FROM users 
                WHERE username LIKE :query OR email LIKE :query
                ORDER BY created_at DESC 
                LIMIT 50";
        
        return $this->db->fetchAll($sql, [
            'query' => "%{$query}%"
        ]);
    }

    public function getRecent(int $limit = 10): array
    {
        $sql = "SELECT id, username, email, role, created_at, updated_at, last_login 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            'limit' => $limit
        ]);
    }
}
