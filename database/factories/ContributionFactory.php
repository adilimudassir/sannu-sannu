<?php

namespace Database\Factories;

use App\Models\Contribution;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contribution>
 */
class ContributionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contribution::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalCommitted = $this->faker->randomFloat(2, 10, 500);
        
        return [
            'tenant_id' => 1, // Will be overridden in tests
            'project_id' => 1, // Will be overridden in tests
            'user_id' => 1, // Will be overridden in tests
            'total_committed' => $totalCommitted,
            'payment_type' => $this->faker->randomElement(['full', 'installments']),
            'installment_amount' => $this->faker->optional(0.5)->randomFloat(2, 10, 100),
            'installment_frequency' => $this->faker->randomElement(['monthly', 'quarterly']),
            'total_installments' => $this->faker->optional(0.5)->numberBetween(2, 12),
            'arrears_amount' => 0,
            'arrears_paid' => 0,
            'total_paid' => $this->faker->randomFloat(2, 0, $totalCommitted),
            'next_payment_due' => $this->faker->optional(0.6)->dateTimeBetween('now', '+3 months'),
            'status' => $this->faker->randomElement(['active', 'completed', 'cancelled']),
            'joined_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'approval_status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'approved_by' => null,
            'approved_at' => $this->faker->optional(0.7)->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Indicate that the contribution is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Indicate that the contribution is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'completed',
        ]);
    }

    /**
     * Indicate that the contribution failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'failed',
        ]);
    }

    /**
     * Indicate that the contribution was refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'refunded',
        ]);
    }

    /**
     * Indicate that the contribution has a specific amount.
     */
    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'total_committed' => $amount,
            'total_paid' => $amount,
        ]);
    }
}