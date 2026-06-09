<?php

namespace App\Domains\Client\Requests;

use App\Domains\Client\DTOs\UpdateClientData;
use App\Domains\Client\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Client $client */
        $client = $this->route('client');

        return $this->user()?->can('update', $client) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Client $client */
        $client = $this->route('client');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->ignore($client),
            ],
            'cpf_cnpj' => [
                'sometimes',
                'required',
                'string',
                'max:18',
                Rule::unique('clients', 'cpf_cnpj')->ignore($client),
            ],
            'phone' => ['sometimes', 'required', 'string', 'max:20'],
            'street' => ['sometimes', 'required', 'string', 'max:255'],
            'number' => ['sometimes', 'required', 'string', 'max:20'],
            'state' => ['sometimes', 'required', 'string', 'max:2'],
            'district' => ['sometimes', 'required', 'string', 'max:255'],
            'city' => ['sometimes', 'required', 'string', 'max:255'],
            'zip_code' => ['sometimes', 'required', 'string', 'max:10'],
            'complement' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toUpdateClientData(): UpdateClientData
    {
        return UpdateClientData::fromRequest($this);
    }
}
