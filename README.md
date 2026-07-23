 # Translation Management Service (Laravel)

 This repository contains a complete Translation Management Service implemented in Laravel.

 Overview
 - Localizable translations stored with `(key, locale, value, context)`.
 - Normalized `tags` and a pivot `tag_translation` for contextual tagging and efficient search.
 - CRUD and search API with token auth (Sanctum) and OpenAPI/Swagger UI.
 - Streamed JSON export endpoint and an upload endpoint which writes snapshot files to `storage/app/public` (CDN-ready).
 - `translations:generate` artisan command to populate the database in batches (supports 100k+ records).

 Quickstart (Docker, recommended)

 1. From repo root, build and start containers:

		```bash
		docker-compose up -d --build
		```

 2. Prepare the app (run inside container or locally from `backend`):

		```bash
		# from repo root
		cd backend
		composer install
		cp .env.example .env
		php artisan key:generate
		php artisan migrate --force
		php artisan storage:link
		```

 3. Seed a smoke dataset (1,000 rows) or large dataset (100k+):

		```bash
		# smoke test
		php artisan translations:generate 1000 --batch=500

		# large (sample):
		php artisan translations:generate 100000 --batch=1000
		```

 4. Run tests and measure coverage:

		```bash
		composer install --dev
		php vendor/bin/phpunit --testdox
		# coverage (phpdbg / xdebug required)
		php -d xdebug.mode=coverage vendor/bin/phpunit --coverage-clover=coverage.xml
		```

 API endpoints (examples)

 - Issue a token (creates a user if missing):

	 ```bash
	 curl -s -X POST http://localhost:8000/api/v1/token -d "email=me@example.com"
	 ```

 - Create a translation (protected):

	 ```bash
	 curl -s -X POST http://localhost:8000/api/v1/translations \
		 -H "Authorization: Bearer <TOKEN>" \
		 -H "Content-Type: application/json" \
		 -d '{"key":"greeting.hello","locale":"en","value":"Hello","tags":["web","mobile"]}'
	 ```

 - Stream export (up-to-date JSON, streamed):

	 ```bash
	 curl -s http://localhost:8000/api/v1/translations/export?locale=en
	 ```

 - Upload export to public storage (returns a URL):

	 ```bash
	 curl -s -X POST http://localhost:8000/api/v1/translations/export/upload \
		 -H "Authorization: Bearer <TOKEN>" -H "Content-Type: application/json" -d '{"locale":"en"}'
	 ```

 - API docs (Swagger UI):

	 Open in browser: http://localhost:8000/api/v1/docs

 Performance and implementation notes
 - Streaming export uses `cursor()` and `StreamedResponse` to avoid memory spikes and always return current DB state.
 - `translations:generate` uses batch inserts for efficiency and predictable memory usage.
 - Indexes exist on `key` and `locale`; searching by tags uses `whereHas` on the pivot table.
 - Upload writes to the configured `public` disk; configure cloud disks in `config/filesystems.php` to use a CDN or S3.

 Checklist vs. PDF requirements
 - Multiple locales: implemented (`locale` column on translations).
 - Tags: normalized `tags` table + pivot `tag_translation` implemented and tested.
 - CRUD/search endpoints: implemented with tests.
 - JSON export (streamed): implemented at `/api/v1/translations/export`.
 - Export always up-to-date: streaming queries the DB at request time.
 - Endpoint latency: lightweight endpoints are designed for ms responses; performance depends on host and DB.
 - Bulk generator: `php artisan translations:generate` supports large counts.
 - Export for large datasets: streaming + `upload` supports large exports.
 - Token auth: implemented via Sanctum token issuance endpoint.
 - Docker: `docker-compose.yml` and `backend/Dockerfile.app` provided.
 - CDN support: `export/upload` writes to `storage/app/public`; swap `filesystems` for S3/CDN.
 - Tests & coverage: PHPUnit tests included; coverage report at `backend/coverage.xml` after running with coverage enabled.

 Repository layout (important files)
 - `backend/` : Laravel app (controllers, models, migrations, tests)
 - `docker-compose.yml` : app + db services
 - `backend/Dockerfile.app` : image used by compose
 - `backend/openapi.json` and `/api/v1/docs` : API specification and Swagger UI

 If you want, I can now create a GitHub repository for this project and push the `release/translation-service` branch. If you'd prefer to provide a remote URL, I will use that. Otherwise I'll try to create the repository using your authenticated `gh` CLI session.

