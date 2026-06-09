<?php

namespace Tests\Unit\Domains\Machine\Actions;

use App\Domains\Client\Models\Client;
use App\Domains\Machine\Actions\CreateMachineAction;
use App\Domains\Machine\DTOs\CreateMachineData;
use App\Domains\Machine\Models\Machine;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateMachineActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateMachineAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateMachineAction();
    }

    public function test_creates_machine_successfully(): void
    {
        $client = Client::factory()->create();

        $data = new CreateMachineData(
            clientId: $client->id,
            name: 'Notebook Dell',
            model: 'Latitude 5420',
            serialNumber: 'SN-00001',
        );

        $machine = $this->action->execute($data);

        $this->assertInstanceOf(Machine::class, $machine);
        $this->assertDatabaseHas('machines', [
            'client_id' => $client->id,
            'serial_number' => 'SN-00001',
            'is_active' => true,
        ]);
    }

    public function test_creates_machine_without_serial_number(): void
    {
        $client = Client::factory()->create();

        $data = new CreateMachineData(
            clientId: $client->id,
            name: 'Monitor LG',
        );

        $machine = $this->action->execute($data);

        $this->assertNull($machine->serial_number);
    }

    public function test_throws_when_serial_number_duplicated_in_same_client(): void
    {
        $client = Client::factory()->create();
        Machine::factory()->forClient($client)->create(['serial_number' => 'SN-DUPE']);

        $data = new CreateMachineData(
            clientId: $client->id,
            name: 'Outro Equipamento',
            serialNumber: 'SN-DUPE',
        );

        $this->expectException(BusinessRuleException::class);

        $this->action->execute($data);
    }

    public function test_allows_same_serial_number_for_different_clients(): void
    {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();

        Machine::factory()->forClient($clientA)->create(['serial_number' => 'SN-SHARED']);

        $data = new CreateMachineData(
            clientId: $clientB->id,
            name: 'Equipamento B',
            serialNumber: 'SN-SHARED',
        );

        $machine = $this->action->execute($data);

        $this->assertSame('SN-SHARED', $machine->serial_number);
    }
}
