<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Model\User;
use App\Service\ImageService;
use App\Service\ValidationService;
use Exception;

class UserProfileController extends BaseController
{
    private User $userModel;
    private ValidationService $validator;
    private ImageService $imageService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->validator = new ValidationService();
        $this->imageService = new ImageService(__DIR__ . '/../../../public/img/users/');
    }

    public function show(): void
    {
        $user = $this->userModel->findById($_SESSION['user']['id']);
        view('user/profile/show', ['user' => $user]);
    }

    public function update(array $params): void
    {
        try {

            $rules = [
                'name' => ['nullable', 'min:3'],
                'phone' => ['nullable', 'numeric', 'min:11', 'max:11'],
                'address' => ['nullable', 'min:5'],
                'password' => ['nullable', 'min:6'],
                'password_confirmation' => ['nullable', 'match:password'],
                'avatar' => ['nullable', 'image', 'type:jpg,jpeg,png', 'size:1mb']
            ];

            if (!$this->validator->validate($params, $rules)) {
                $this->sendValidationError($this->validator->getErrors(), $params);
            }

            if ($_FILES['avatar'] && !empty($_FILES['avatar']['name'])) {
                $oldThumbnail = $_SESSION['user']['avatar'];
                $params['avatar'] = $this->imageService->uploadImage($_FILES['avatar']);

                if ($oldThumbnail && !str_contains($oldThumbnail, 'default_avatar.png')) {
                    $this->imageService->deleteImage($oldThumbnail);
                }
            }

            $updateData = array_filter([
                'phone' => $params['phone'] ?? null,
                'name' => $params['name'] ?? null,
                'address' => $params['address'] ?? null,
                'password' => !empty($params['password'])
                    ? password_hash($params['password'], PASSWORD_BCRYPT)
                    : null,
                'avatar' => $params['avatar'] ?? null

            ], fn($value) => !is_null($value)  && !empty($value));


            $result = $this->userModel->update($_SESSION['user']['id'], $updateData);

            if (!$result) {
                $this->sendError('Failed to update profile');
            }

            $_SESSION['user'] = $this->userModel->findById($_SESSION['user']['id']);

            $this->sendSuccess('Profile updated successfully', '/user/profile');
            exit;
        } catch (Exception $e) {
            $this->sendError('Something went wrong on server');
            exit;
        }
    }
}