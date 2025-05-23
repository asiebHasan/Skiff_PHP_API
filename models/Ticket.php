<?php

class Ticket extends Model
{
    protected $tableName = 'tickets';

    public function index()
    {
        $result = $this->db->query("SELECT * FROM {$this->tableName}");
        return $result->fetch_all(MYSQLI_ASSOC);
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
            "INSERT INTO {$this->tableName} (title, description, status, user_id, department_id, file_path)
         VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            'sssiis',
            $data['title'],
            $data['description'],
            $data['status'],
            $data['user_id'],
            $data['department_id'],
            $data['file_path']
        );

        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception("Database insertion failed");
        }

        return $stmt->insert_id;
    }

    public function update($id, $data)
    {
        $stmt = $this->db->connection->prepare(
            "UPDATE {$this->tableName}
         SET title = ?, description = ?, status = ?, user_id = ?, department_id = ?
         WHERE id = ?"
        );

        
        $userId = is_int($data['user_id']) ? $data['user_id'] : null;
        $departmentId = is_int($data['department_id']) ? $data['department_id'] : null;

        $title = $data['title'] ?? '';
        $description = $data['description'] ?? '';
        $status = $data['status'] ?? 'open';

        $stmt->bind_param(
            'sssiii',
            $title,
            $description,
            $status,
            $userId,
            $departmentId,
            $id
        );

        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception("Database update failed or no changes detected", 400);
        }

        return true;
    }


}