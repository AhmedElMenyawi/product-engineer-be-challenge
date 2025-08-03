<?php

namespace Database\Factories;

use App\Models\TaskHistory;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaskHistory>
 */
class TaskHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $actions = ['created', 'updated', 'deleted'];
        $fields = ['title', 'description', 'status', 'priority', 'assigned_to', 'team_id', 'start_time', 'end_time'];
        
        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement($actions),
            'field_changed' => $this->faker->optional()->randomElement($fields),
            'old_value' => $this->faker->optional()->text(50),
            'new_value' => $this->faker->optional()->text(50),
            'changed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Indicate that the history is for task creation.
     */
    public function created(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'created',
            'field_changed' => null,
            'old_value' => null,
            'new_value' => null,
        ]);
    }

    /**
     * Indicate that the history is for task update.
     */
    public function updated(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'updated',
        ]);
    }

    /**
     * Indicate that the history is for task deletion.
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'deleted',
            'field_changed' => null,
            'old_value' => null,
            'new_value' => null,
        ]);
    }

    /**
     * Indicate that the history is for title change.
     */
    public function titleChange(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'updated',
            'field_changed' => 'title',
            'old_value' => $this->faker->sentence(3),
            'new_value' => $this->faker->sentence(3),
        ]);
    }

    /**
     * Indicate that the history is for status change.
     */
    public function statusChange(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'updated',
            'field_changed' => 'status',
            'old_value' => $this->faker->randomElement(['pending', 'in_progress']),
            'new_value' => $this->faker->randomElement(['in_progress', 'completed']),
        ]);
    }

    /**
     * Indicate that the history is for priority change.
     */
    public function priorityChange(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'updated',
            'field_changed' => 'priority',
            'old_value' => $this->faker->randomElement(['low', 'medium']),
            'new_value' => $this->faker->randomElement(['medium', 'high']),
        ]);
    }

    /**
     * Indicate that the history is for assignment change.
     */
    public function assignmentChange(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'updated',
            'field_changed' => 'assigned_to',
            'old_value' => $this->faker->numberBetween(1, 10),
            'new_value' => $this->faker->numberBetween(1, 10),
        ]);
    }
} 