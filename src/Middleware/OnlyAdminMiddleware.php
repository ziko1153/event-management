<?php

namespace App\Middleware;

class OnlyAdminMiddleware
{
    public function handle(array $params): bool
    {
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin'])) {
            header('Location: /admin/dashboard');
            exit;
        }
        return true;
    }
}