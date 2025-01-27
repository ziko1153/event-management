<?php

namespace Config;

use App\Service\RouterService;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Middleware\GuestMiddleware;

$router = new RouterService();

$router->get('/', [HomeController::class, 'home'])->middleware([GuestMiddleware::class]);

$router->get('/login', [AuthController::class, 'showLogin'])->middleware([GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login']);

$router->get('/register', [AuthController::class, 'showRegister'])->middleware([GuestMiddleware::class]);
$router->post('/register', [AuthController::class, 'register']);

$router->get('/logout', [AuthController::class, 'logout'])->middleware([AuthMiddleware::class]);



$router->dispatch();
