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

Api:
http://localhost:8080

CLI:
docker compose exec php php import.php
