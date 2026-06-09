<?php

namespace App\Domains\Ticket\Requests;

use App\Domains\Ticket\DTOs\UpdateTicketData;
use App\Domains\Ticket\Enums\TicketPriority;
use App\Domains\Ticket\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Ticket $ticket */
        $ticket = $this->route('ticket');

        return $this->user()?->can('update', $ticket) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'priority' => ['sometimes', 'required', Rule::enum(TicketPriority::class)],
            'machine_id' => ['sometimes', 'nullable', 'uuid', 'exists:machines,id'],
        ];
    }

    public function toUpdateTicketData(): UpdateTicketData
    {
        return UpdateTicketData::fromRequest($this);
    }
}
