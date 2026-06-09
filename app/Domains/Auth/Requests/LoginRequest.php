<?php

namespace App\Domains\Auth\Requests;

use App\Domains\Auth\DTOs\LoginData;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function toLoginData(): LoginData
    {
        return LoginData::fromRequest($this);
    }
}
