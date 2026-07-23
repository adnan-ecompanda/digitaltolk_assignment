<?php

use App\Http\Controllers\Api\TranslationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public export endpoint
    Route::get('translations/export', [TranslationController::class, 'export']);

    // OpenAPI JSON
    Route::get('/openapi.json', function () {
        return response()->file(base_path('openapi.json'));
    });

    // Swagger UI served from a simple view (loads /api/v1/openapi.json)
    Route::get('docs', function () {
        return response()->view('swagger');
    });

    // Token issuance for tests / clients (simple - creates user if not exists)
    Route::post('token', function (\Illuminate\Http\Request $request) {
        $request->validate(['email' => 'required|email', 'name' => 'sometimes|string']);
        $user = App\Models\User::firstOrCreate(
            ['email' => $request->input('email')],
            ['name' => $request->input('name', 'ApiUser'), 'password' => bcrypt(\Illuminate\Support\Str::random(16))]
        );

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token]);
    });

    // Protected CRUD routes
    Route::middleware('auth:sanctum')->apiResource('translations', TranslationController::class);

    // Protected export-to-storage (CDN support) - saves latest export to storage/public and returns a public URL
    Route::post('translations/export/upload', [TranslationController::class, 'uploadExport'])->middleware('auth:sanctum');
});
