<?php

namespace App\Core;

class Router {
    protected $routes = [];

    public function add($method, $uri, $controllerAction) {
        // Convierte la URI en una expresión regular para manejar parámetros
        // ej: /contact/{id} se convierte en /contact/([^/]+)
        $uri = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $uri);
        $this->routes[] = [
            'method' => $method,
            'uri' => "#^{$uri}$#", // Delimitadores de regex
            'controller' => $controllerAction
        ];
    }

    public function dispatch($uri, $method) {
        foreach ($this->routes as $route) {
            // Comprueba si la ruta coincide con la URI usando la expresión regular
            if (preg_match($route['uri'], $uri, $matches) && $route['method'] === $method) {
                // Elimina la coincidencia completa para quedarnos solo con los parámetros
                array_shift($matches);
                $params = $matches;

                list($controller, $action) = explode('@', $route['controller']);
                $controller = "App\\Controllers\\{$controller}";

                if (class_exists($controller) && method_exists($controller, $action)) {
                    $controllerInstance = new $controller();
                    // Llama al método del controlador, pasando los parámetros de la URL
                    call_user_func_array([$controllerInstance, $action], $params);
                    return;
                }
            }
        }
        http_response_code(404);
        echo "Error 404: Página no encontrada.";
    }
}

