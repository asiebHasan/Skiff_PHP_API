<?php

require_once 'core/TokenStorage.php';
require_once 'core/Auth.php';
require_once 'models/User.php';
class Middleware
{
    public static function authenticate(){
        $header = getallheaders();
        $authHeader = $header['Authorization'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $token = $matches[1];
        $storage = new TokenStorage();

        if (!$storage->isValidToken($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        return $token;
    }

    public static function adminOnly(){
        $token = self::authenticate();

        $payload = Auth::validateToken($token);

       

        $userId = $payload['sub'] ?? null;

       

        $user  = (new User())->find($userId);

        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }

        return $user;
    }
}