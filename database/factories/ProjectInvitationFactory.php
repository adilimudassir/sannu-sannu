<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use App\Models\ProjectInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectInvitation>
 */
class ProjectInvitationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProjectInvitation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'email' => fake()->unique()->safeEmail(),
            'invited_by' => User::factory(),
            'token' => fake()->uuid(),
            'status' => fake()->randomElement(['pending', 'accepted', 'declined']),
            'expires_at' => fake()->dateTimeBetween('now', '+1 month'),
            'accepted_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Indicate that the invitation is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'accepted_at' => null,
        ]);
    }

    /**
     * Indicate that the invitation is accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'accepted_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the invitation is declined.
     */
    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'declined',
            'accepted_at' => null,
        ]);
    }
}