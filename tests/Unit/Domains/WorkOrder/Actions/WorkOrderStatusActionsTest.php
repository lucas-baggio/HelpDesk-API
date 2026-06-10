<?php

namespace Tests\Unit\Domains\WorkOrder\Actions;

use App\Domains\WorkOrder\Actions\FinalizeWorkOrderAction;
use App\Domains\WorkOrder\Actions\StartWorkOrderAction;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderStatusActionsTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // StartWorkOrderAction
    // -------------------------------------------------------------------------

    public function test_start_moves_open_work_order_to_in_execution(): void
    {
        $workOrder = WorkOrder::factory()->create();

        $updated = (new StartWorkOrderAction())->execute($workOrder);

        $this->assertSame(WorkOrderStatus::EmExecucao, $updated->status);
    }

    public function test_start_throws_when_work_order_is_not_open(): void
    {
        $workOrder = WorkOrder::factory()->inProgress()->create();

        $this->expectException(BusinessRuleException::class);

        (new StartWorkOrderAction())->execute($workOrder);
    }

    // -------------------------------------------------------------------------
    // FinalizeWorkOrderAction
    // -------------------------------------------------------------------------

    public function test_finalize_moves_in_execution_work_order_to_finalized(): void
    {
        $workOrder = WorkOrder::factory()->inProgress()->create();

        $updated = (new FinalizeWorkOrderAction())->execute($workOrder);

        $this->assertSame(WorkOrderStatus::Finalizada, $updated->status);
    }

    public function test_finalize_throws_when_work_order_is_open(): void
    {
        $workOrder = WorkOrder::factory()->create();

        $this->expectException(BusinessRuleException::class);

        (new FinalizeWorkOrderAction())->execute($workOrder);
    }

    public function test_finalize_throws_when_work_order_is_already_finalized(): void
    {
        $workOrder = WorkOrder::factory()->finalized()->create();

        $this->expectException(BusinessRuleException::class);

        (new FinalizeWorkOrderAction())->execute($workOrder);
    }

    // RN-024: finalized cannot return to open
    public function test_start_throws_when_work_order_is_finalized(): void
    {
        $workOrder = WorkOrder::factory()->finalized()->create();

        $this->expectException(BusinessRuleException::class);

        (new StartWorkOrderAction())->execute($workOrder);
    }
}
