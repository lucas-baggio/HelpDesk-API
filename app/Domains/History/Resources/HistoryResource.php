<?php

namespace App\Domains\History\Resources;

use App\Shared\Http\Resources\BaseJsonResource;
use Illuminate\Http\Request;

class HistoryResource extends BaseJsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'action' => $this->action,
            'description' => $this->description,
            'created_at' => $this->created_at,
        ];
    }
}
