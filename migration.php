<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Migrations\Database\CreateEventCategoriesTable;
use App\Migrations\Database\CreateEventCategoryRelationTable;
use App\Migrations\Database\CreateEventRegistrationTable;
use App\Migrations\Database\CreateEventTable;
use App\Migrations\Database\CreateUserTable;
use App\Migrations\Seeder\EventSeeder;
use App\Migrations\Seeder\UserSeeder;
use Config\Database;

$db = Database::getInstance();
$connection = $db->getConnection();
// List available migrations and seeders
$availableMigrations = [
    new CreateUserTable($connection),
    new CreateEventTable($connection),
    new CreateEventCategoriesTable($connection),
    new CreateEventCategoryRelationTable($connection),
    new CreateEventRegistrationTable($connection)
];

$availableSeeders = [
    new UserSeeder,
    new EventSeeder
];

function rollback($availableMigrations)
{
    $availableMigrations = array_reverse($availableMigrations);
    foreach ($availableMigrations as $migration) {
        $migration->down();
    }
}

function migrate($availableMigrations)
{
    foreach ($availableMigrations as $migration) {
        $migration->up();
    }
}

function seeder($availableSeeders)
{
    foreach ($availableSeeders as $seeder) {
        $seeder->run();
    }
}

// Handle command-line arguments
$options = getopt('', ['migrate', 'rollback', 'seed', 'file:']);

if (isset($options['migrate'])) {
    rollback($availableMigrations);
    migrate($availableMigrations);
    seeder($availableSeeders);
} elseif (isset($options['rollback'])) {
    rollback($availableMigrations);
} elseif (isset($options['seed'])) {
    seeder($availableSeeders);
} elseif (isset($options['file'])) {
    $fileName = $options['file'];
    $migrationFound = false;
    foreach ($availableMigrations as $migration) {
        $migrationClass = (new ReflectionClass($migration))->getShortName();
        if ($migrationClass === $fileName) {
            $migration->up();
            $migrationFound = true;
            break;
        }
    }
    if (!$migrationFound) {
        echo "Migration file '{$fileName}' not found.\n";
    }
} else {
    echo "Usage:\n";
    echo "  php migrate.php --migrate\n";
    echo "  php migrate.php --rollback\n";
    echo "  php migrate.php --file=CreateUserTable\n";
}