# Run

1. Run docker https://www.docker.com/
2. Run project with
   make up
   or
   docker composer up --build

Other commands:
make up
make down -
make logs - Enter Docker logs
make cli - Enter PHP container
make import
make test

Api:
http://localhost:8080

CLI:
docker compose exec php php import.php

Package installation:
docker compose exec php composer require --dev phpunit/phpunit

Test:
make test

Db logs:
docker compose logs -f db

To run migrations once:
make migrate

To seed:
make seed

The plan:

Stack:
Doctrine - storage
Symfony Validator - validation
Monolog - logging
Doctrine Migrations - migration/seeds
Symfony Console - import/report
Symfony Dotenv - .env
phpunit - test
league/csv - csv handling

Api:
symfony/http-foundation - request/response objects
symfony/routing - routing

Preparations:

1. Customer class
   "id": "c539792e-7773-4a39-9cf6-f273b2581438",
   "firstname": "Pupa",
   "lastname": "Lupa",
   "ssn": "0987654321", ?? not needed?
   "email": "pupa.lupa@example.com"

2. Loan class
   "id": "51ed9314-955c-4014-8be2-b0e2b13588a5",
   "customerId": "c539792e-7773-4a39-9cf6-f273b2581438",
   "reference": "LN12345678",
   "state": "ACTIVE",
   "amount_issued": "100.00", // camelCase
   "amount_to_pay": "120.00" // camelCase
   "createdAt"
   "updatedAt"

3. Payment class
   "id": "c539792e-7773-4a39-9cf6-f273b2581438",
   "loanId": "51ed9314-955c-4014-8be2-b0e2b13588a5",
   "loanRef" optional, for audit
   "fistName" ? payment can be made by someone else
   "lastName" ? payment can be made by someone else
   "createdAt"
   "state": "ASSIGNED", // REFUND
   "paymentDate"
   "amount" 99 and -99
   "refId" : external ref id string
   "descripton": "text whatever"
   "nationalSecurityNumber" ? nullable - we save since we receive it by requirement

4. Create logger - use monolib

5. Use sqlite for speed and simplicity - doctrine

6. Migrate and seed customers.json - sumfony migration
   2.1. Migrate and seed loans.json
   2.2. Migrate and payments

Process:

1. DONE - Importing csv file which contains following fields: **import --file=<FILE_PATH>**
   DONE - (required) paymentDate - "Wed, 14 Dec 2022 11:20:45 +0000" / 20221310235959
   DONE - (required) payerName - Pupa / Armands
   DONE - (required) payerSurname - Lupa / Grundmanis
   DONE - (required) amount - 17.99 / -20
   DONE - (required?) nationalSecurityNumber - 1234567890 / null
   DONE - (required - loan number is required in description) description - whatever + has loan number in description. Consists of 2 letters and 8 numbers, starts with LN.
   DONE - (required) paymentReference - ffsd2342134 / refId

Loop through every record and: 2. Validate:

DONE - duplicate entry (paymentReference or refId)
DONE - negative amount
DONE - invalid date
DONE - Get Loan number , error "4" - no loan number in description.
DONE - 2.1. Create ResponseError class for Console -
DONE - Duplicate entry - 1,
DONE - Negative amount - 2,
DONE - Invalid date - 3,
DONE - All fine - 0,
DONE - 4 - "No laon number in description",
DONE - - "No required field"

3. Save to store with following logic:
   When payment amount equals to matched loan amount to pay

   - Mark loan as paid
   - Mark payment as assigned
     When payment amount is greater than matched loan amount to pay
   - Mark loan as paid
   - Mark payment as partially assigned
   - Create refund payment as separate entity called "Payment Order" with all necessary information
     When payment amount is less than matched load amount to pay
   - Mark payment as assigned

4. Implement communicaiton using Events:

- Payment received: communication sent to email and|or phone if defined
- Loan fully paid: communication sent to email and|or phone if defined
- Failed payments report: support@example.com

5. Implement Console interface:

- Show payments by date `report --date=YYYY-MM-DD`

7. Implement API and reuse existing classes
   1. API (single payment) - **{app_url}/api/payment**
      Request body example:
      {
      "firstname": "Lorem",
      "lastname": "Ipsum",
      "paymentDate": "2022-12-12T15:19:21+00:00",
      "amount": "99.99",
      "description": "Lorem ipsum dolorLN20221212 sit amet...",
      "refId": "dda8b637-b2e8-4f79-a4af-d1d68e266bf5"
      },
      {
      "firstname": "Lorem",
      "lastname": "Ipsum",
      "paymentDate": "2022-12-12T15:19:21+00:00",
      "amount": "99.99",
      "description": "LN20221212",
      "refId": "130f8a89-51c9-47d0-a6ef-1aea54924d3b"
      }
   2. Errors:
   - API: Duplicate entry - 409, rest of errors - 400, All fine - 2XX depending on implementation
