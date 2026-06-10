<?php

namespace App\Domains\WorkOrder\Actions;

use App\Domains\WorkOrder\Models\WorkOrder;

class ShowWorkOrderAction
{
    public function execute(WorkOrder $workOrder): WorkOrder
    {
        return $workOrder;
    }
}
