<?php

namespace App;

class Router {
    private array $routes = [];

    // Add route
    public function add(string $method, string $path, string $handler, array $middlewares = []): void {
        // Convert format to regex
        $routePattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $routePattern = '#^' . $routePattern . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $routePattern,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    // Handle request
    public function handle(string $method, string $uri): void {
        // Remove query string
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Remove BASE_URL prefix
        if (defined('BASE_URL') && BASE_URL !== '') {
            $len = strlen(BASE_URL);
            if (substr($path, 0, $len) === BASE_URL) {
                $path = substr($path, $len);
            }
        }
        if (empty($path)) {
            $path = '/';
        }

        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                // Run route middlewares
                foreach ($route['middlewares'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (!$middleware->handle()) {
                        return; // Exit if middleware blocks
                    }
                }

                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Handler format: Controller@method
                list($controllerName, $methodName) = explode('@', $route['handler']);
                $fullControllerName = "\\App\\Controllers\\" . $controllerName;

                if (class_exists($fullControllerName)) {
                    $controller = new $fullControllerName();
                    if (method_exists($controller, $methodName)) {
                        call_user_func_array([$controller, $methodName], $params);
                        return;
                    }
                }
                
                $this->sendNotFound();
                return;
            }
        }

        $this->sendNotFound();
    }

    private function sendNotFound(): void {
        http_response_code(404);
        if (function_exists('view') && file_exists(VIEW_PATH . '/errors/404.php')) {
            view('errors/404', ['title' => '404 - AlmaKick']);
        } else {
            echo "<h1>404 Pagina Non Trovata</h1><p>La risorsa richiesta non esiste.</p>";
        }
    }
}
