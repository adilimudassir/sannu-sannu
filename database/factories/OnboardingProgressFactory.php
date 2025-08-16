<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OnboardingProgress>
 */
class OnboardingProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stepKeys = [
            'profile_setup',
            'first_project',
            'team_invitation',
            'payment_setup',
            'customization',
            'documentation_review',
        ];

        $stepNames = [
            'profile_setup' => 'Complete Organization Profile',
            'first_project' => 'Create Your First Project',
            'team_invitation' => 'Invite Team Members',
            'payment_setup' => 'Set Up Payment Methods',
            'customization' => 'Customize Your Workspace',
            'documentation_review' => 'Review Platform Documentation',
        ];

        $stepKey = fake()->randomElement($stepKeys);

        return [
            'tenant_id' => Tenant::factory(),
            'step_key' => $stepKey,
            'step_name' => $stepNames[$stepKey],
            'completed' => fake()->boolean(30), // 30% chance of being completed
            'completed_at' => fake()->optional(0.3)->dateTimeBetween('-7 days', 'now'),
            'data' => fake()->optional()->randomElement([
                ['notes' => fake()->sentence()],
                ['progress' => fake()->numberBetween(0, 100)],
                ['metadata' => ['key' => fake()->word()]],
            ]),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => true,
            'completed_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => false,
            'completed_at' => null,
        ]);
    }

    public function forStep(string $stepKey, string $stepName): static
    {
        return $this->state(fn (array $attributes) => [
            'step_key' => $stepKey,
            'step_name' => $stepName,
        ]);
    }
}
