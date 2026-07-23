<?php

namespace Tests\Unit;

use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_translation_can_have_tags(): void
    {
        $t = Translation::factory()->create(['key' => 'test.key']);
        $tag = Tag::create(['name' => 'mobile']);

        $t->tags()->attach($tag->id);

        $this->assertTrue($t->tags()->exists());
        $this->assertEquals('mobile', $t->tags()->first()->name);
    }

    public function test_translation_casts_tags_array(): void
    {
        $t = Translation::factory()->make(['tags' => ['web','mobile']]);
        $this->assertIsArray($t->tags);
    }
}
