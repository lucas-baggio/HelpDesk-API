<?php

namespace App\Domains\Client\Resources;

use App\Shared\Http\Resources\BaseJsonResource;
use Illuminate\Http\Request;

class ClientResource extends BaseJsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'cpf_cnpj' => $this->cpf_cnpj,
            'phone' => $this->phone,
            'address' => [
                'street' => $this->street,
                'number' => $this->number,
                'complement' => $this->complement,
                'district' => $this->district,
                'city' => $this->city,
                'state' => $this->state,
                'zip_code' => $this->zip_code,
            ],
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
