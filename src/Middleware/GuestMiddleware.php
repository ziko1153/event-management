<?php

namespace App\Middleware;

class GuestMiddleware
{
    public function handle($params): bool
    {
        if (isset($_SESSION['user'])) {

            if ($_SESSION['user']['role'] == 'admin') {
                header('Location: /admin/dashboard');
                exit;
            } else if ($_SESSION['user']['role'] == 'user') {
                header('Location: /');
                exit;
            }
        }
        return true;
    }
}