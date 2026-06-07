<?php

namespace Tests\Feature\Shared\Http;

use App\Shared\Exceptions\BusinessRuleException;
use App\Shared\Exceptions\ResourceConflictException;
use App\Shared\Exceptions\UnauthorizedActionException;
use App\Shared\Http\HttpStatus;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiExceptionHandlingTest extends TestCase
{
    public function test_health_endpoint_returns_success_envelope(): void
    {
        $response = $this->getJson('/api/health');

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'API is running',
                'data' => ['status' => 'ok'],
            ]);
    }

    public function test_validation_errors_use_standard_envelope(): void
    {
        Route::post('/api/test/validation', function () {
            request()->validate([
                'email' => ['required', 'email'],
            ]);
        });

        $response = $this->postJson('/api/test/validation', []);

        $response
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY)
            ->assertJson([
                'success' => false,
                'message' => 'The email field is required.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['email'],
            ]);
    }

    public function test_business_rule_exception_uses_standard_error_envelope(): void
    {
        Route::get('/api/test/business-rule', function () {
            throw BusinessRuleException::withCode(
                'BUSINESS_RULE_VIOLATION',
                'The operation violates a business rule.',
            );
        });

        $response = $this->getJson('/api/test/business-rule');

        $response
            ->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY)
            ->assertJson([
                'success' => false,
                'message' => 'The operation violates a business rule.',
                'errors' => ['code' => 'BUSINESS_RULE_VIOLATION'],
            ]);
    }

    public function test_resource_conflict_exception_returns_409(): void
    {
        Route::get('/api/test/conflict', function () {
            throw ResourceConflictException::withCode(
                'RESOURCE_CONFLICT',
                'The resource is in a conflicting state.',
            );
        });

        $response = $this->getJson('/api/test/conflict');

        $response
            ->assertStatus(HttpStatus::CONFLICT)
            ->assertJson([
                'success' => false,
                'message' => 'The resource is in a conflicting state.',
                'errors' => ['code' => 'RESOURCE_CONFLICT'],
            ]);
    }

    public function test_unauthorized_action_exception_returns_403(): void
    {
        Route::get('/api/test/unauthorized', function () {
            throw new UnauthorizedActionException('You are not allowed to perform this action.');
        });

        $response = $this->getJson('/api/test/unauthorized');

        $response
            ->assertStatus(HttpStatus::FORBIDDEN)
            ->assertJson([
                'success' => false,
                'message' => 'You are not allowed to perform this action.',
            ]);
    }

    public function test_not_found_route_returns_standard_error_envelope(): void
    {
        $response = $this->getJson('/api/unknown-endpoint');

        $response
            ->assertStatus(HttpStatus::NOT_FOUND)
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found.',
            ]);
    }
}
