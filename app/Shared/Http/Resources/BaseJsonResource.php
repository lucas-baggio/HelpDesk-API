<?php

namespace App\Shared\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseJsonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(Request $request): array;
}
