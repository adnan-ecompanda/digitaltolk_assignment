<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class GenerateTranslations extends Command
{
    protected $signature = 'translations:generate {count=100000} {--batch=1000}';

    protected $description = 'Generate a large number of translation rows efficiently using batch inserts.';

    public function handle(): int
    {
        $count = (int)$this->argument('count');
        $batch = (int)$this->option('batch');

        $this->info("Generating {$count} translations in batches of {$batch}...");

        $faker = Faker::create();
        $locales = ['en','fr','es','de','it'];

        $inserted = 0;
        $chunks = intdiv($count, $batch);
        if ($count % $batch !== 0) { $chunks++; }

        for ($i = 0; $i < $chunks; $i++) {
            $rows = [];
            for ($j = 0; $j < $batch && $inserted < $count; $j++) {
                $key = 'key_'.bin2hex(random_bytes(6)).'_'.($inserted + 1);
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
                $inserted++;
            }

            DB::table('translations')->insert($rows);
            $this->info("Inserted {$inserted} / {$count}");
        }

        $this->info('Done.');

        return 0;
    }
}
