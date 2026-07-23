<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_endpoint_performance(): void
    {
        // Seed a moderate number of translations for the perf test.
        $count = (int) env('PERF_TEST_COUNT', 5000);
        $this->artisan('translations:generate', ['count' => $count, '--batch' => 1000]);

        $start = microtime(true);
        $res = $this->get('/api/v1/translations/export?locale=en');
        $duration = microtime(true) - $start;

        $res->assertStatus(200);

        // Assert under 0.5 seconds (500ms) — adjust for CI if necessary.
        $this->assertLessThan(0.5, $duration, "Export endpoint took {$duration} seconds");
    }
}
