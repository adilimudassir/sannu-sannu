<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate, '+6 months');

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->sentence(3),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->paragraph(),
            'visibility' => $this->faker->randomElement(ProjectVisibility::cases()),
            'requires_approval' => $this->faker->boolean(20),
            'max_contributors' => $this->faker->optional(0.3)->numberBetween(10, 100),
            'total_amount' => $this->faker->randomFloat(2, 100, 10000),
            'minimum_contribution' => $this->faker->optional(0.5)->randomFloat(2, 10, 100),
            'payment_options' => $this->faker->randomElements(['full', 'installments'], $this->faker->numberBetween(1, 2)),
            'installment_frequency' => $this->faker->randomElement(['monthly', 'quarterly', 'custom']),
            'custom_installment_months' => $this->faker->optional(0.2)->numberBetween(2, 12),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'registration_deadline' => $this->faker->optional(0.3)->dateTimeBetween('now', $startDate),
            'created_by' => User::factory(),
            'managed_by' => $this->faker->optional(0.3)->randomElements([1, 2, 3], $this->faker->numberBetween(1, 3)),
            'status' => $this->faker->randomElement(ProjectStatus::cases()),
            'settings' => $this->faker->optional(0.2)->randomElements([
                'allow_anonymous_contributions' => $this->faker->boolean(),
                'send_updates' => $this->faker->boolean(),
                'custom_fields' => $this->faker->words(3),
            ]),
        ];
    }

    /**
     * Indicate that the project is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::DRAFT,
        ]);
    }

    /**
     * Indicate that the project is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::ACTIVE,
        ]);
    }

    /**
     * Indicate that the project is paused.
     */
    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::PAUSED,
        ]);
    }

    /**
     * Indicate that the project is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::COMPLETED,
        ]);
    }

    /**
     * Indicate that the project is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::CANCELLED,
        ]);
    }

    /**
     * Indicate that the project is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => ProjectVisibility::PUBLIC,
        ]);
    }

    /**
     * Indicate that the project is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => ProjectVisibility::PRIVATE,
        ]);
    }

    /**
     * Indicate that the project is invite only.
     */
    public function inviteOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => ProjectVisibility::INVITE_ONLY,
        ]);
    }

    /**
     * Indicate that the project has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $this->faker->dateTimeBetween('-6 months', '-2 months'),
            'end_date' => $this->faker->dateTimeBetween('-2 months', '-1 day'),
        ]);
    }

    /**
     * Indicate that the project is upcoming.
     */
    public function upcoming(): static
    {
        $startDate = $this->faker->dateTimeBetween('+1 week', '+2 months');
        
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $this->faker->dateTimeBetween($startDate, '+6 months'),
        ]);
    }
}