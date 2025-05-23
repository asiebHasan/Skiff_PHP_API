<?php
class Router
{
    private $middlewares = [];

    public function addMiddleware($middleware)
    {
        $this->middlewares[] = $middleware;
        return $this;
    }
    public function dispatch()
    {

        foreach ($this->middlewares as $middleware) {
            call_user_func($middleware);
        }

        $url = $_GET['url'] ?? '';
        $urlParts = array_values(array_filter(explode('/', $url)));

        
        if (!empty($urlParts) && $urlParts[0] === 'api') {
            array_shift($urlParts);

            
            $controllerName = !empty($urlParts[0])
                ? ucfirst($urlParts[0]) . 'Controller'
                : 'ApiController';

            $action = !empty($urlParts[1])
                ? lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $urlParts[1]))))
                : 'index';

            $params = array_slice($urlParts, 2);
        }

        $controllerFile = "controllers/$controllerName.php";

        try {
            if (!file_exists($controllerFile)) {
                throw new Exception("Controller not found", 404);
            }

            require_once $controllerFile;

            if (!class_exists($controllerName)) {
                throw new Exception("Controller class not found", 500);
            }

            $controller = new $controllerName();

            if (!method_exists($controller, $action)) {
                throw new Exception("Endpoint not found", 404);
            }

            $controller->$action($_SERVER['REQUEST_METHOD'], $params);

        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}