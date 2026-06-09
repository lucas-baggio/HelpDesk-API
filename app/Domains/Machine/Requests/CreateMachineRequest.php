<?php

namespace App\Domains\Machine\Requests;

use App\Domains\Machine\DTOs\CreateMachineData;
use App\Domains\Machine\Models\Machine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMachineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Machine::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $clientId = $this->input('client_id');

        return [
            'client_id' => ['required', 'uuid', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('machines')->where(
                    fn ($query) => $query->where('client_id', $clientId)
                ),
            ],
        ];
    }

    public function toCreateMachineData(): CreateMachineData
    {
        return CreateMachineData::fromRequest($this);
    }
}
