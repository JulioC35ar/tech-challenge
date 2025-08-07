<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceOrder>
 */
class MaintenanceOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Technicians only
        $technician = User::where('role', 'technician')->inRandomOrder()->first();

        // Asset random
        $asset = Asset::inRandomOrder()->first();

        // States
        $statuses = ['created', 'in_progress', 'pending_approval', 'approved', 'rejected'];
        $status = $this->faker->randomElement($statuses);

        return [
            'title' => $this->faker->sentence(3),
            'asset_id' => $asset?->id ?? Asset::factory(),
            'priority' => $this->faker->randomElement(['high', 'medium', 'low']),
            'technician_id' => $technician?->id ?? User::factory(),
            'status' => $status,
            'rejection_reason' => $status === 'rejected' ? $this->faker->sentence() : null,
        ];
    }
}
