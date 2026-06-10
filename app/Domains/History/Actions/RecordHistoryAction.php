<?php

namespace App\Domains\History\Actions;

use App\Domains\History\DTOs\RecordHistoryData;
use App\Domains\History\Models\History;

class RecordHistoryAction
{
    public function execute(RecordHistoryData $data): History
    {
        return History::query()->create([
            'user_id' => $data->userId,
            'entity_type' => $data->entityType->value,
            'entity_id' => $data->entityId,
            'action' => $data->action->value,
            'description' => $data->description,
            'created_at' => now(),
        ]);
    }
}
