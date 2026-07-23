<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Efficient translations generator
Artisan::command('translations:generate {count=100000} {--batch=1000}', function ($count = 100000) {
    $batch = (int) $this->option('batch');
    $count = (int) $count;
    $this->info("Generating {$count} translations in batches of {$batch}...");

    $faker = Faker\Factory::create();
    $locales = ['en','fr','es','de','it'];

    $inserted = 0;
    while ($inserted < $count) {
        $rows = [];
        $toCreate = min($batch, $count - $inserted);
        for ($i = 0; $i < $toCreate; $i++) {
            $key = 'key_'.bin2hex(random_bytes(6)).'_'.($inserted + $i + 1);
            $locale = $locales[array_rand($locales)];
            $rows[] = [
                'key' => $key,
                'locale' => $locale,
                'value' => $faker->sentence(6),
                'tags' => json_encode($faker->randomElements(['web','mobile','desktop','auth','email'], $faker->numberBetween(0,3))),
                'context' => $faker->randomElement(['general','validation','notifications','errors']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        \Illuminate\Support\Facades\DB::table('translations')->insert($rows);
        $inserted += $toCreate;
        $this->info("Inserted {$inserted} / {$count}");
    }

    $this->info('Done.');
})->describe('Generate many translation records efficiently');
