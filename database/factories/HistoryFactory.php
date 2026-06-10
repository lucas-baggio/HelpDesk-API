<?php

namespace Database\Factories;

use App\Domains\History\Enums\HistoryAction;
use App\Domains\History\Enums\HistoryEntityType;
use App\Domains\History\Models\History;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<History>
 */
class HistoryFactory extends Factory
{
    protected $model = History::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'entity_type' => HistoryEntityType::Ticket->value,
            'entity_id' => fake()->uuid(),
            'action' => HistoryAction::TicketCreated->value,
            'description' => fake()->sentence(),
            'created_at' => now(),
        ];
    }

    public function forTicket(string $ticketId): static
    {
        return $this->state(fn (array $attributes): array => [
            'entity_type' => HistoryEntityType::Ticket->value,
            'entity_id' => $ticketId,
        ]);
    }

    public function forWorkOrder(string $workOrderId): static
    {
        return $this->state(fn (array $attributes): array => [
            'entity_type' => HistoryEntityType::WorkOrder->value,
            'entity_id' => $workOrderId,
        ]);
    }
}
