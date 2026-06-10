<?php

namespace App\Domains\History\Observers;

use App\Domains\FileUpload\Models\WorkOrderFile;
use App\Domains\History\Actions\RecordHistoryAction;
use App\Domains\History\DTOs\RecordHistoryData;
use App\Domains\History\Enums\HistoryAction;
use App\Domains\History\Enums\HistoryEntityType;

class WorkOrderFileObserver
{
    public function __construct(private readonly RecordHistoryAction $action) {}

    /** RN-030: file uploads generate history automatically. */
    public function created(WorkOrderFile $file): void
    {
        $userId = auth()->id();

        if ($userId === null) {
            return;
        }

        $this->action->execute(new RecordHistoryData(
            userId: $userId,
            entityType: HistoryEntityType::WorkOrderFile,
            entityId: $file->id,
            action: HistoryAction::FileUploaded,
            description: "File \"{$file->file_name}\" uploaded to work order.",
        ));
    }

    /** RN-027, RN-030: file deletions are also recorded. */
    public function deleted(WorkOrderFile $file): void
    {
        $userId = auth()->id();

        if ($userId === null) {
            return;
        }

        $this->action->execute(new RecordHistoryData(
            userId: $userId,
            entityType: HistoryEntityType::WorkOrderFile,
            entityId: $file->id,
            action: HistoryAction::FileDeleted,
            description: "File \"{$file->file_name}\" deleted from work order.",
        ));
    }
}
