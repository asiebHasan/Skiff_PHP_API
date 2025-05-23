<?php

class TicketNote extends Model
{
    protected $tableName = 'ticket_notes';

    public function getNote($ticketId)
    {
        $stmt = $this->db->connection->prepare(
            "SELECT * FROM {$this->tableName} WHERE ticket_id = ?"
        );
        $stmt->bind_param('i', $ticketId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllNotes()
    {
        $result = $this->db->query("SELECT * FROM {$this->tableName}");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function create($data)
    {
        $stmt = $this->db->connection->prepare(
            "INSERT INTO {$this->tableName} (user_id, ticket_id, note) VALUES (?, ?, ?)"
        );
        $stmt->bind_param(
            'iis',
            $data['user_id'],
            $data['ticket_id'],
            $data['note']
        );
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception("Database insertion failed");
        }

        return $stmt->insert_id;
    }

}