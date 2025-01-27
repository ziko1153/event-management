<?php

namespace Config;


class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../src/Views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \Exception("View file '{$view}.php' not found.");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        require __DIR__ . '/../src/Views/layouts/main.php';
    }
}
