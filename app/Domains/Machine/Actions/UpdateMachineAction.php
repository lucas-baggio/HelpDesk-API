<?php

namespace App\Domains\Machine\Actions;

use App\Domains\Machine\DTOs\UpdateMachineData;
use App\Domains\Machine\Models\Machine;
use Illuminate\Support\Facades\DB;

class UpdateMachineAction
{
    public function execute(Machine $machine, UpdateMachineData $data): Machine
    {
        return DB::transaction(function () use ($machine, $data): Machine {
            $machine->fill($data->toPersistenceArray());
            $machine->save();

            return $machine->fresh();
        });
    }
}
