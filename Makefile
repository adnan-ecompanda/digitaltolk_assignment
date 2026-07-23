.PHONY: up build shell migrate seed test

up:
	docker-compose up -d --build

shell:
	docker exec -it translation_app bash

migrate:
	cd backend && php artisan migrate

seed:
	cd backend && php artisan db:seed

test:
	cd backend && php artisan test
