<?php

namespace Config;

use App\Controllers\Admin\AdminController;
use App\Controllers\Admin\EventController;
use App\Controllers\User\UserEventController;
use App\Middleware\AdminMiddleware;
use App\Middleware\UserMiddleware;
use App\Service\RouterService;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\User\UserProfileController;
use App\Middleware\GuestMiddleware;

$router = new RouterService();


$router->get('/', [HomeController::class, 'home']);
$router->get('/events/search', [HomeController::class, 'search']);

// Event Registration Routes
$router->get('/events/register/{slug}', [UserEventController::class, 'showRegistrationForm']);
$router->post('/events/register/{slug}', [UserEventController::class, 'register'])->middleware([AuthMiddleware::class, UserMiddleware::class]);

$router->get('/events/payment/process/{registration_number}', [UserEventController::class, 'paymentProcess'])->middleware([AuthMiddleware::class, UserMiddleware::class]);

$router->post('/events/payment/complete/{registration_number}', [UserEventController::class, 'paymentComplete'])
    ->middleware([AuthMiddleware::class, UserMiddleware::class]);



//=====================================  AUTH Route 
$router->get('/login', [AuthController::class, 'showLogin'])->middleware([GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login']);

$router->get('/register', [AuthController::class, 'showRegister'])->middleware([GuestMiddleware::class]);

$router->get('/forgot-password', [AuthController::class, 'showForgotPassword'])->middleware([GuestMiddleware::class]);

$router->post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware([GuestMiddleware::class]);

$router->get('/reset-password', [AuthController::class, 'showResetPassword'])->middleware([GuestMiddleware::class]);

$router->post('/reset-password', [AuthController::class, 'resetPassword'])->middleware([GuestMiddleware::class]);

$router->post('/register', [AuthController::class, 'register']);

$router->get('/logout', [AuthController::class, 'logout'])->middleware([AuthMiddleware::class]);
# ========================= END AUTH ROUTE ========================


// Admin Routes
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/events', [EventController::class, 'getEvents'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);


//======================================= Admin Event Route =======================
$router->get('/admin/events/create', [EventController::class, 'create'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/events/create', [EventController::class, 'store'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/events/update/{slug}', [EventController::class, 'edit'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/events/update/{slug}', [EventController::class, 'update'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/events/delete/{slug}', [EventController::class, 'delete'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/events/search-organizers', [EventController::class, 'searchOrganizers'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/events/{slug}/attendees', [EventController::class, 'attendees'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/events/{slug}/attendees/download', [EventController::class, 'downloadAttendees'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
//========================================== END: EVENT ROUTE

// $router->get('/admin/categories', [CategoryController::class, 'index'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
// $router->post('/admin/categories', [CategoryController::class, 'store'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/users', [AdminController::class, 'users'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/users/{id}', [AdminController::class, 'getUser'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/status', [AdminController::class, 'updateUserStatus'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users', [AdminController::class, 'storeUser'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/{id}/update', [AdminController::class, 'updateUser'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/{id}/delete', [AdminController::class, 'deleteUser'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);


// Admin profile routes
$router->get('/admin/profile', [AdminController::class, 'profile'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/profile', [AdminController::class, 'updateProfile'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/profile/password', [AdminController::class, 'changePassword'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/profile/password', [AdminController::class, 'updatePassword'])->middleware([AuthMiddleware::class, AdminMiddleware::class]);



// User Event Routes
$router->get('/user/my-events', [UserEventController::class, 'myEvents'])->middleware([AuthMiddleware::class, UserMiddleware::class]);
$router->get('user/my-events/{slug}', [UserEventController::class, 'show'])->middleware([AuthMiddleware::class, UserMiddleware::class]);

// User Profile Routes
$router->get('/user/profile', [UserProfileController::class, 'show'])
    ->middleware([AuthMiddleware::class, UserMiddleware::class]);
$router->post('/user/profile', [UserProfileController::class, 'update'])
    ->middleware([AuthMiddleware::class, UserMiddleware::class]);

$router->dispatch();