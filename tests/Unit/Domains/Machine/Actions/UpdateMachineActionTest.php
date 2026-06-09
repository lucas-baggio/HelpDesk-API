<?php

namespace Tests\Unit\Domains\Machine\Actions;

use App\Domains\Client\Models\Client;
use App\Domains\Machine\Actions\UpdateMachineAction;
use App\Domains\Machine\DTOs\UpdateMachineData;
use App\Domains\Machine\Models\Machine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateMachineActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateMachineAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new UpdateMachineAction();
    }

    public function test_updates_machine_name(): void
    {
        $machine = Machine::factory()->create(['name' => 'Old Name']);

        $updated = $this->action->execute($machine, new UpdateMachineData(name: 'New Name'));

        $this->assertSame('New Name', $updated->name);
    }

    public function test_deactivates_via_update(): void
    {
        $machine = Machine::factory()->create(['is_active' => true]);

        $updated = $this->action->execute($machine, new UpdateMachineData(isActive: false));

        $this->assertFalse($updated->is_active);
    }

    public function test_does_not_overwrite_unset_fields(): void
    {
        $machine = Machine::factory()->create([
            'name' => 'Keep This',
            'model' => 'Keep Model',
        ]);

        $updated = $this->action->execute($machine, new UpdateMachineData(serialNumber: 'NEW-SN'));

        $this->assertSame('Keep This', $updated->name);
        $this->assertSame('Keep Model', $updated->model);
    }
}
