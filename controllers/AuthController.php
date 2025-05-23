<?php

require_once "core/Controller.php";
require_once "models/User.php";
require_once "core/Auth.php";
require_once "core/TokenStorage.php";
require_once "core/Middleware.php";


class AuthController extends Controller
{
    public function login($method, $params)
    {

        if ($method !== 'POST') {
            throw new Exception("Method Not Allowed", 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        $user = (new User())->findByEmail($input['email']);
        if ($user && password_verify($input['password'], $user['password_hash'])) {
            $token = Auth::generateToken($user['id']);
            echo json_encode(['token' => $token]);

            (new TokenStorage())->storeToken($token);

        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    }

    public function register($method, $params)
    {

        try {
            if ($method !== 'POST') {
                throw new Exception("Method Not Allowed", 405);
            }

            
            $input = json_decode(file_get_contents('php://input'), true);

            echo json_encode($input);
            
            $required = ['email', 'name', 'password', "role"];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    throw new Exception("Missing required field: $field", 400);
                }
            }

            
            $email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
            $name = htmlspecialchars(filter_var($input['name']));
            $password = htmlspecialchars(filter_var($input['password']));
            $role = htmlspecialchars(filter_var($input['role']));

            // Check existing user
            $user = (new User())->findByEmail($email);
            if ($user) {
                throw new Exception("Email already exists", 409);
            }

            // Create user
            $userId = (new User())->create([
                'email' => $email,
                'name' => $name,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'role' => $role
            ]);

            // Generate JWT
            $token = Auth::generateToken($userId);

            (new TokenStorage())->storeToken($token);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'token' => $token,
                'user_id' => $userId
            ]);

        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function logout($method, $params)
    {
        if ($method !== 'POST') {
            throw new Exception("Method Not Allowed", 405);
        }

        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];

            $token = Middleware::authenticate(); // Get validated token
            (new TokenStorage())->invalidateToken($token);
            echo json_encode(['message' => 'Logged out successfully']);
        } else {
            $this->jsonResponse(['error' => 'Authorization header missing'], 401);
        }
    }

}