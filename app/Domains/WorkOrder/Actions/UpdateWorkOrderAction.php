<?php

namespace App\Domains\WorkOrder\Actions;

use App\Domains\WorkOrder\DTOs\UpdateWorkOrderData;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class UpdateWorkOrderAction
{
    public function execute(WorkOrder $workOrder, UpdateWorkOrderData $data): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $data): WorkOrder {
            // RN-024: finalized work order cannot be edited
            if ($workOrder->status->isFinalized()) {
                throw BusinessRuleException::withCode(
                    'WORK_ORDER_ALREADY_FINALIZED',
                    'A finalized work order cannot be edited.',
                );
            }

            $workOrder->fill($data->toPersistenceArray());
            $workOrder->save();

            return $workOrder->fresh();
        });
    }
}
