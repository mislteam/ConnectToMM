<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Joytel>
 */
class JoytelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_name' => $this->faker->word(),
            'product_name' => $this->faker->word(),
            'usage_location' => $this->faker->country(),
            'supplier' => $this->faker->name(),
            'product_type' => $this->faker->word(),
            'plan' => $this->faker->sentence(),
            'expired_date' => $this->faker->date(),
        ];
    }
}
