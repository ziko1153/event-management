<?php
use Config\Env;
use Config\View;

Env::load(__DIR__.'/../../.env');

if (!function_exists('env')) {
    function env($key, $default = null) {
        return Env::get($key, $default);
    }
}

if (!function_exists('view')) {
    function view(string $view, array $data = [], string $layout = 'main')
    {
        View::render($view, $data, $layout);
    }
}

if (!function_exists('dd')) {
    function dd($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        die();
    }
}

if (!function_exists('oldData')) {
    function oldData($field, $default = '')
    {
        return $_SESSION['old'][$field] ?? $default;
    }
}