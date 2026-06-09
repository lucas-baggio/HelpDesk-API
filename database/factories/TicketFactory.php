<?php

namespace Database\Factories;

use App\Domains\Client\Models\Client;
use App\Domains\Machine\Models\Machine;
use App\Domains\Ticket\Enums\TicketPriority;
use App\Domains\Ticket\Enums\TicketStatus;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'machine_id' => null,
            'created_by' => User::factory(),
            'resolved_by' => null,
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(TicketPriority::cases())->value,
            'status' => TicketStatus::Aberto->value,
            'resolved_at' => null,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TicketStatus::EmAndamento->value,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(function (array $attributes): array {
            $resolver = User::factory()->create();

            return [
                'status' => TicketStatus::Resolvido->value,
                'resolved_by' => $resolver->id,
                'resolved_at' => now(),
            ];
        });
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TicketStatus::Cancelado->value,
        ]);
    }

    public function withMachine(Machine $machine): static
    {
        return $this->state(fn (array $attributes): array => [
            'client_id' => $machine->client_id,
            'machine_id' => $machine->id,
        ]);
    }

    public function forClient(Client $client): static
    {
        return $this->state(fn (array $attributes): array => [
            'client_id' => $client->id,
        ]);
    }
}
