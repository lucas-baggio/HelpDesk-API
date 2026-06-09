<?php

namespace App\Domains\Ticket\Enums;

enum TicketPriority: string
{
    case Baixa = 'baixa';
    case Media = 'media';
    case Alta = 'alta';
}
