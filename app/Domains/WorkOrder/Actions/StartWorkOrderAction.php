<?php

namespace App\Domains\WorkOrder\Actions;

use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class StartWorkOrderAction
{
    public function execute(WorkOrder $workOrder): WorkOrder
    {
        return DB::transaction(function () use ($workOrder): WorkOrder {
            // RN-023: only open work orders can move to in execution
            if (! $workOrder->status->isOpen()) {
                throw BusinessRuleException::withCode(
                    'WORK_ORDER_CANNOT_START',
                    'Only open work orders can be moved to in execution.',
                );
            }

            $workOrder->update(['status' => WorkOrderStatus::EmExecucao->value]);

            return $workOrder->fresh();
        });
    }
}
