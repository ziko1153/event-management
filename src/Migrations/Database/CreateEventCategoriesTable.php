<?php

namespace App\Migrations\Database;

use App\Migrations\Interface\MigrationInterface;

class CreateEventCategoriesTable implements MigrationInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function up(): void
    {


        $sql = "
        CREATE TABLE event_categories (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX event_categories_slug_index (slug)
        ) ENGINE=InnoDB;
        ";

        echo "Running event_categories table migration...\n";

        $this->pdo->exec($sql);

        echo "Table 'event_categories' created successfully.\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS event_categories");
        echo "Table 'event_categories' dropped successfully.\n";
    }
}
