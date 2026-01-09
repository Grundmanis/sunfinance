<?php

use App\Entity\Customer;
use App\Entity\Loan;

require __DIR__ . '/../vendor/autoload.php';

$entityManager = require __DIR__ . '/../config/db.php';

// customers 
$jsonFilePath = __DIR__ . '/../exampleData/json/customers.json';
$jsonData = json_decode(file_get_contents($jsonFilePath), true);

if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
    die('Error decoding JSON: ' . json_last_error_msg());
}

foreach ($jsonData as $item) {
    $customer = new Customer();
    $customer->setId($item['id']);
    $customer->setFirstname($item['firstname']);
    $customer->setLastname($item['lastname']);
    if (isset($item['email'])) {
        $customer->setEmail($item['email']);
    }
    if (isset($item['phone'])) {
        $customer->setPhoneNumber($item['phone']);
    }
    if (isset($item['ssn'])) {
        $customer->setSsn($item['ssn']);
    }
    $entityManager->persist($customer);
}
$entityManager->flush();

// loans
$jsonFilePath = __DIR__ . '/../exampleData/json/loans.json';
$jsonData = json_decode(file_get_contents($jsonFilePath), true);

if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
    die('Error decoding JSON: ' . json_last_error_msg());
}

foreach ($jsonData as $item) {
    $customer = new Loan();

    //

    $entityManager->persist($customer);
}
$entityManager->flush();

echo "✅ Seeder ran successfully!\n";
