<?php

namespace Tests\Unit\Domains\Client\Actions;

use App\Domains\Client\Actions\CreateClientAction;
use App\Domains\Client\DTOs\CreateClientData;
use App\Domains\Client\Models\Client;
use App\Shared\Exceptions\BusinessRuleException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateClientActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateClientAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateClientAction();
    }

    public function test_creates_client_successfully(): void
    {
        $data = new CreateClientData(
            name: 'Acme Corp',
            email: 'contato@acme.com',
            cpf_cnpj: '12.345.678/0001-99',
            phone: '(11) 99999-9999',
            street: 'Rua das Flores',
            number: '123',
            state: 'SP',
            district: 'Centro',
            city: 'São Paulo',
            zip_code: '01310-100',
            complement: null,
        );

        $client = $this->action->execute($data);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertDatabaseHas('clients', [
            'email' => 'contato@acme.com',
            'cpf_cnpj' => '12.345.678/0001-99',
            'is_active' => true,
        ]);
    }

    public function test_throws_when_cpf_cnpj_already_exists(): void
    {
        Client::factory()->create(['cpf_cnpj' => '12.345.678/0001-99']);

        $data = new CreateClientData(
            name: 'Another Corp',
            email: 'other@example.com',
            cpf_cnpj: '12.345.678/0001-99',
            phone: '(11) 98888-8888',
            street: 'Av. Paulista',
            number: '1000',
            state: 'SP',
            district: 'Bela Vista',
            city: 'São Paulo',
            zip_code: '01311-100',
        );

        $this->expectException(BusinessRuleException::class);

        $this->action->execute($data);
    }
}
