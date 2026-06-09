<?php

namespace Database\Factories;

use App\Domains\Client\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'cpf_cnpj' => fake()->unique()->numerify('##.###.###/####-##'),
            'phone' => fake()->phoneNumber(),
            'street' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'state' => fake()->stateAbbr(),
            'district' => fake()->city(),
            'city' => fake()->city(),
            'zip_code' => fake()->postcode(),
            'complement' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
