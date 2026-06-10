<?php

namespace App\Domains\WorkOrder\Actions;

use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class FinalizeWorkOrderAction
{
    public function execute(WorkOrder $workOrder): WorkOrder
    {
        return DB::transaction(function () use ($workOrder): WorkOrder {
            // RN-024: finalized work order cannot return to open state
            if ($workOrder->status->isFinalized()) {
                throw BusinessRuleException::withCode(
                    'WORK_ORDER_ALREADY_FINALIZED',
                    'This work order is already finalized.',
                );
            }

            // RN-023: must be in execution before finalizing
            if ($workOrder->status->isOpen()) {
                throw BusinessRuleException::withCode(
                    'WORK_ORDER_NOT_IN_EXECUTION',
                    'A work order must be in execution before it can be finalized.',
                );
            }

            $workOrder->update(['status' => WorkOrderStatus::Finalizada->value]);

            return $workOrder->fresh();
        });
    }
}
