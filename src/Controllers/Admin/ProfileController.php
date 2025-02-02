<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Enums\RoleEnum;
use App\Model\Organization;
use App\Model\User;
use App\Service\ValidationService;
use App\Service\UserService;

class ProfileController extends BaseController
{
    private ValidationService $validator;
    private UserService $userService;
    private User $userModel;
    private Organization $organizerModel;

    public function __construct()
    {
        $this->validator = new ValidationService();
        $this->userService = new UserService();
        $this->userModel = new User();
        $this->organizerModel = new Organization();
    }

    public function show(): void
    {
        $user = $_SESSION['user'];
        view('admin/profile/show', ['user' => $user], 'admin');
    }

    public function update(array $params): void
    {
        $rules = [
            'name' => ['required', 'min:3'],
            'phone' => ['nullable', 'numeric', 'min:11', 'unique:users,phone,' . $_SESSION['user']['id']],
            'address' => ['nullable', 'min:5'],
            'avatar' => ['nullable', 'image', 'type:jpg,jpeg,png', 'size:1mb']
        ];

        if (isset($_SESSION['user']['organizer'])) {
            $rules['org_name'] = ['required', 'min:3'];
            $rules['org_website'] = ['nullable', 'url'];
            $rules['org_description'] = ['nullable', 'min:5'];
            $rules['org_logo'] = ['nullable', 'image', 'type:jpg,jpeg,png', 'size:1mb'];
        }

        if (!$this->validator->validate($params, $rules)) {
            $this->sendValidationError($this->validator->getErrors(), $params);
        }

        $result = $this->userService->updateUser($_SESSION['user'], $params, $_FILES['avatar'] ?? null);

        if (!$result['success']) {
            $this->sendError($result['error'] ?? 'Failed to update profile');
        }

        $user = $this->userModel->findById($_SESSION['user']['id']);
        $_SESSION['user'] = $user;

        if ($_SESSION['user']['role'] == RoleEnum::ORGANIZER->value) {
            $_SESSION['user']['organizer'] = $this->organizerModel->findByColumn('user_id', $_SESSION['user']['id']);
        }

        $this->sendSuccess('Profile updated successfully', '/admin/profile');
    }

    public function showPasswordForm(): void
    {
        view('admin/profile/password', [], 'admin');
    }

    public function updatePassword(array $params): void
    {
        $rules = [
            'current_password' => ['required'],
            'password' => ['required', 'min:6'],
            'password_confirmation' => ['required', 'match:password']
        ];

        if (!$this->validator->validate($params, $rules)) {
            $this->sendValidationError($this->validator->getErrors(), $params);
        }

        $result = $this->userService->updatePassword($_SESSION['user']['id'], $params);

        if (!$result['success']) {
            $this->sendError($result['message'] ?? 'Failed to update password');
        }

        $this->sendSuccess('Password updated successfully', '/admin/profile');
    }
}