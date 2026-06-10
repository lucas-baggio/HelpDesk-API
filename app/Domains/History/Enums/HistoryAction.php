<?php

namespace App\Domains\History\Enums;

/** Canonical action identifiers used across all observers. */
enum HistoryAction: string
{
    case TicketCreated = 'ticket.created';
    case TicketStatusChanged = 'ticket.status_changed';

    case WorkOrderCreated = 'work_order.created';
    case WorkOrderStatusChanged = 'work_order.status_changed';
    case WorkOrderServiceValueUpdated = 'work_order.service_value_updated';

    case FileUploaded = 'file.uploaded';
    case FileDeleted = 'file.deleted';
}
