<?php

class Auth
{
    public static function generateToken($userID)
    {
        $header = json_encode(['type' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'sub' => $userID,
            'exp' => time() + (60 * 60 * 24), 
        ]);

        $base64Header = str_replace(['+', '/', "="], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", JWT_SECRET, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return "$base64Header.$base64Payload.$base64Signature";
    }

    public static function validateToken($token)
    {
        $storage = new TokenStorage();

        if (!$storage->isValidToken($token)) {
            return false;
        }

        $payload = self::verifyJWT($token);

        if (!$payload || $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    private static function verifyJWT($token)
    {
        $parts = explode('.', $token);
        if (count($parts) != 3)
            return false;
        list($base64Header, $base64Payload, $base64Signature) = $parts;
        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Signature));
        $validSignature = hash_hmac('sha256', "$base64Header.$base64Payload", JWT_SECRET, true);
        if (!hash_equals($signature, $validSignature))
            return false;
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Payload)), true);
        if ($payload['exp'] < time())
            return false;
        return $payload;
    }

    public static function userIdFromRequest()
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            throw new Exception("Missing Authorization header", 401);
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $payload = self::validateToken($token);
        if (!$payload || !isset($payload['sub'])) {
            throw new Exception("Invalid token", 401);
        }

        return $payload['sub'];
    }

}