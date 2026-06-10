<?php

namespace App\Domains\WorkOrder\Enums;

enum WorkOrderStatus: string
{
    case Aberta = 'aberta';
    case EmExecucao = 'em_execucao';
    case Finalizada = 'finalizada';

    public function isOpen(): bool
    {
        return $this === self::Aberta;
    }

    public function isFinalized(): bool
    {
        return $this === self::Finalizada;
    }
}
