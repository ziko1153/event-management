<?php

namespace Config;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../src/Views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \Exception("View file '{$view}.php' not found.");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = __DIR__ . '/../src/Views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new \Exception("Layout file '{$layout}.php' not found.");
        }

        require $layoutFile;
        unset($_SESSION["old"]);
    }
}