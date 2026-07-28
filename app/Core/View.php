<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'main'): string
    {
        $content = self::renderFile(self::path('Views', $view), $data);

        if ($layout === null) {
            return $content;
        }

        return self::renderFile(self::path('Views/layouts', $layout), array_merge($data, ['content' => $content]));
    }

    private static function renderFile(string $file, array $data): string
    {
        extract($data);
        ob_start();
        require $file;

        return ob_get_clean();
    }

    private static function path(string $base, string $view): string
    {
        return __DIR__ . '/../' . $base . '/' . str_replace('.', '/', $view) . '.php';
    }
}
