<?php

namespace Config;

use App\Service\RouterService;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Middleware\GuestMiddleware;

$router = new RouterService();

$router->get('/', [HomeController::class, 'home']);

$router->get('/login', [AuthController::class, 'showLogin'])->middleware([GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login']);

$router->get('/register', [AuthController::class, 'showRegister'])->middleware([GuestMiddleware::class]);

$router->get('/forgot-password', [AuthController::class, 'showForgotPassword'])->middleware([GuestMiddleware::class]);

$router->post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware([GuestMiddleware::class]);

$router->get('/reset-password', [AuthController::class, 'showResetPassword'])->middleware([GuestMiddleware::class]);

$router->post('/reset-password', [AuthController::class, 'resetPassword'])->middleware([GuestMiddleware::class]);

$router->post('/register', [AuthController::class, 'register']);

$router->get('/logout', [AuthController::class, 'logout'])->middleware([AuthMiddleware::class]);



$router->dispatch();