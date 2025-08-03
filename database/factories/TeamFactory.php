<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Team',
            'description' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that the team is for engineering.
     */
    public function engineering(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Engineering Team',
            'description' => 'Software development and engineering team',
        ]);
    }

    /**
     * Indicate that the team is for product.
     */
    public function product(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Product Team',
            'description' => 'Product management and design team',
        ]);
    }

    /**
     * Indicate that the team is for marketing.
     */
    public function marketing(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Marketing Team',
            'description' => 'Marketing and communications team',
        ]);
    }

    /**
     * Indicate that the team is for sales.
     */
    public function sales(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Sales Team',
            'description' => 'Sales and business development team',
        ]);
    }

    /**
     * Indicate that the team is for support.
     */
    public function support(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Support Team',
            'description' => 'Customer support and success team',
        ]);
    }
} 