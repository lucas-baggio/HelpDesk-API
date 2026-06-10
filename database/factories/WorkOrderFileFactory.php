<?php

namespace Database\Factories;

use App\Domains\FileUpload\Models\WorkOrderFile;
use App\Domains\User\Models\User;
use App\Domains\WorkOrder\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrderFile>
 */
class WorkOrderFileFactory extends Factory
{
    protected $model = WorkOrderFile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->word() . '.pdf';

        return [
            'work_order_id' => WorkOrder::factory(),
            'uploaded_by' => User::factory(),
            'file_name' => $name,
            'file_path' => 'work-order-files/' . fake()->uuid() . '/' . $name,
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(10000, 5000000),
            'created_at' => now(),
        ];
    }

    public function image(): static
    {
        return $this->state(function (array $attributes): array {
            $name = fake()->word() . '.jpg';

            return [
                'file_name' => $name,
                'file_path' => 'work-order-files/' . fake()->uuid() . '/' . $name,
                'mime_type' => 'image/jpeg',
            ];
        });
    }

    public function forWorkOrder(WorkOrder $workOrder): static
    {
        return $this->state(fn (array $attributes): array => [
            'work_order_id' => $workOrder->id,
        ]);
    }
}
