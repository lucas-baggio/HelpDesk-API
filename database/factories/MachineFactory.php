<?php

namespace Database\Factories;

use App\Domains\Client\Models\Client;
use App\Domains\Machine\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Machine>
 */
class MachineFactory extends Factory
{
    protected $model = Machine::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'name' => fake()->words(2, true),
            'model' => fake()->bothify('Model-##??'),
            'serial_number' => fake()->unique()->numerify('SN-#####'),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function forClient(Client $client): static
    {
        return $this->state(fn (array $attributes): array => [
            'client_id' => $client->id,
        ]);
    }
}
