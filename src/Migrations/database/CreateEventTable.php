<?php

namespace App\Migrations\Database;

use App\Enums\EventStatusEnum;
use App\Enums\EventTypeEnum;
use App\Migrations\Interface\MigrationInterface;

class CreateEventTable implements MigrationInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function up(): void
    {
        $eventTypes = implode(',', array_map(fn($type) => "'$type'", EventTypeEnum::getEventEnum()));
        $eventStatuses = implode(',', array_map(fn($status) => "'$status'", EventStatusEnum::getEventStatusEnum()));
        $defaultEventStatus = EventStatusEnum::DRAFT->value;

        $sql = "
        CREATE TABLE events (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            created_by BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            thumbnail VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            location VARCHAR(255),
            venue_details TEXT,
            start_date DATETIME NOT NULL,
            end_date DATETIME NOT NULL,
            registration_deadline DATETIME NOT NULL,
            max_capacity INT UNSIGNED NOT NULL DEFAULT 0,
            current_capacity INT UNSIGNED NOT NULL DEFAULT 0,
            ticket_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            event_type ENUM($eventTypes) NOT NULL,
            status ENUM($eventStatuses) NOT NULL DEFAULT '$defaultEventStatus',
            is_featured BOOLEAN NOT NULL DEFAULT FALSE,
            is_private BOOLEAN NOT NULL DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
            INDEX events_slug_index (slug),
            INDEX events_start_date_index (start_date),
            INDEX events_status (status),
            INDEX events_featured (is_featured),
            FULLTEXT INDEX events_event_search (title, description)
        ) ENGINE=InnoDB;
    ";

        echo "Running events table migration...\n";

        $this->pdo->exec($sql);

        echo "Table 'events' created successfully.\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS events");
        echo "Table 'events' dropped successfully.\n";
    }
}
