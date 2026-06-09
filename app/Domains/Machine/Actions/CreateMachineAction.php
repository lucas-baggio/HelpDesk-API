<?php

namespace App\Domains\Machine\Actions;

use App\Domains\Machine\DTOs\CreateMachineData;
use App\Domains\Machine\Models\Machine;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class CreateMachineAction
{
    public function execute(CreateMachineData $data): Machine
    {
        return DB::transaction(function () use ($data): Machine {
            // RN-011: serial_number must be unique per client
            if (
                $data->serialNumber !== null &&
                Machine::query()
                    ->where('client_id', $data->clientId)
                    ->where('serial_number', $data->serialNumber)
                    ->exists()
            ) {
                throw BusinessRuleException::withCode(
                    'MACHINE_SERIAL_NUMBER_ALREADY_EXISTS',
                    'A machine with this serial number already exists for this client.',
                );
            }

            return Machine::query()->create([
                'client_id' => $data->clientId,
                'name' => $data->name,
                'model' => $data->model,
                'serial_number' => $data->serialNumber,
                'is_active' => true,
            ]);
        });
    }
}
