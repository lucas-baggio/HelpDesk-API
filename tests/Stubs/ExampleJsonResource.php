<?php

namespace Tests\Stubs;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExampleJsonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'name' => $this->resource['name'],
        ];
    }
}
