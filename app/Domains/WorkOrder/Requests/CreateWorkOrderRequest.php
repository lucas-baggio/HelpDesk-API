<?php

namespace App\Domains\WorkOrder\Requests;

use App\Domains\WorkOrder\DTOs\CreateWorkOrderData;
use App\Domains\WorkOrder\Models\WorkOrder;
use Illuminate\Foundation\Http\FormRequest;

class CreateWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', WorkOrder::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ticket_id' => ['required', 'uuid', 'exists:tickets,id'],
            'description' => ['required', 'string'],
            'service_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function toCreateWorkOrderData(): CreateWorkOrderData
    {
        return CreateWorkOrderData::fromRequest($this);
    }
}
