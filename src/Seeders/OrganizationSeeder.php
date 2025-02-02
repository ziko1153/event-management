<?php

namespace App\Seeders;

use App\Model\Organization;
use App\Model\User;

class OrganizationSeeder
{
    public function run(): void
    {
        $organizationModel = new Organization();
        $userModel = new User();

        $organizerUsers = $userModel->findAll(['role' => 'organizer']);

        $organizations = [
            [
                'name' => 'Tech Events BD',
                'description' => 'Leading tech event organizer in Bangladesh',
                'website' => 'https://ziko.dev',
            ],
            [
                'name' => 'Creative Solutions BD',
                'description' => 'Creative event management company',
                'website' => 'https://ziko.dev',
            ]
        ];

        foreach ($organizations as $key => $org) {
            if (isset($organizerUsers[$key])) {
                $org['user_id'] = $organizerUsers[$key]['id'];
                $organizationModel->create($org);
            }
        }

        echo "Organizations seeded successfully!\n";
    }
}
