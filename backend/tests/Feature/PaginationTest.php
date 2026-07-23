<?php

namespace Tests\Feature;

use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    private function token(): string
    {
        return $this->postJson('/api/v1/token', ['email' => 'pager@example.com'])->json('token');
    }

    public function test_index_paginates_results(): void
    {
        Translation::factory()->count(30)->create();
        $token = $this->token();

        $res = $this->withHeader('Authorization','Bearer '.$token)->getJson('/api/v1/translations?per_page=10&page=2');
        $res->assertStatus(200);
        $res->assertJsonCount(10, 'data');
    }
}
