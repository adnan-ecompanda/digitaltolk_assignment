<?php

namespace Tests\Unit;

use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_can_attach_translation(): void
    {
        $t = Translation::factory()->create();
        $tag = Tag::create(['name' => 'auth']);
        $tag->translations()->attach($t->id);

        $this->assertTrue($tag->translations()->exists());
    }
}
