<?php

namespace App\Domains\Machine\Actions;

use App\Domains\Machine\Models\Machine;

class ShowMachineAction
{
    public function execute(Machine $machine): Machine
    {
        return $machine;
    }
}
