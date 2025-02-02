<?php

namespace App\Migrations\Database;

use App\Migrations\Interface\MigrationInterface;

class CreateOrganizationTable implements MigrationInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function up(): void
    {
        $sql = "
        CREATE TABLE organizations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT NULL,
            website VARCHAR(255) NULL,
            logo VARCHAR(255) NULL DEFAULT 'img/organizations/default_logo.png',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX organizations_user_id_index (user_id)
        ) ENGINE=InnoDB;
        ";

        $this->pdo->exec($sql);
        echo "Table 'organizations' created successfully.\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS organizations");
        echo "Table 'organizations' dropped successfully.\n";
    }
}
