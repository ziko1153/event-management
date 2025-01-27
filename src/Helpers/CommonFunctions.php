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
    function view(string $view, array $data = [])
    {
        View::render($view, $data);
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