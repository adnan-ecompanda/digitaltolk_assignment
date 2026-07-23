<?php

namespace Database\Factories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition()
    {
        return [
            'key' => $this->faker->unique()->bothify('key_####_##'),
            'locale' => $this->faker->randomElement(['en','fr','es','de','it']),
            'value' => $this->faker->sentence(6),
            'tags' => [],
            'context' => $this->faker->randomElement(['general','validation','notifications','errors']),
        ];
    }
}
