<?php

namespace App\Domains\Ticket\Actions;

use App\Domains\Ticket\Models\Ticket;

class ShowTicketAction
{
    public function execute(Ticket $ticket): Ticket
    {
        return $ticket;
    }
}
