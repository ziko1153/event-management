<?php

namespace App\Middleware;

class GuestMiddleware
{
    public function handle($params): bool
    {
        if (isset($_SESSION['user'])) {
            header('Location: /home');
            exit;
        }
        return true;
    }
}