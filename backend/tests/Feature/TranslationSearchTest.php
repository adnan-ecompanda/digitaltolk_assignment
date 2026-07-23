<?php

namespace Tests\Feature;

use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationSearchTest extends TestCase
{
    use RefreshDatabase;

    private function getToken(): string
    {
        return $this->postJson('/api/v1/token', ['email' => 'searcher@example.com'])->json('token');
    }

    public function test_search_by_key_locale_q_and_tags(): void
    {
        $t1 = Translation::factory()->create(['key' => 'welcome.message','locale' => 'en','value' => 'Welcome home']);
        $t2 = Translation::factory()->create(['key' => 'login.prompt','locale' => 'en','value' => 'Please login']);

        // attach tags
        $t1->tags()->create(['name' => 'web']);
        $t2->tags()->create(['name' => 'mobile']);

        $token = $this->getToken();

        // search by key
        $res = $this->withHeader('Authorization','Bearer '.$token)->getJson('/api/v1/translations?key=welcome.message');
        $res->assertStatus(200)->assertJsonFragment(['key' => 'welcome.message']);

        // search by q (value)
        $res = $this->withHeader('Authorization','Bearer '.$token)->getJson('/api/v1/translations?q=login');
        $res->assertStatus(200)->assertJsonFragment(['key' => 'login.prompt']);

        // search by tags
        $res = $this->withHeader('Authorization','Bearer '.$token)->getJson('/api/v1/translations?tags=web');
        $res->assertStatus(200)->assertJsonFragment(['key' => 'welcome.message']);
    }
}
