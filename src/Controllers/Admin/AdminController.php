<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Enums\RoleEnum;
use App\Model\User;
use App\Model\Event;
use App\Model\Organization;
use App\Service\UserService;
use App\Service\ValidationService;
use Throwable;

class AdminController extends BaseController
{
    private User $userModel;
    private Organization $organizationModel;
    private Event $eventModel;
    private ValidationService $validator;
    private UserService $userService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->organizationModel = new Organization();
        $this->eventModel = new Event();
        $this->validator = new ValidationService();
        $this->userService = new UserService();
    }

    public function dashboard(): void
    {
        $conditions = [];
        if (isUserOrganizer()) {
            $conditions['organizer_id'] = $_SESSION['user']['organizer']['id'];
        }

        $stats = [
            'total_users' => !isUserOrganizer() ? $this->userModel->count() : [],
            'total_events' => $this->eventModel->count($conditions),
            'recent_events' => $this->eventModel->findWithQuery(
                ['events.*', 'organizations.name as organizer_name'],
                $conditions,
                [['type' => 'LEFT', 'table' => 'organizations', 'on' => 'events.organizer_id = organizations.id']],
                ['events.created_at' => 'DESC'],
                '',
                5
            )
        ];

        view('admin/dashboard', [
            'title' => 'Dashboard',
            'stats' => $stats
        ], 'admin');
    }

    public function users(array $params): void
    {
        $page = $params['page'] ?? 1;
        $filters = [
            'search' => $params['search'] ?? '',
            'role' => $params['role'] ?? '',
            'status' => $params['status'] ?? '',
        ];


        if ($this->isAjaxRequest()) {
            $result = $this->userService->getUsersList($filters, (int) $page);
            $this->jsonResponse([
                'success' => true,
                'users' => $result['users'],
                'pagination' => [
                    'current' => (int) $page,
                    'total' => $result['total_pages']
                ]
            ]);
        }


        view('admin/users/index', [
            'title' => 'User Management',
            'roles' => [RoleEnum::USER->value, RoleEnum::ORGANIZER->value],
            'filters' => $filters,
        ], 'admin');
    }

    public function storeUser(array $params): void
    {
        $rules = [
            'name' => ['required', 'min:3'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6'],
            'role' => ['required', 'enum:' . implode(',', [RoleEnum::USER->value, RoleEnum::ORGANIZER->value])],
            'avatar' => ['nullable', 'image', 'type:jpg,jpeg,png', 'size:1mb'],
            'phone' => ['nullable', 'numeric', 'min:11', 'max:11', 'unique:users,phone'],
            'address' => ['nullable', 'min:5'],
        ];

        if ($params['role'] === 'organizer') {
            $rules['org_name'] = ['required', 'min:3'];
            $rules['org_website'] = ['nullable', 'url'];
            $rules['org_description'] = ['nullable', 'min:5'];
            $rules['org_logo'] = ['nullable', 'image', 'type:jpg,jpeg,png', 'size:1mb'];
            $rules['org_website'] = ['nullable', 'url'];
        }

        if (!$this->validator->validate($params, $rules)) {
            $this->sendValidationError($this->validator->getErrors(), $params);
        }

        $result = $this->userService->createUser($params, $_FILES['avatar'] ?? null);

        if (!$result['success']) {
            $this->sendError($result['error']);
        }

        $this->sendSuccess($result['message'], '/admin/users');
    }

    public function updateUserStatus(array $params): void
    {
        if (!isset($params['user_id']) || !isset($params['status'])) {
            $this->sendError('Invalid request');
        }

        $result = $this->userService->updateUserStatus((int) $params['user_id'], (bool) $params['status']);

        if (!$result['success']) {
            $this->sendError($result['error']);
        }

        $this->sendSuccess($result['message']);
    }

    public function getUser(array $params): void
    {
        if ($this->isAjaxRequest()) {
            $user = $this->userModel->findById($params['id']);
            $organization = null;

            if ($user['role'] === 'organizer') {
                $organization = $this->organizationModel->findByColumn('user_id', $user['id']);
            }

            $data =  [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'avatar' => $user['avatar'],
                    'address' => $user['address'],
                    'role' => $user['role'],
                    'status' => $user['status'],
                ],
                'organization' => $organization
            ];

            $this->jsonResponse($data);
            exit;
        }
    }

    public function updateUser(array $params): void
    {
        try {
            $user = $this->userModel->findById($params['id']);
            if (!$user) {
                $this->sendError('User not Found');;
            }

            $rules = [
                'name' => ['required', 'min:3'],
                'avatar' => ['nullable', 'image', 'type:jpg,jpeg,png', 'size:1mb'],
                'phone' => ['nullable', 'numeric', 'min:11', 'max:11', 'unique:users,phone,' . $user['id']],
                'password' => ['nullable', 'min:6'],
                'address' => ['nullable', 'min:5'],
            ];

            if ($user['role'] === RoleEnum::ORGANIZER->value) {
                $rules['org_name'] = ['required', 'min:3'];
                $rules['org_website'] = ['nullable', 'url'];
                $rules['org_description'] = ['nullable', 'min:5'];
                $rules['org_logo'] = ['nullable', 'image', 'type:jpg,jpeg,png', 'size:1mb'];
            }

            if (!$this->validator->validate($params, $rules)) {
                $this->sendValidationError($this->validator->getErrors(), $params);
            }


            $result = $this->userService->updateUser($user, $params, $_FILES['avatar'] ?? null);

            if (!$result['success']) {
                $this->sendError($result['error']);
            }

            $this->sendSuccess($result['message']);
        } catch (Throwable $e) {
            dd([$e->getMessage(), $e->getFile(), $e->getLine()]);
            // $this->sendError('Something went wrong on server');
        }
    }

    public function deleteUser(array $params): void
    {
        try {
            if ($this->isAjaxRequest()) {
                if (!isset($params['id'])) {
                    $this->sendError('Invalid request');
                }

                $result = $this->userService->deleteUser((int) $params['id']);

                if (!$result['success']) {
                    if (isset($result['validation_error'])) {
                        $this->sendError($result['message']);
                    } else {
                        $this->sendError('Something went wrong on server,please try again later');
                    }
                }

                $this->sendSuccess($result['message']);
            }
        } catch (Throwable $e) {
            $this->sendError('Something went wrong on server, try again');
        }
    }
}