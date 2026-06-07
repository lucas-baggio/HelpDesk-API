<?php

namespace Tests\Unit\Shared\Http\Controllers;

use App\Shared\Http\Controllers\ApiController;
use App\Shared\Http\HttpStatus;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class ApiControllerTest extends TestCase
{
    public function test_controller_trait_returns_success_envelope(): void
    {
        $controller = new class extends ApiController
        {
            public function store(): JsonResponse
            {
                return $this->created(['id' => 1], 'Resource created successfully');
            }
        };

        $response = $controller->store();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(HttpStatus::CREATED, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'message' => 'Resource created successfully',
            'data' => ['id' => 1],
        ], $response->getData(true));
    }

    public function test_controller_trait_returns_validation_envelope(): void
    {
        $controller = new class extends ApiController
        {
            public function validate(): JsonResponse
            {
                return $this->validation([
                    'name' => ['The name field is required.'],
                ]);
            }
        };

        $response = $controller->validate();

        $this->assertSame(HttpStatus::UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'The given data was invalid.',
            'errors' => [
                'name' => ['The name field is required.'],
            ],
        ], $response->getData(true));
    }
}
