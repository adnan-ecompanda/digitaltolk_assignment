<?php

namespace Tests\Feature;

use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportStreamingTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_returns_json_object(): void
    {
        Translation::factory()->create(['key' => 'a', 'locale' => 'en', 'value' => 'A']);
        Translation::factory()->create(['key' => 'b', 'locale' => 'en', 'value' => 'B']);

        $token = $this->postJson('/api/v1/token', ['email' => 'exporter@example.com'])->json('token');

        $res = $this->withHeader('Authorization','Bearer '.$token)->get('/api/v1/translations/export?locale=en');
        $res->assertStatus(200);

        $contentType = $res->headers->get('content-type');
        $this->assertStringContainsString('application/json', $contentType);
    }
}
