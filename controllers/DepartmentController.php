<?php

require_once "core/Controller.php";
require_once "models/Department.php";
require_once "core/Auth.php";
require_once "core/TokenStorage.php";
require_once "core/Middleware.php";

class DepartmentController extends Controller
{
    public function getDepartments($method)
    {
        if ($method !== 'GET') {
            throw new Exception("Method Not Allowed", 405);
        }

        $department = (new Department())->getAllDepartments();
        echo json_encode($department);
    }

    public function create($method)
    {
        if ($method !== 'POST') {
            throw new Exception("Method Not Allowed", 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        
        if (empty($input['name'])) {
            throw new Exception("Missing required field: name", 400);
        }

        
        $name = htmlspecialchars(filter_var($input['name']));

        $departmentId = (new Department())->createDepartment(['name' => $name]);

        echo json_encode(['id' => $departmentId]);
    }
}