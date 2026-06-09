<?php

namespace Tests\Unit\Domains\Client\Actions;

use App\Domains\Client\Actions\UpdateClientAction;
use App\Domains\Client\DTOs\UpdateClientData;
use App\Domains\Client\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateClientActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateClientAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new UpdateClientAction();
    }

    public function test_updates_client_fields(): void
    {
        $client = Client::factory()->create(['name' => 'Old Name']);

        $data = new UpdateClientData(name: 'New Name');

        $updated = $this->action->execute($client, $data);

        $this->assertSame('New Name', $updated->name);
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'New Name']);
    }

    public function test_updates_is_active_field(): void
    {
        $client = Client::factory()->create(['is_active' => true]);

        $data = new UpdateClientData(isActive: false);

        $updated = $this->action->execute($client, $data);

        $this->assertFalse($updated->is_active);
    }

    public function test_does_not_overwrite_unset_fields(): void
    {
        $client = Client::factory()->create([
            'name' => 'Keep This',
            'phone' => '(11) 99999-9999',
        ]);

        $data = new UpdateClientData(city: 'Nova Cidade');

        $updated = $this->action->execute($client, $data);

        $this->assertSame('Keep This', $updated->name);
        $this->assertSame('Nova Cidade', $updated->city);
    }
}
