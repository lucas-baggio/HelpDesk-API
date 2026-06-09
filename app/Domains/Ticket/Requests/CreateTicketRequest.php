<?php

namespace App\Domains\Ticket\Requests;

use App\Domains\Ticket\DTOs\CreateTicketData;
use App\Domains\Ticket\Enums\TicketPriority;
use App\Domains\Ticket\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Ticket::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'uuid', 'exists:clients,id'],
            'machine_id' => ['nullable', 'uuid', 'exists:machines,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
        ];
    }

    public function toCreateTicketData(): CreateTicketData
    {
        return CreateTicketData::fromRequest($this);
    }
}
