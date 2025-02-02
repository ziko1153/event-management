<?php

namespace App\Migrations\Database;

use App\Migrations\Interface\MigrationInterface;

class CreateEventRegistrationTable implements MigrationInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function up(): void
    {
        $sql = "
        CREATE TABLE event_registrations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            registration_number VARCHAR(50) NOT NULL,
            payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
            amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            payment_method VARCHAR(50) NULL,
            payment_details JSON NULL,
            registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_registration (event_id, user_id),
            INDEX idx_registration_number (registration_number)
        ) ENGINE=InnoDB;
        ";

        $this->pdo->exec($sql);
        echo "Table 'event_registrations' created successfully.\n";
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS event_registrations");
        echo "Table 'event_registrations' dropped successfully.\n";
    }
}
