<?php

namespace App\Domains\WorkOrder\Requests;

use App\Domains\WorkOrder\DTOs\UpdateWorkOrderData;
use App\Domains\WorkOrder\Models\WorkOrder;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var WorkOrder $workOrder */
        $workOrder = $this->route('work_order');

        return $this->user()?->can('update', $workOrder) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'description' => ['sometimes', 'required', 'string'],
            'service_value' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }

    public function toUpdateWorkOrderData(): UpdateWorkOrderData
    {
        return UpdateWorkOrderData::fromRequest($this);
    }
}
