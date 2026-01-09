<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

$paths = [__DIR__ . '/../src/Entity'];
$isDevMode = true;

$databaseUrl = $_ENV['DATABASE_URL'];
if (!$databaseUrl) {
    throw new RuntimeException('DATABASE_URL is not defined');
}

$dbParams = [
    'driver'   => $_ENV['DB_DRIVER'],
    'host'     => $_ENV['DB_HOST'],
    'port'     => $_ENV['DB_PORT'],
    'user'     => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD'],
    'dbname'   => $_ENV['DB_NAME'],
];

$config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode);

$connection = DriverManager::getConnection($dbParams, $config);
$entityManager = new EntityManager($connection, $config);

// try {
//     // execute a trivial query to force connection
//     $connection->executeQuery('SELECT 1');

//     echo "✅ Database connection is working!\n";
// } catch (\Exception $e) {
//     echo "❌ Database connection failed: " . $e->getMessage() . "\n";
// }


return $entityManager;
