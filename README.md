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
or
make cli
composer require --dev phpunit/phpunit

Test:
make test

The plan:

1. Import CSV files (batches)
2. Api call (single payment)

Payment import and API request has loan number in description. Consists of 2 letters and 8 numbers, starts with LN.

3. Validate

   - duplicate entry (paymentReference or refId)
   - negative amount
   - invalid date

4. Save to the storage

5. Resources

- loan
- customer
- payment

6. Logs
