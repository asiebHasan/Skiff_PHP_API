<?php
require_once 'core/Model.php';

class User extends Model
{
    protected $tableName = 'users';

    public function isAdmin($userId)
    {
        $stmt = $this->db->connection->prepare(
            "SELECT role FROM {$this->tableName} WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return isset($result['role']) && $result['role'] === 'admin';
    }


    public function findByEmail($email)
    {
        $stmt = $this->db->connection->prepare(
            "SELECT * FROM {$this->tableName} WHERE email = ? LIMIT 1"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function find($id)
    {
        $stmt = $this->db->connection->prepare(
            "SELECT * FROM {$this->tableName} WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data)
    {
        $stmt = $this->db->connection->prepare(
            "INSERT INTO {$this->tableName} (email, name, password_hash, role) VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            'ssss',
            $data['email'],
            $data['name'],
            $data['password'],
            $data['role']
        );

        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception("Database insertion failed");
        }

        return $stmt->insert_id;
    }


}
