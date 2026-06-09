<?php

namespace App\Domains\Client\Models;

use App\Domains\Machine\Models\Machine;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'cpf_cnpj',
        'phone',
        'street',
        'number',
        'state',
        'district',
        'city',
        'zip_code',
        'complement',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * RN-005: a client may have multiple machines.
     *
     * @return HasMany<Machine, $this>
     */
    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }

    protected static function newFactory(): ClientFactory
    {
        return ClientFactory::new();
    }
}
