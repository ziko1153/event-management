<?php

namespace App\Migrations\Database;

use App\Enums\RoleEnum;
use App\Migrations\Interface\MigrationInterface;

class CreateUserTable implements MigrationInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function up(): void
    {
        $roles = RoleEnum::getRoleEnum();
        $defaultRole = RoleEnum::USER->value;
        $enumValues = implode(',', array_map(fn($role) => "'$role'", $roles));

        $sql = "
        CREATE TABLE users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            phone VARCHAR(15)  NULL UNIQUE,
            avatar VARCHAR(255)  NULL DEFAULT 'img/users/default_avatar.png',
            address TEXT  NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM($enumValues) NOT NULL DEFAULT '$defaultRole',
            status BOOLEAN NOT NULL DEFAULT 1,
            email_verified_at TIMESTAMP NULL,
            reset_token VARCHAR(100) NULL,
            reset_token_expires_at TIMESTAMP NULL,
            remember_token VARCHAR(100) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login_at TIMESTAMP NULL,
            INDEX users_email_index (email)
        ) ENGINE=InnoDB;
    ";

        echo "Running users table migration...\n";

        $this->pdo->exec($sql);

        echo "Table 'users' created successfully.\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        echo "Table 'users' dropped successfully.\n";
    }
}