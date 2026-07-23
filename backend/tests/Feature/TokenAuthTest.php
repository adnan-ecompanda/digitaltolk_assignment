<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_issuance_and_protected_routes(): void
    {
        $resp = $this->postJson('/api/v1/token', ['email' => 'foo@example.com']);
        $resp->assertStatus(200);
        $token = $resp->json('token');
        $this->assertIsString($token);

        // protected without token
        $this->postJson('/api/v1/translations', [])->assertStatus(401);

        // protected with token
        $payload = ['key'=>'x','locale'=>'en','value'=>'v'];
        $this->withHeader('Authorization','Bearer '.$token)
            ->postJson('/api/v1/translations', $payload)
            ->assertStatus(201);
    }
}
