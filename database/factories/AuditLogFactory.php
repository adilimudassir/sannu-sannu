<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\TenantApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $auditableTypes = [
            Tenant::class,
            TenantApplication::class,
            User::class,
        ];

        $auditableType = $this->faker->randomElement($auditableTypes);
        $actions = [
            'created',
            'updated',
            'deleted',
            'approved',
            'rejected',
            'suspended',
            'reactivated',
        ];

        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement($actions),
            'auditable_type' => $auditableType,
            'auditable_id' => fake()->numberBetween(1, 100),
            'old_values' => fake()->optional()->randomElement([
                ['status' => 'pending'],
                ['name' => fake()->company()],
                ['is_active' => false],
            ]),
            'new_values' => fake()->optional()->randomElement([
                ['status' => 'approved'],
                ['name' => fake()->company()],
                ['is_active' => true],
            ]),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'context' => fake()->optional()->randomElement([
                ['reason' => fake()->sentence()],
                ['notes' => fake()->paragraph()],
                ['metadata' => ['key' => fake()->word()]],
            ]),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function forModel(string $modelClass, int $modelId): static
    {
        return $this->state(fn (array $attributes) => [
            'auditable_type' => $modelClass,
            'auditable_id' => $modelId,
        ]);
    }

    public function withAction(string $action): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => $action,
        ]);
    }

    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
