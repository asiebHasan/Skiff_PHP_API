<?php

require_once "core/Controller.php";
require_once "models/Department.php";
require_once "models/Ticket.php";
require_once "models/TicketNote.php";
require_once "core/Auth.php";
require_once "core/TokenStorage.php";
require_once "core/Middleware.php";



class TicketController extends Controller
{
    public function index($method)
    {
        if ($method !== 'GET') {
            throw new Exception("Method Not Allowed", 405);
        }

        $ticket = (new Ticket())->index();
        echo json_encode($ticket);
    }

    public function create($method)
    {
        if ($method !== 'POST') {
            throw new Exception("Method Not Allowed", 405);
        }

        $input = $_POST;


        if (empty($input['title']) || empty($input['description'])) {
            throw new Exception("Missing required fields: title, description", 400);
        }

        $title = filter_var($input['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = filter_var($input['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $status = isset($input['status']) ? htmlspecialchars(filter_var($input['status'])) : 'open';
        $userId = Auth::userIdFromRequest();
        $departmentId = isset($input['department_id']) ? filter_var($input['department_id'], FILTER_VALIDATE_INT) : null;

        if (!empty($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../storage/uploads/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir);

            $originalName = basename($_FILES['file']['name']);
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $safeName = uniqid() . '.' . $ext;

            $targetPath = $uploadDir . $safeName;
            move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);

            $filePath = 'uploads/' . $safeName;
        } else {
            $filePath = null;
        }

        $userId = Auth::userIdFromRequest();
        $user = (new User())->find($userId);


        // adding limit
        $rateFile = __DIR__ . '/../storage/ratelimit/user_' . $userId . '.txt';


        if (file_exists($rateFile)) {
            $last = filemtime($rateFile);
            $now = time();
            $remaining = RATE_LIMIT_SECONDS - ($now - $last);

            if ($remaining > 0) {
                http_response_code(429);
                echo json_encode([
                    'error' => "Rate limit exceeded. Please wait {$remaining} seconds."
                ]);
                return;
            }
        }

        file_put_contents($rateFile, '1');


        if ($user['role'] !== 'admin') {
            echo json_encode(['role' => $user['role']]);
            $ticketId = (new Ticket())->create([
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'user_id' => $userId,
                'department_id' => $departmentId,
                'file_path' => $filePath,
            ]);
        } else {
            echo json_encode(['role' => $user['role']]);
            $ticketId = (new Ticket())->create([
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'user_id' => isset($input['user_id']) ? filter_var($input['user_id'], FILTER_VALIDATE_INT) : null,
                'department_id' => $departmentId,
                'file_path' => $filePath,
            ]);
        }


        echo json_encode([
            'id' => $ticketId,
            'file_url' => $filePath ? 'http://localhost:8080/' . $filePath : null
        ]);
    }

    public function update($method, $id)
    {
        if ($method !== 'PUT') {
            throw new Exception("Method Not Allowed", 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            throw new Exception("Invalid JSON", 400);
        }

        if (empty($input['title']) || empty($input['description'])) {
            throw new Exception("Missing required fields: title, description", 400);
        }

        $title = filter_var($input['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = filter_var($input['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $status = isset($input['status']) ? htmlspecialchars(filter_var($input['status'])) : 'open';
        $departmentId = isset($input['department_id'])
            ? filter_var($input['department_id'], FILTER_VALIDATE_INT)
            : null;

        $userId = Auth::userIdFromRequest();

        $user = (new User())->find($userId);

        if ($user['role'] !== 'admin') {
            echo json_encode(['role' => $user['role']]);
            (new Ticket())->update($id, [
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'user_id' => isset($input['user_id']) ? filter_var($input['user_id'], FILTER_VALIDATE_INT) : null,
                'department_id' => $departmentId
            ]);
        } else {
            echo json_encode(['role' => $user['role']]);
            (new Ticket())->update($id, [
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'user_id' => $userId,
                'department_id' => $departmentId
            ]);
        }




        echo json_encode(['id' => $id]);



        echo json_encode(['message' => 'Ticket updated successfully']);
    }

    public function addNote($method, $id)
    {
        if ($method !== 'POST') {
            throw new Exception("Method Not Allowed", 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            throw new Exception("Invalid JSON", 400);
        }

        if (empty($input['note'])) {
            throw new Exception("Missing required field: note", 400);
        }

        $note = filter_var($input['note'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $userId = Auth::userIdFromRequest();

        (new TicketNote())->create([
            'ticket_id' => $id,
            'user_id' => $userId,
            'note' => $note
        ]);

        echo json_encode(['message' => 'Note added successfully']);
    }


}