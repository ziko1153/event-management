<?php

namespace App\Controllers;

use App\Enums\RoleEnum;
use App\Model\Organization;
use App\Service\ValidationService;
use App\Service\AuthService;

class AuthController
{
    private ValidationService $validator;
    private AuthService $authService;
    private Organization $organizerModel;

    public function __construct()
    {
        $this->validator = new ValidationService();
        $this->authService = new AuthService();
        $this->organizerModel = new Organization();
    }

    public function showLogin(): void
    {
        view('auth/login');
    }

    public function login(array $params): void
    {
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6']
        ];

        if (!$this->validator->validate($params, $rules)) {
            $_SESSION['error'] = $this->validator->getFirstError();
            header('Location: /login');
            exit;
        }

        if (!$this->authService->login($params['email'], $params['password'], isset($params['remember']))) {
            $_SESSION['error'] = 'Invalid email or password';
            header('Location: /login');
            exit;
        }

        $_SESSION['success'] = 'Login successful!';
        $this->redirectBasedOnRole($params['redirect'] ?? null);
        exit;
    }

    public function showRegister(): void
    {
        view('auth/register');
    }

    public function register(array $params): void
    {
        $rules = [
            'name' => ['required', 'min:3'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:6'],
            'password_confirmation' => ['required', 'match:password']
        ];




        if (!$this->validator->validate($params, $rules)) {
            $_SESSION['error'] = $this->validator->getFirstError();
            $_SESSION['old'] = $params;
            header('Location: /register');
            exit;
        }

        $userData = [
            'name' => $params['name'],
            'email' => $params['email'],
            'password' => password_hash($params['password'], PASSWORD_BCRYPT),
            'email_verified_at' => null,
            'remember_token' => null,
            'status' => 1
        ];

        if ($this->authService->register($userData)) {
            $_SESSION['success'] = 'Registration successful! Please login.';
            header('Location: /login');
            exit;
        }

        $_SESSION['error'] = 'Registration failed. Please try again.';
        $_SESSION['old'] = $params;
        header('Location: /register');
        exit;
    }

    public function showForgotPassword(): void
    {
        view('auth/forgot-password');
    }

    public function forgotPassword(array $params): void
    {
        $rules = [
            'email' => ['required', 'email'],
        ];

        if (!$this->validator->validate($params, $rules)) {
            $_SESSION['error'] = $this->validator->getFirstError();
            header('Location: /login');
            exit;
        }

        if ($link = $this->authService->initiatePasswordReset($params['email'])) {
            // TODO: Currently email service disable so i am just send link for demo, it will be removed in production 
            $_SESSION['success'] = 'Password reset link has been sent to your email, for demo purpose here is the link. <a href=' . $link . '> Click Here</a>';
            header('Location: /login');
            exit;
        }

        $_SESSION['error'] = 'Email not found';
        header('Location: /forgot-password');
        exit;
    }


    public function showResetPassword(): void
    {
        $token = $_GET['token'] ?? '';
        view('auth/reset-password', ['token' => $token]);
    }

    public function resetPassword(array $params): void
    {
        $rules = [
            'token' => ['required'],
            'password' => ['required', 'min:6'],
            'password_confirmation' => ['required', 'match:password']
        ];

        if (!$this->validator->validate($params, $rules)) {
            $_SESSION['error'] = $this->validator->getFirstError();
            header("Location: /reset-password?token={$params['token']}");
            exit;
        }

        if ($this->authService->resetPassword($params['token'], $params['password'])) {
            $_SESSION['success'] = 'Password has been reset successfully';
            header('Location: /login');
            exit;
        }

        $_SESSION['error'] = 'Invalid or expired reset token';
        header('Location: /forgot-password');
        exit;
    }

    public function logout(): void
    {
        $this->authService->logout();
        header('Location: /login');
        exit;
    }

    private function redirectBasedOnRole($redirect = null): void
    {
        if ($redirect) {
            header("Location: $redirect");
            exit;
        }

        $roleRedirects = [
            'admin' => '/admin/dashboard',
            'organizer' => '/admin/dashboard',
            'user' => '/user/my-events'
        ];

        $role = $_SESSION['user']['role'] ?? 'user';
        if ($role == RoleEnum::ORGANIZER->value) {
            $_SESSION['user']['organizer'] = $this->organizerModel->findByColumn('user_id', $_SESSION['user']['id']);
        }
        $redirectUrl = $roleRedirects[$role] ?? '/';

        header('Location: ' . $redirectUrl);
    }
}