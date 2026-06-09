<?php

namespace App\Domains\Machine\Requests;

use App\Domains\Machine\DTOs\UpdateMachineData;
use App\Domains\Machine\Models\Machine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMachineRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Machine $machine */
        $machine = $this->route('machine');

        return $this->user()?->can('update', $machine) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Machine $machine */
        $machine = $this->route('machine');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'model' => ['sometimes', 'nullable', 'string', 'max:255'],
            'serial_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('machines', 'serial_number')
                    ->where(fn ($q) => $q->where('client_id', $machine->client_id))
                    ->ignore($machine),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toUpdateMachineData(): UpdateMachineData
    {
        return UpdateMachineData::fromRequest($this);
    }
}
