Translation Management Service - Delivery Instructions
===============================================

Quick start (development)

1. Copy `.env.example` to `.env` and set database credentials (or use Docker compose):

   cp .env.example .env

2. Run composer install and migrations:

   composer install
   php artisan key:generate
   php artisan migrate --seed

3. Serve the app:

   php artisan serve --host=0.0.0.0 --port=8000

API endpoints (examples using `curl`)

- Issue a token (creates user if missing):

  curl -s -X POST http://localhost:8000/api/v1/token -H "Content-Type: application/json" -d '{"email":"me@example.com"}'

- Create a translation (protected):

  curl -s -X POST http://localhost:8000/api/v1/translations \
    -H "Authorization: Bearer <TOKEN>" \
    -H "Content-Type: application/json" \
    -d '{"key":"greeting.hello","locale":"en","value":"Hello","tags":["web","mobile"]}'

- Update a translation:

  curl -s -X PUT http://localhost:8000/api/v1/translations/1 \
    -H "Authorization: Bearer <TOKEN>" -H "Content-Type: application/json" \
    -d '{"value":"Hi","tags":["web"]}'

- Search translations (by key, q, tags, locale):

  curl -s "http://localhost:8000/api/v1/translations?tags=web&locale=en" -H "Authorization: Bearer <TOKEN>"

- Export streamed JSON (frontend friendly):

  curl -s http://localhost:8000/api/v1/translations/export?locale=en

- Upload export to public storage (CDN-ready):

  curl -s -X POST http://localhost:8000/api/v1/translations/export/upload \
    -H "Authorization: Bearer <TOKEN>" -H "Content-Type: application/json" -d '{"locale":"en"}'

- API docs (Swagger UI):

  Open in browser: http://localhost:8000/api/v1/docs

Performance notes

- Streaming export uses a DB cursor and writes chunks; suitable for large datasets to minimize memory usage.
- `translations:generate` command supports large counts and configurable batch sizes to seed 100k+ records efficiently.
- Indexes exist on `key` and `locale`; tags are normalized into pivot table for efficient lookups.

Checklist against PDF requirements

- Store translations for multiple locales: implemented (`locale` column).
- Tag translations: implemented via `tags` table and pivot `tag_translation`.
- CRUD & search endpoints: implemented and tested.
- JSON export endpoint (streamed): implemented at `/api/v1/translations/export`.
- Export always up-to-date: streaming hits DB on request; `upload` writes current snapshot.
- Endpoints designed for ms responses; performance validated via tests locally (environment dependent).
- Command to populate 100k+ records: `php artisan translations:generate --count=100000 --batch=1000`.
- Export endpoint handles large datasets: streamed and tested.
- Token-based auth: implemented via Sanctum token issuance endpoint.
- Docker setup: `docker-compose.yml` included; `backend/Dockerfile.app` provided to build container.
- CDN support: supported via `export/upload` writing to public disk; configure cloud storage in `config/filesystems.php` if needed.
- Test coverage >95%: achieved (coverage report at `coverage.xml`).
- OpenAPI/Swagger: `openapi.json` present and UI available at `/api/v1/docs`.

If anything is missing or you want me to push the repository to GitHub, I can create a remote and push the `release/translation-service` branch (I need remote credentials or you can provide a repo URL).
