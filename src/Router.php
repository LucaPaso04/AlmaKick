<?php

namespace App;

class Router {
    private array $routes = [];

    // Aggiunge una rotta alla lista
    public function add(string $method, string $path, string $handler, array $middlewares = []): void {
        // Converte ad esempio /matches/{id} in una regex /matches/(?P<id>[^/]+)
        $routePattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $routePattern = '#^' . $routePattern . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $routePattern,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    // Risolve la richiesta corrente
    public function handle(string $method, string $uri): void {
        // Rimuove query string se presente (es. ?id=1)
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Se l'app si trova in una sottocartella, rimuove il prefisso BASE_URL dal path di routing
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
                // Esegue i middleware registrati per questa rotta
                foreach ($route['middlewares'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (!$middleware->handle()) {
                        return; // Se il middleware blocca la richiesta, esce
                    }
                }

                // Estrae i parametri nominativi dal regex match
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Il formato dell'handler deve essere "NomeController@metodo"
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
