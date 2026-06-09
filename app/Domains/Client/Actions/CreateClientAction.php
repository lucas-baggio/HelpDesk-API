<?php

namespace App\Domains\Client\Actions;

use App\Domains\Client\DTOs\CreateClientData;
use App\Domains\Client\Models\Client;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class CreateClientAction
{
    public function execute(CreateClientData $data): Client
    {
        return DB::transaction(function () use ($data): Client {
            if (Client::query()->where('cpf_cnpj', $data->cpf_cnpj)->exists()) {
                throw BusinessRuleException::withCode(
                    'CLIENT_CPF_CNPJ_ALREADY_EXISTS',
                    'A client with this CPF/CNPJ already exists.',
                );
            }

            return Client::query()->create([
                'name' => $data->name,
                'email' => $data->email,
                'cpf_cnpj' => $data->cpf_cnpj,
                'phone' => $data->phone,
                'street' => $data->street,
                'number' => $data->number,
                'state' => $data->state,
                'district' => $data->district,
                'city' => $data->city,
                'zip_code' => $data->zip_code,
                'complement' => $data->complement,
                'is_active' => true,
            ]);
        });
    }
}
