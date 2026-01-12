up:
	docker compose up -d --build

down:
	docker compose down

logs:
	docker compose logs -f

cli:
	docker compose exec php sh

test:
	docker compose exec php vendor/bin/phpunit

migrate:
	docker compose exec -w /var/www/app php php scripts/migration.php

seed:
	docker compose exec -w /var/www/app php php scripts/seeder.php

import:
	docker compose exec -w /var/www/app php php bin/console import $(file)
	
report:
	docker compose exec -w /var/www/app php php bin/console report $(date)

drop:
	docker compose exec -w /var/www/app php php scripts/drop.php