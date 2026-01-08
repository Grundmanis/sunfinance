up:
	docker compose up -d --build

down:
	docker compose down

logs:
	docker compose logs -f

cli:
	docker compose exec php sh

import:
	docker compose exec php php import.php

test:
	docker compose exec php vendor/bin/phpunit