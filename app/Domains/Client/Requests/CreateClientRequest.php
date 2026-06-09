<?php

namespace App\Domains\Client\Requests;

use App\Domains\Client\DTOs\CreateClientData;
use App\Domains\Client\Models\Client;
use Illuminate\Foundation\Http\FormRequest;

class CreateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Client::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:clients,email'],
            'cpf_cnpj' => ['required', 'string', 'max:18', 'unique:clients,cpf_cnpj'],
            'phone' => ['required', 'string', 'max:20'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:20'],
            'state' => ['required', 'string', 'max:2'],
            'district' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:10'],
            'complement' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toCreateClientData(): CreateClientData
    {
        return CreateClientData::fromRequest($this);
    }
}
