<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $uri, array|\Closure $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, array|\Closure $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    private function addRoute(string $method, string $uri, array|\Closure $action): void
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $this->compile($uri),
            'action' => $action,
        ];
    }

    private function compile(string $uri): string
    {
        $uri = '/' . trim($uri, '/');
        $uri = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $uri);

        return '#^' . $uri . '$#';
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = '/' . trim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches);
                $this->callAction($route['action'], $matches);
                return;
            }
        }

        $this->notFound();
    }

    private function callAction(array|\Closure $action, array $params): void
    {
        if ($action instanceof \Closure) {
            call_user_func_array($action, $params);
            return;
        }

        [$class, $method] = $action;
        $controller = new $class();
        call_user_func_array([$controller, $method], $params);
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo View::render('errors.404');
    }
}
