<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Helpers/ServiceContainer.php';
require_once __DIR__ . '/../config/Database.php';


// Create a service container
$container = new ServiceContainer();

// Register the Database service
$container->set(Database::class, fn() => Database::getInstance());