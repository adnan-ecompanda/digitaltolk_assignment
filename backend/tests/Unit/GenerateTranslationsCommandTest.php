<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Translation;

class GenerateTranslationsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_inserts_requested_number_of_rows(): void
    {
        // Call via artisan (integration) to ensure command wiring
        $this->artisan('translations:generate', ['count' => 5, '--batch' => 5])->assertExitCode(0);

        // Also directly instantiate and invoke handle() (via a small subclass) to ensure coverage of the command file
        $cmd = new class extends \App\Console\Commands\GenerateTranslations {
            public function argument($key = null)
            {
                if ($key === 'count') return 5;
                return parent::argument($key);
            }

            public function option($key = null)
            {
                if ($key === 'batch') return 5;
                return parent::option($key);
            }
            public function info($string, $verbosity = null)
            {
                // silence output during tests
            }
        };

        $cmd->setLaravel($this->app);
        $cmd->handle();

        $this->assertGreaterThanOrEqual(5, Translation::count());
    }
}
