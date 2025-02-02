<?php

namespace App\Service;

use App\Model\User;

class AuthService
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function register(array $data): bool
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        return $this->userModel->create($data);
    }

    public function login(string $email, string $password, bool $remember = false): bool
    {
        $user = $this->userModel->findWithQuery(conditions: [
            'email' => $email,
            'status' => 1
        ]);


        if (!isset($user[0]) || !password_verify($password, $user[0]['password'])) {
            return false;
        }

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->userModel->update($user[0]['id'], ['remember_token' => $token]);
            setcookie('remember_token', $token, time() + (86400 * 30), '/');
        }

        $_SESSION['user'] = $user[0];
        return true;
    }

    public function logout(): void
    {
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
    }

    public function initiatePasswordReset(string $email): bool | string
    {
        $user = $this->userModel->findByColumn('email', $email);
        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        if ($this->userModel->update($user['id'], [
            'reset_token' => $token,
            'reset_token_expires_at' => $expires
        ])) {
            $resetLink = env('APP_URL') . "/reset-password?token=" . $token;
            // TODO: Implement email sending logic in  future 
            return $resetLink;
        }
        return false;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $user = $this->userModel->findByColumn("reset_token", $token);
        if (!$user) {
            return false;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->userModel->update($user['id'], [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_token_expires_at' => null
        ]);
    }
}