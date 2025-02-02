<?php

namespace App\Service;

use App\Model\Organization;
use Config\Database;

class OrganizationService
{
    private Organization $organizationModel;
    private ImageService $imageService;

    public function __construct()
    {
        $this->organizationModel = new Organization();
        $this->imageService = new ImageService(__DIR__ . '/../../public/img/organizations/');
    }

    public function createOrganization(array $data, ?array $logo = null): array
    {
        try {
            if ($logo && !empty($logo['name'])) {
                $data['logo'] = $this->imageService->uploadImage($logo);
            }

            $organizationId = $this->organizationModel->create($data);

            if (!$organizationId) {
                throw new \Exception('Failed to create organization');
            }

            return [
                'success' => true,
                'message' => 'Organization created successfully',
                'organization_id' => $organizationId
            ];
        } catch (\Exception $e) {
            if (isset($data['logo'])) {
                $this->imageService->deleteImage($data['logo']);
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateOrganization(int $userId, array $data, ?array $logo = null): array
    {
        try {
            $organization = $this->organizationModel->findByColumn('user_id', $userId);
            if (!$organization) {
                throw new \Exception('Organization not found');
            }

            if ($logo && !empty($logo['name'])) {
                $data['logo'] = $this->imageService->uploadImage($logo);
                if (!empty($organization['logo']) && !str_contains($organization['logo'], 'default_logo.png')) {
                    $this->imageService->deleteImage($organization['logo']);
                }
            }

            $success = $this->organizationModel->update($organization['id'], $data);

            if (!$success) {
                throw new \Exception('Failed to update organization');
            }

            return [
                'success' => true,
                'message' => 'Organization updated successfully'
            ];
        } catch (\Exception $e) {
            if (isset($data['logo']) && $logo) {
                $this->imageService->deleteImage($data['logo']);
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteOrganization(int $id): array
    {
        try {
            $organization = $this->organizationModel->findById($id);
            if (!$organization) {
                throw new \Exception('Organization not found');
            }

            if (!empty($organization['logo']) && !str_contains($organization['logo'], 'default_logo.png')) {
                $this->imageService->deleteImage($organization['logo']);
            }

            $success = $this->organizationModel->delete($id);

            if (!$success) {
                throw new \Exception('Failed to delete organization');
            }

            return [
                'success' => true,
                'message' => 'Organization deleted successfully'
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
