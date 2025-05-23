<?php

class Database{
    public $connection;

    public function __construct(){
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if($this->connection->connect_error){
            die("Connection Failed". $this->connection->connect_error);
        }
    }

    public function query($sql, $params = []){
        $stmt = $this->connection->prepare($sql);
        if (!empty($params)){
            $types = str_repeat("s", count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }
}