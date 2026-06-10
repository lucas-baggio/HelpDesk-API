<?php

namespace App\Domains\History\Observers;

use App\Domains\History\Actions\RecordHistoryAction;
use App\Domains\History\DTOs\RecordHistoryData;
use App\Domains\History\Enums\HistoryAction;
use App\Domains\History\Enums\HistoryEntityType;
use App\Domains\WorkOrder\Models\WorkOrder;

class WorkOrderObserver
{
    public function __construct(private readonly RecordHistoryAction $action) {}

    /** RN-030, RN-031 */
    public function created(WorkOrder $workOrder): void
    {
        $userId = auth()->id();

        if ($userId === null) {
            return;
        }

        $this->action->execute(new RecordHistoryData(
            userId: $userId,
            entityType: HistoryEntityType::WorkOrder,
            entityId: $workOrder->id,
            action: HistoryAction::WorkOrderCreated,
            description: "Work order {$workOrder->number} created.",
        ));
    }

    /** RN-032, RN-033 */
    public function updated(WorkOrder $workOrder): void
    {
        $userId = auth()->id();

        if ($userId === null) {
            return;
        }

        if ($workOrder->wasChanged('status')) {
            $old = $workOrder->getRawOriginal('status');
            $new = $workOrder->status->value;

            $this->action->execute(new RecordHistoryData(
                userId: $userId,
                entityType: HistoryEntityType::WorkOrder,
                entityId: $workOrder->id,
                action: HistoryAction::WorkOrderStatusChanged,
                description: "Work order {$workOrder->number} status changed from \"{$old}\" to \"{$new}\".",
            ));
        }

        // RN-033: service value changes are critical and must be audited
        if ($workOrder->wasChanged('service_value')) {
            $old = $workOrder->getRawOriginal('service_value') ?? 'none';
            $new = $workOrder->service_value ?? 'none';

            $this->action->execute(new RecordHistoryData(
                userId: $userId,
                entityType: HistoryEntityType::WorkOrder,
                entityId: $workOrder->id,
                action: HistoryAction::WorkOrderServiceValueUpdated,
                description: "Work order {$workOrder->number} service value changed from \"{$old}\" to \"{$new}\".",
            ));
        }
    }
}
