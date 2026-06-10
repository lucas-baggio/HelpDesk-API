<?php

namespace App\Domains\WorkOrder\Models;

use App\Domains\FileUpload\Models\WorkOrderFile;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use Database\Factories\WorkOrderFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    /** @use HasFactory<WorkOrderFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'ticket_id',
        'number',
        'description',
        'service_value',
        'status',
    ];

    protected $casts = [
        'status' => WorkOrderStatus::class,
        'service_value' => 'decimal:2',
    ];

    /** @return BelongsTo<Ticket, $this> */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /** @return HasMany<WorkOrderFile, $this> */
    public function files(): HasMany
    {
        return $this->hasMany(WorkOrderFile::class);
    }

    protected static function newFactory(): WorkOrderFactory
    {
        return WorkOrderFactory::new();
    }
}
