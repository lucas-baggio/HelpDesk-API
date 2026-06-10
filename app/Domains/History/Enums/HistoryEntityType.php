<?php

namespace App\Domains\History\Enums;

/** Canonical entity type identifiers used in the history table. */
enum HistoryEntityType: string
{
    case Ticket = 'ticket';
    case WorkOrder = 'work_order';
    case WorkOrderFile = 'work_order_file';
}
