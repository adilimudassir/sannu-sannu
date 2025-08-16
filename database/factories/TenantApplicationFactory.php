<?php

namespace Database\Factories;

use App\Enums\TenantApplicationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TenantApplication>
 */
class TenantApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference_number' => 'TA-'.now()->format('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'organization_name' => fake()->company(),
            'business_description' => fake()->paragraph(3),
            'industry_type' => fake()->randomElement(['technology', 'healthcare', 'finance', 'education', 'retail', 'manufacturing', 'consulting', 'other']),
            'contact_person_name' => fake()->name(),
            'contact_person_email' => fake()->unique()->safeEmail(),
            'contact_person_phone' => fake()->phoneNumber(),
            'business_registration_number' => fake()->optional()->numerify('REG-########'),
            'website_url' => fake()->optional()->url(),
            'status' => TenantApplicationStatus::PENDING,
            'submitted_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'reviewed_at' => null,
            'reviewer_id' => null,
            'rejection_reason' => null,
            'notes' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TenantApplicationStatus::APPROVED,
            'reviewed_at' => fake()->dateTimeBetween($attributes['submitted_at'], 'now'),
            'reviewer_id' => User::factory(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TenantApplicationStatus::REJECTED,
            'reviewed_at' => fake()->dateTimeBetween($attributes['submitted_at'], 'now'),
            'reviewer_id' => User::factory(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TenantApplicationStatus::PENDING,
            'reviewed_at' => null,
            'reviewer_id' => null,
        ]);
    }
}
