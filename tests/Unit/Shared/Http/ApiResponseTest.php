<?php

namespace Tests\Unit\Shared\Http;

use App\Shared\Http\ApiResponse;
use App\Shared\Http\HttpStatus;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Stubs\ExampleJsonResource;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_response_uses_standard_envelope(): void
    {
        $response = ApiResponse::success(
            data: ['id' => 1],
            message: 'Resource created successfully',
            status: HttpStatus::CREATED,
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(HttpStatus::CREATED, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'message' => 'Resource created successfully',
            'data' => ['id' => 1],
        ], $response->getData(true));
    }

    public function test_success_response_uses_empty_object_when_data_is_null(): void
    {
        $response = ApiResponse::success();
        $payload = json_decode($response->getContent());

        $this->assertTrue($payload->success);
        $this->assertSame('Operation completed successfully', $payload->message);
        $this->assertSame('{}', json_encode($payload->data));
    }

    public function test_created_response_uses_201_status(): void
    {
        $response = ApiResponse::created(
            data: ['id' => 10],
            message: 'Resource created successfully',
        );

        $this->assertSame(HttpStatus::CREATED, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'message' => 'Resource created successfully',
            'data' => ['id' => 10],
        ], $response->getData(true));
    }

    public function test_deleted_response_uses_success_envelope(): void
    {
        $response = ApiResponse::deleted('Resource deleted successfully');
        $payload = json_decode($response->getContent());

        $this->assertSame(HttpStatus::OK, $response->getStatusCode());
        $this->assertTrue($payload->success);
        $this->assertSame('Resource deleted successfully', $payload->message);
        $this->assertSame('{}', json_encode($payload->data));
    }

    public function test_error_response_uses_standard_envelope(): void
    {
        $response = ApiResponse::error(
            message: 'Operation failed',
            errors: ['code' => 'INVALID_STATE'],
            status: HttpStatus::CONFLICT,
        );

        $this->assertSame(HttpStatus::CONFLICT, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Operation failed',
            'errors' => ['code' => 'INVALID_STATE'],
        ], $response->getData(true));
    }

    public function test_error_response_uses_empty_object_when_errors_are_missing(): void
    {
        $response = ApiResponse::error('Operation failed');
        $payload = json_decode($response->getContent());

        $this->assertFalse($payload->success);
        $this->assertSame('Operation failed', $payload->message);
        $this->assertSame('{}', json_encode($payload->errors));
    }

    public function test_validation_response_uses_422_status(): void
    {
        $response = ApiResponse::validation(
            errors: [
                'email' => ['The email field is required.'],
            ],
        );

        $this->assertSame(HttpStatus::UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'The given data was invalid.',
            'errors' => [
                'email' => ['The email field is required.'],
            ],
        ], $response->getData(true));
    }

    public function test_success_response_resolves_json_resource_into_data(): void
    {
        $response = ApiResponse::success(new ExampleJsonResource([
            'id' => 1,
            'name' => 'Example',
        ]));

        $this->assertSame([
            'success' => true,
            'message' => 'Operation completed successfully',
            'data' => [
                'id' => 1,
                'name' => 'Example',
            ],
        ], $response->getData(true));
    }

    #[DataProvider('httpStatusProvider')]
    public function test_http_status_constants_are_standardized(int $status, int $expected): void
    {
        $this->assertSame($expected, $status);
    }

    public static function httpStatusProvider(): array
    {
        return [
            'ok' => [HttpStatus::OK, 200],
            'created' => [HttpStatus::CREATED, 201],
            'no content' => [HttpStatus::NO_CONTENT, 204],
            'bad request' => [HttpStatus::BAD_REQUEST, 400],
            'unauthorized' => [HttpStatus::UNAUTHORIZED, 401],
            'forbidden' => [HttpStatus::FORBIDDEN, 403],
            'not found' => [HttpStatus::NOT_FOUND, 404],
            'conflict' => [HttpStatus::CONFLICT, 409],
            'unprocessable entity' => [HttpStatus::UNPROCESSABLE_ENTITY, 422],
            'internal server error' => [HttpStatus::INTERNAL_SERVER_ERROR, 500],
        ];
    }
}
