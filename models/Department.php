<?php


class Department extends Model
{
    protected $tableName = 'departments';

    public function getAllDepartments()
    {
        $result = $this->db->query("SELECT * FROM {$this->tableName}");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getDepartmentById($id)
    {
        return $this->find($id);
    }


    public function createDepartment($data)
    {
        $stmt = $this->db->connection->prepare(
            "INSERT INTO {$this->tableName} (name) VALUES (?)"
        );

        $stmt->bind_param('s', $data['name']);
        
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception("Database insertion failed");
        }

        return $stmt->insert_id;
    }

}