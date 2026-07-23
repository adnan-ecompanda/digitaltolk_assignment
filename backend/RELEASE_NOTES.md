Release Notes - Translation Management Service
============================================

Summary
-------
- Streaming JSON export endpoint: `GET /api/v1/translations/export?locale=...`
- Normalized tags with pivot table and indexed searches
- CRUD + search endpoints under `api/v1/translations` protected by Sanctum token
- `translations:generate` Artisan command for efficient bulk generation (supports 100k+)
- Tests cover models, controllers, generator, export streaming, pagination, auth, and validation

Coverage
--------
- Achieved test coverage: 97.4% (Clover report at `backend/coverage.xml`)

Key Files
---------
- Controller: `app/Http/Controllers/Api/TranslationController.php`
- Command: `app/Console/Commands/GenerateTranslations.php`
- Routes: `routes/api.php`
- Tests: `tests/` (unit & feature)

How to run tests and coverage
-----------------------------
1. Run tests:

   php vendor/bin/phpunit --testdox

2. Generate coverage (requires Xdebug):

   php -d xdebug.mode=coverage vendor/bin/phpunit --coverage-clover=coverage.xml

Notes
-----
- Export endpoint is implemented as a streamed JSON object for low memory use and always-up-to-date responses.
- Use `php artisan translations:generate --count=100000 --batch=1000` to generate large datasets (ensure sufficient DB resources).
