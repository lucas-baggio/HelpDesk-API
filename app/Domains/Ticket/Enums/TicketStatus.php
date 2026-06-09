<?php

namespace App\Domains\Ticket\Enums;

enum TicketStatus: string
{
    case Aberto = 'aberto';
    case EmAndamento = 'em_andamento';
    case Resolvido = 'resolvido';
    case Cancelado = 'cancelado';

    public function isOpen(): bool
    {
        return $this === self::Aberto;
    }

    public function isCancelled(): bool
    {
        return $this === self::Cancelado;
    }

    public function isResolved(): bool
    {
        return $this === self::Resolvido;
    }

    public function isClosed(): bool
    {
        return $this === self::Resolvido || $this === self::Cancelado;
    }
}
