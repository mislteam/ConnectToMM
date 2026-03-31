<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CnyCurrency>
 */
class CnyCurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mini_amount' => $this->faker->numberBetween(1, 9),
            'max_amount' => $this->faker->numberBetween(1, 9),
            'mmk' => $this->faker->numberBetween(500, 999),
        ];
    }
}
