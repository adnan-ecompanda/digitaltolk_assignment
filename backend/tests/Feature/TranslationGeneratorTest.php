<?php

namespace Tests\Feature;

use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_translations_generate_command_inserts_rows(): void
    {
        $this->artisan('translations:generate', ['count' => 100])->assertExitCode(0);

        $this->assertGreaterThanOrEqual(100, Translation::count());
    }
}
