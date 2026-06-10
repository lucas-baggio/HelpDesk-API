<?php

namespace App\Domains\History\DTOs;

use App\Domains\History\Enums\HistoryAction;
use App\Domains\History\Enums\HistoryEntityType;

readonly class RecordHistoryData
{
    public function __construct(
        public string $userId,
        public HistoryEntityType $entityType,
        public string $entityId,
        public HistoryAction $action,
        public string $description,
    ) {}
}
