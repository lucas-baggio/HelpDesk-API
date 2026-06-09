<?php

namespace App\Domains\Ticket\Resources;

use App\Shared\Http\Resources\BaseJsonResource;
use Illuminate\Http\Request;

class TicketResource extends BaseJsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'machine_id' => $this->machine_id,
            'created_by' => $this->created_by,
            'resolved_by' => $this->resolved_by,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority->value,
            'status' => $this->status->value,
            'resolved_at' => $this->resolved_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
