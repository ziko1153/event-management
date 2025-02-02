<?php

namespace App\Service;

use App\Enums\RoleEnum;
use App\Model\User;
use App\Model\Organization;
use Config\Database;

class UserService extends BaseService
{
    private User $userModel;
    private Organization $organizationModel;
    private ImageService $imageService;
    private \PDO $connection;

    private OrganizationService $organizationService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->organizationModel = new Organization();
        $this->imageService = new ImageService(__DIR__ . '/../../public/img/users/');
        $this->organizationService = new OrganizationService();
        $this->connection = Database::getInstance()->getConnection();
    }

    public function createUser(array $data, ?array $file = null): array
    {
        try {
            $this->connection->beginTransaction();

            if ($file && !empty($file['name'])) {
                $data['avatar'] = $this->imageService->uploadImage($file);
            }
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

            $userId = $this->userModel->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => !empty($data['phone']) ? $data['phone'] :  null,
                'address' => !empty($data['address']) ?  $data['address'] : null,
                'role' => $data['role'],
                'avatar' => $data['avatar'] ?? "img/users/default_avatar.png",
            ]);

            if ($data['role'] === RoleEnum::ORGANIZER->value) {
                $organizationData = [];
                $organizationData['user_id'] = $userId;
                $organizationData['name'] = $data['org_name'];
                $organizationData['website'] = $data['org_website'];
                $organizationData['description'] = $data['org_description'];

                $result = $this->organizationService->createOrganization(
                    $organizationData,
                    $_FILES['org_logo'] ?? null
                );

                if (!$result['success']) {
                    throw new \Exception($result['error']);
                }
            }

            $this->connection->commit();
            return ['success' => true, 'message' => 'User created successfully'];
        } catch (\Exception $e) {
            $this->connection->rollBack();
            if (isset($data['avatar'])) {
                $this->imageService->deleteImage($data['avatar']);
            }
            dd($e->getMessage());
            return ['success' => false, 'error' => 'something went wrong on server,  please try again'];
        }
    }

    public function getUsersList(array $filters, int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $whereConditions = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $whereConditions[] = "(users.name LIKE :search_name OR users.email LIKE :search_email)";
            $params[':search_name'] = "%{$filters['search']}%";
            $params[':search_email'] = "%{$filters['search']}%";
        }

        if (!empty($filters['role'])) {
            $whereConditions[] = "users.role = :role";
            $params[':role'] = $filters['role'];
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $whereConditions[] = "users.status = :status";
            $params[':status'] = $filters['status'];
        }

        $query = "SELECT 
                    users.*,
                    organizations.name as organization_name
                 FROM users
                 LEFT JOIN organizations ON users.id = organizations.user_id
                 WHERE " . implode(' AND ', $whereConditions) . "
                 AND users.role != 'admin'
                 ORDER BY users.created_at DESC
                 LIMIT :limit OFFSET :offset";

        $countQuery = "SELECT COUNT(*) as total 
                      FROM users 
                      WHERE " . implode(' AND ', $whereConditions) . " and users.role != 'admin'";

        // dd($countQuery);

        $totalCount = $this->userModel->executeRawQuery($countQuery, $params)[0]['total'] ?? 0;

        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        $users = $this->userModel->executeRawQuery($query, $params);

        return [
            'users' => $users,
            'total' => (int) $totalCount,
            'total_pages' => ceil($totalCount / $perPage)
        ];
    }

    public function updateUserStatus(int $userId, bool $status): array
    {
        try {
            $success = $this->userModel->update($userId, ['status' => $status]);

            if (!$success) {
                throw new \Exception('Failed to update user status');
            }

            return [
                'success' => true,
                'message' => 'User status updated successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function updateUser(array $user, array $data, ?array $file = null): array
    {
        try {
            $this->connection->beginTransaction();

            $updateData = [
                'name' => $data['name'],
                'phone' => empty($data['phone']) ? null : $data['phone'],
                'address' => $data['address'] ?? null,
            ];


            if (!empty($data['password'])) {
                $updateData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }

            if ($file && !empty($file['name'])) {
                $updateData['avatar'] = $this->imageService->uploadImage($file);
                if (!empty($user['avatar']) && !str_contains($user['avatar'], 'default_avatar.png')) {
                    $this->imageService->deleteImage($user['avatar']);
                }
            }

            if (!$this->userModel->update($user['id'], $updateData)) {
                throw new \Exception('Failed to update user');
            }

            if ($user['role'] === RoleEnum::ORGANIZER->value) {
                $organization = $this->organizationModel->findByColumn('user_id', $user['id']);

                if (!$organization) {
                    return $this->sendValidationError('No Organization found', 404);
                }

                $orgData = [
                    'name' => $data['org_name'],
                    'website' => $data['org_website'] ?? null,
                    'description' => $data['org_description'] ?? null
                ];

                $result = $this->organizationService->updateOrganization(
                    $user['id'],
                    $orgData,
                    $_FILES['org_logo'] ?? null
                );

                if (!$result['success']) {
                    throw new \Exception($result['error']);
                }
            }

            $this->connection->commit();
            return ['success' => true, 'message' => 'User updated successfully'];
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteUser(int $userId): array
    {
        try {
            $this->connection->beginTransaction();

            $user = $this->userModel->findById($userId);
            if (!$user) {
                return $this->sendValidationError('User not found', 404);
            }

            // Delete user's avatar if exists
            if (!empty($user['avatar']) && !str_contains($user['avatar'], 'default_avatar.png')) {
                $this->imageService->deleteImage($user['avatar']);
            }

            if ($user['role'] === RoleEnum::ORGANIZER->value) {
                $organization = $this->organizationModel->findByColumn('user_id', $userId);

                if (!$organization) {
                    return $this->sendValidationError('No Organization found', 404);
                }

                if ($organization && !empty($organization['logo'])) {
                    $this->imageService->deleteImage($organization['logo']);
                }

                $this->organizationModel->delete($organization['id']);
            }

            // Delete user
            if (!$this->userModel->delete($userId)) {
                throw new \Exception('Failed to delete user');
            }

            $this->connection->commit();
            return ['success' => true, 'message' => 'User deleted successfully'];
        } catch (\Exception $e) {
            $this->connection->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    public function updatePassword(int $userId, array $data): array
    {
        try {
            $user = $this->userModel->findById($userId);

            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            if (!password_verify($data['current_password'], $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            $updateData = [
                'password' => password_hash($data['password'], PASSWORD_BCRYPT)
            ];

            $success = $this->userModel->update($userId, $updateData);

            if (!$success) {
                return ['success' => false, 'message' => 'Failed to update password'];
            }

            return [
                'success' => true,
                'message' => 'Password updated successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Something went wrong while updating password'
            ];
        }
    }
}