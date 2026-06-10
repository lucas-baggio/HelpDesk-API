<?php

namespace Database\Factories;

use App\Domains\Ticket\Models\Ticket;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    private static int $counter = 0;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        self::$counter++;

        return [
            'ticket_id' => Ticket::factory(),
            'number' => 'OS-' . str_pad((string) self::$counter, 5, '0', STR_PAD_LEFT),
            'description' => fake()->paragraph(),
            'service_value' => fake()->randomFloat(2, 50, 2000),
            'status' => WorkOrderStatus::Aberta->value,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => WorkOrderStatus::EmExecucao->value,
        ]);
    }

    public function finalized(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => WorkOrderStatus::Finalizada->value,
        ]);
    }

    public function forTicket(Ticket $ticket): static
    {
        return $this->state(fn (array $attributes): array => [
            'ticket_id' => $ticket->id,
        ]);
    }
}
