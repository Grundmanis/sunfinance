<?php
// Script to drop all tables in the database
require __DIR__ . '/../vendor/autoload.php';

$entityManager = require __DIR__ . '/../config/db.php';

use Doctrine\ORM\Tools\SchemaTool;

$metaData = $entityManager->getMetadataFactory()->getAllMetadata();

if (empty($metaData)) {
    echo "❌ No metadata found. Ensure your entities are properly configured.\n";
    exit(1);
}

$schemaTool = new SchemaTool($entityManager);

$schemaTool->dropSchema($metaData);

echo "✅ All tables dropped successfully!\n";
