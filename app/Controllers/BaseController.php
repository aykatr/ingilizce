<?php

namespace App\Controllers;

use App\Core\View;

abstract class BaseController
{
    protected function view(string $view, array $data = [], ?string $layout = 'main'): void
    {
        echo View::render($view, $data, $layout);
    }

    protected function redirect(string $to): never
    {
        header('Location: ' . $to);
        exit;
    }

    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
