<?php

namespace App\Domains\Machine\Actions;

use App\Domains\Machine\Models\Machine;
use Illuminate\Support\Facades\DB;

class DeactivateMachineAction
{
    public function execute(Machine $machine): Machine
    {
        return DB::transaction(function () use ($machine): Machine {
            $machine->update(['is_active' => false]);

            return $machine->fresh();
        });
    }
}
