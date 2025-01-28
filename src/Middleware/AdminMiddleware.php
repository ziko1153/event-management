<?php

namespace App\Middleware;

class AdminMiddleware
{
    public function handle(array $params): bool
    {
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'organizer'])) {
            header('Location: /');
            exit;
        }
        return true;
    }
}
