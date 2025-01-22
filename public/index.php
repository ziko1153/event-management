<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Config\Database;

try {

    $db = Database::getInstance();

    $connection = $db->getConnection();
    
    $stmt = $connection->query('SELECT * FROM users');
    $users = $stmt->fetchAll();

    print_r($users);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally
{
    $db->releaseConnection($connection);
}