<?php
require_once 'core/Database.php';

class Model {
    protected $db;
    protected $tableName;

    public function __construct() {
        $this->db = new Database();
    }

    
    public function find($id) {
        $result = $this->db->query(
            "SELECT * FROM {$this->tableName} WHERE id = ?",
            [$id]
        );
        return $result->fetch_assoc();
    }


}