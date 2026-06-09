<?php

namespace App\Domains\Machine\Resources;

use App\Shared\Http\Resources\BaseJsonResource;
use Illuminate\Http\Request;

class MachineResource extends BaseJsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'name' => $this->name,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
