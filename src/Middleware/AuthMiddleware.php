<?php

namespace App\Middleware;

class AuthMiddleware
{
    public function handle($params): bool
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        return true;
    }
}