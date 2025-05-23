<?php
class Controller {
    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}