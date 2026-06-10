<?php

namespace App\Domains\FileUpload\Models;

use App\Domains\User\Models\User;
use App\Domains\WorkOrder\Models\WorkOrder;
use Database\Factories\WorkOrderFileFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderFile extends Model
{
    /** @use HasFactory<WorkOrderFileFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'work_order_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'created_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];

    /** @return BelongsTo<WorkOrder, $this> */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    protected static function newFactory(): WorkOrderFileFactory
    {
        return WorkOrderFileFactory::new();
    }
}
