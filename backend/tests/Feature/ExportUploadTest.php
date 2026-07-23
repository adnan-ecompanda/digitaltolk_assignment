<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_export_writes_file_and_returns_url(): void
    {
        Storage::fake('public');

        $token = $this->postJson('/api/v1/token', ['email' => 'uploader@example.com'])->json('token');

        // create a translation to export
        $this->postJson('/api/v1/translations', ['key' => 't1', 'locale' => 'en', 'value' => 'v1'], ['Authorization' => 'Bearer '.$token]);

        $res = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/v1/translations/export/upload', ['locale' => 'en']);
        $res->assertStatus(200)->assertJsonStructure(['url']);

        Storage::disk('public')->assertExists('translations_en.json');
    }
}
