<?php

namespace App\Migrations\Database;

use App\Migrations\Interface\MigrationInterface;

class CreateEventCategoryRelationTable implements MigrationInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function up(): void
    {


        $sql = "
        CREATE TABLE event_categories_relations (
            event_id BIGINT UNSIGNED NOT NULL,
            category_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (event_id, category_id),
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
        ";

        echo "Running event_categories_relations table migration...\n";

        $this->pdo->exec($sql);

        echo "Table 'event_categories_relations' created successfully.\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS event_categories_relations");
        echo "Table 'event_categories_relations' dropped successfully.\n";
    }
}
