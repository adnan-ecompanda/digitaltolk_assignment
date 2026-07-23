<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_export_translations(): void
    {
        $payload = [
            'key' => 'welcome.message',
            'locale' => 'en',
            'value' => 'Welcome!',
            'tags' => ['web','mobile'],
        ];

        // get token for a test user
        $tokenRes = $this->postJson('/api/v1/token', ['email' => 'test@example.com']);
        $token = $tokenRes->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/translations', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['key' => 'welcome.message']);

        $this->getJson('/api/v1/translations/export?locale=en')
            ->assertStatus(200)
            ->assertJsonFragment(['welcome.message' => 'Welcome!']);
    }
}
