<?php

require_once "core/Controller.php";

class ApiController extends Controller {
    public function getData() {
        $userId = $this->authenticate(); 
        echo json_encode(['data' => 'Protected data']);
    }

    private function authenticate() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token required']);
            exit;
        }
        $payload = Auth::validateToken($matches[1]);
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
        return $payload['sub'];
    }
}