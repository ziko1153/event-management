<?php

namespace App\Service;

class RouterService
{
    private array $routes = [];
    private ?array $lastRoute = null;
    private ?string $currentGroupPrefix = null;

    public function get(string $path, array $action): self
    {
        return $this->addRoute('GET', $path, $action);
    }

    public function post(string $path, array $action): self
    {
        return $this->addRoute('POST', $path, $action);
    }

    public function middleware(array $middleware): self
    {
        if ($this->lastRoute) {
            $method = $this->lastRoute['method'];
            $path = $this->lastRoute['path'];
            $this->routes[$method][$path]['middleware'] = $middleware;
        }

        return $this;
    }

    public function serveStaticFiles(string $basePath = 'public'): void
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $basePath . $requestUri;

        if (file_exists($filePath) && is_file($filePath)) {
            $fileInfo = pathinfo($filePath);
            $mimeTypes = [
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'css' => 'text/css',
                'js' => 'application/javascript',
                'pdf' => 'application/pdf',
            ];

            $extension = strtolower($fileInfo['extension']);
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

            header("Content-Type: $mimeType");
            readfile($filePath);
            exit;
        }
    }

    public function dispatch(): void
    {
        // $this->serveStaticFiles();

        $method = $_SERVER['REQUEST_METHOD'];
        $uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        $route = $this->findRoute($method, $uri);

        if (!$route) {
            $this->handleError(404);
            return;
        }

        $params = $this->extractParams($uri, $route['path']);
        $queryParams = $_GET;
        $postParams = $_POST;


        $allParams = array_merge($params, $queryParams, $postParams);
        $this->runMiddleware($route['middleware'], $allParams);
        $this->executeHandler($route['action'], $allParams);
    }

    private function addRoute(string $method, string $path, array $action): self
    {
        $path = rtrim(($this->currentGroupPrefix ?? '') . $path, '/');
        $this->routes[$method][$path] = [
            'path' => $path,
            'action' => $action,
            'middleware' => [],
        ];

        $this->lastRoute = [
            'method' => $method,
            'path' => $path
        ];


        return $this;
    }

    private function findRoute(string $method, string $uri): ?array
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $route) {
            if ($this->isPathMatch($route['path'], $uri)) {
                return $route;
            }
        }

        return null;
    }

    private function isPathMatch(string $routePath, string $uri): bool
    {
        $routeSegments = explode('/', trim($routePath, '/'));
        $uriSegments = explode('/', trim($uri, '/'));

        if (count($routeSegments) !== count($uriSegments)) {
            return false;
        }

        foreach ($routeSegments as $index => $segment) {
            if (preg_match('/^{.*}$/', $segment)) {
                // This is a placeholder (e.g., {id})
                continue;
            }
            if ($segment !== $uriSegments[$index]) {
                return false;
            }
        }

        return true;
    }

    private function extractParams(string $uri, string $routePath): array
    {
        $params = [];
        $routeSegments = explode('/', trim($routePath, '/'));
        $uriSegments = explode('/', trim($uri, '/'));

        foreach ($routeSegments as $index => $segment) {
            if (preg_match('/^{(.*)}$/', $segment, $matches)) {
                $key = $matches[1];
                $params[$key] = $uriSegments[$index] ?? null;
            }
        }

        return $params;
    }

    private function runMiddleware(array $middleware, array $params): void
    {
        foreach ($middleware as $middlewareClass) {
            $middlewareInstance = new $middlewareClass();
            if (!$middlewareInstance->handle($params)) {
                $this->handleError(403, 'route mismatch, please write correct one');
                exit;
            }
        }
    }

    private function executeHandler(array $action, array $params): void
    {
        [$controller, $method] = $action;
        if (!class_exists($controller)) {
            throw new \RuntimeException("Controller not found: $controller");
        }

        $controllerInstance = new $controller();
        if (!method_exists($controllerInstance, $method)) {
            throw new \RuntimeException("Method not found: $method in $controller");
        }

        $controllerInstance->$method($params);
    }

    private function handleError(int $code, string $message = ''): void
    {
        http_response_code($code);
        echo $this->getDefaultErrorMessage($code, $message);
    }

    private function getDefaultErrorMessage(int $code, string $message = ''): string
    {
        $errors = [
            404 => 'Page Not Found',
            403 => 'Forbidden',
            500 => 'Internal Server Error'
        ];

        $title = $errors[$code] ?? 'Error';
        $description = $message ?: $errors[$code] ?? 'An error occurred';

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <title>$title</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    text-align: center; 
                    padding-top: 50px; 
                }
                h1 { color: #444; }
                p { color: #666; }
            </style>
        </head>
        <body>
            <h1>$title</h1>
            <p>$description</p>
        </body>
        </html>
        HTML;
    }
}