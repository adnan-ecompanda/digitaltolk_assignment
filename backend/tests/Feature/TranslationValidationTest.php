<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationValidationTest extends TestCase
{
    use RefreshDatabase;

    private function getToken(): string
    {
        return $this->postJson('/api/v1/token', ['email' => 'tester@example.com'])->json('token');
    }

    public function test_create_requires_key_locale_value(): void
    {
        $token = $this->getToken();
        $res = $this->withHeader('Authorization','Bearer '.$token)->postJson('/api/v1/translations', []);
        $res->assertStatus(422);
        $res->assertJsonValidationErrors(['key','locale','value']);
    }
}
