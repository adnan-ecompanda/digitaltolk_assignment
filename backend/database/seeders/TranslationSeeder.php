<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    public function run(): void
    {
        // Create a reasonably large dataset for performance testing.
        // Be careful running 100k+ locally; tune as needed.
        $count = env('TRANSLATION_SEED_COUNT', 100000);
        $faker = \Faker\Factory::create();
        $tagNames = ['web','mobile','desktop','auth','email'];

        for ($i = 0; $i < (int)$count; $i++) {
            $t = Translation::factory()->create();
            // attach 0-3 tags
            $pick = $faker->randomElements($tagNames, $faker->numberBetween(0,3));
            if (! empty($pick)) {
                $tagIds = [];
                foreach ($pick as $name) {
                    $tag = \App\Models\Tag::firstOrCreate(['name' => $name]);
                    $tagIds[] = $tag->id;
                }
                $t->tags()->attach($tagIds);
            }
        }
    }
}
