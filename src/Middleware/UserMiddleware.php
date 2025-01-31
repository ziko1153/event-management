<?php

namespace App\Middleware;

class UserMiddleware
{
    public function handle(array $params): bool
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
            header('Location: /');
            exit;
        }
        return true;
    }
}
