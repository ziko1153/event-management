<?php

namespace App\Controllers;

use App\Service\ValidationService;
use App\Service\AuthService;

class AuthController
{
    private ValidationService $validator;
    private AuthService $authService;

    public function __construct()
    {
        $this->validator = new ValidationService();
        $this->authService = new AuthService();
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

        $_SESSION['success'] = 'Login successful!'; // Changed from error to success
        header('Location: /dashboard'); // Changed destination
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

        if ($this->authService->initiatePasswordReset($params['email'])) {
            $_SESSION['success'] = 'Password reset link has been sent to your email';
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
}