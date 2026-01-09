<?php
// simple migration script for existing schemas
require __DIR__ . '/../vendor/autoload.php';

$entityManager = require __DIR__ . '/../config/db.php';

use Doctrine\ORM\Tools\SchemaTool;

$metaData = $entityManager->getMetadataFactory()->getAllMetadata();

$schemaTool = new SchemaTool($entityManager);

$schemaTool->createSchema($metaData);

echo "✅ Schema created!\n";
