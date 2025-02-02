<?php

namespace App\Migrations\Seeder;

use App\Enums\RoleEnum;
use App\Model\User;

class UserSeeder
{
    public function run(): void
    {
        $userModel = new User();

        $users = [
            [
                'name' => 'Tahmid Ziko',
                'email' => 'tahmidziko@test.com',
                'password' => password_hash('12345678', PASSWORD_BCRYPT),
                'role' => RoleEnum::ADMIN->value,
                'status' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'remember_token' => bin2hex(random_bytes(10)),
                'last_login_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Md Nur',
                'email' => 'nur@test.com',
                'password' => password_hash('12345678', PASSWORD_BCRYPT),
                'role' => RoleEnum::USER->value,
                'status' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'remember_token' => bin2hex(random_bytes(10)),
                'last_login_at' => null,
            ],
            [
                'name' => 'Niharika Telecom',
                'email' => 'niharika@test.com',
                'password' => password_hash('12345678', PASSWORD_BCRYPT),
                'role' => RoleEnum::ORGANIZER->value,
                'status' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'remember_token' => bin2hex(random_bytes(10)),
                'last_login_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Abdullah Enterprize',
                'email' => 'abdullah@test.com',
                'password' => password_hash('12345678', PASSWORD_BCRYPT),
                'role' => RoleEnum::ORGANIZER->value,
                'status' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'remember_token' => bin2hex(random_bytes(10)),
                'last_login_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $userModel->bulkInsert($users);

        echo "Users seeded successfully.\n";
    }
}
