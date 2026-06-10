<?php

namespace Tests\Feature\Domains\FileUpload;

use App\Domains\FileUpload\Models\WorkOrderFile;
use App\Domains\User\Enums\UserRole;
use App\Domains\User\Models\User;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Shared\Http\HttpStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkOrderFileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    // -------------------------------------------------------------------------
    // Upload
    // -------------------------------------------------------------------------

    public function test_guest_cannot_upload_file(): void
    {
        $workOrder = WorkOrder::factory()->create();

        $this->postJson("/api/work-orders/{$workOrder->id}/files", [
            'file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ])->assertStatus(HttpStatus::UNAUTHORIZED);
    }

    public function test_admin_can_upload_pdf(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)->postJson(
            "/api/work-orders/{$workOrder->id}/files",
            ['file' => UploadedFile::fake()->create('relatorio.pdf', 500, 'application/pdf')],
        );

        $response
            ->assertStatus(HttpStatus::CREATED)
            ->assertJson([
                'success' => true,
                'data' => [
                    'file_name' => 'relatorio.pdf',
                    'mime_type' => 'application/pdf',
                ],
            ]);

        Storage::disk('local')->assertExists($response->json('data.file_path') ?? '');
    }

    public function test_tecnico_can_upload_image(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)->postJson(
            "/api/work-orders/{$workOrder->id}/files",
            ['file' => UploadedFile::fake()->create('foto.jpg', 200, 'image/jpeg')],
        )->assertStatus(HttpStatus::CREATED);
    }

    public function test_atendente_cannot_upload_file(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $token = $this->loginAs(UserRole::Atendente);

        $this->withToken($token)->postJson(
            "/api/work-orders/{$workOrder->id}/files",
            ['file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')],
        )->assertStatus(HttpStatus::FORBIDDEN);
    }

    public function test_rejects_unsupported_file_type(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)->postJson(
            "/api/work-orders/{$workOrder->id}/files",
            ['file' => UploadedFile::fake()->create('script.exe', 100, 'application/octet-stream')],
        )->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.file.0', fn ($msg): bool => str_contains($msg, 'JPEG') || str_contains($msg, 'allowed'));
    }

    public function test_rejects_file_exceeding_max_size(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $token = $this->loginAs(UserRole::Admin);

        $this->withToken($token)->postJson(
            "/api/work-orders/{$workOrder->id}/files",
            ['file' => UploadedFile::fake()->create('big.pdf', 11000, 'application/pdf')],
        )->assertStatus(HttpStatus::UNPROCESSABLE_ENTITY);
    }

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function test_all_roles_can_list_files(): void
    {
        $workOrder = WorkOrder::factory()->create();
        WorkOrderFile::factory()->forWorkOrder($workOrder)->count(2)->create();

        foreach ([UserRole::Admin, UserRole::Tecnico, UserRole::Atendente] as $role) {
            $this->withToken($this->loginAs($role))
                ->getJson("/api/work-orders/{$workOrder->id}/files")
                ->assertOk()
                ->assertJsonCount(2, 'data');
        }
    }

    public function test_list_returns_download_url(): void
    {
        $workOrder = WorkOrder::factory()->create();
        WorkOrderFile::factory()->forWorkOrder($workOrder)->create();
        $token = $this->loginAs(UserRole::Admin);

        $response = $this->withToken($token)
            ->getJson("/api/work-orders/{$workOrder->id}/files");

        $this->assertNotNull($response->json('data.0.url'));
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function test_admin_can_delete_any_file(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $file = WorkOrderFile::factory()->forWorkOrder($workOrder)->create();
        $token = $this->loginAs(UserRole::Admin);

        Storage::disk('local')->put($file->file_path, 'fake content');

        $this->withToken($token)
            ->deleteJson("/api/work-orders/{$workOrder->id}/files/{$file->id}")
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('work_order_files', ['id' => $file->id]);
        Storage::disk('local')->assertMissing($file->file_path);
    }

    public function test_uploader_can_delete_own_file(): void
    {
        $uploader = User::factory()->create(['role' => UserRole::Tecnico->value, 'password' => Hash::make('password123')]);
        $workOrder = WorkOrder::factory()->create();
        $file = WorkOrderFile::factory()->forWorkOrder($workOrder)->create(['uploaded_by' => $uploader->id]);

        Storage::disk('local')->put($file->file_path, 'fake content');

        $token = $this->postJson('/api/auth/login', [
            'email' => $uploader->email,
            'password' => 'password123',
        ])->json('data.access_token');

        $this->withToken($token)
            ->deleteJson("/api/work-orders/{$workOrder->id}/files/{$file->id}")
            ->assertOk();

        Storage::disk('local')->assertMissing($file->file_path);
    }

    public function test_tecnico_cannot_delete_file_uploaded_by_other(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $file = WorkOrderFile::factory()->forWorkOrder($workOrder)->create();
        $token = $this->loginAs(UserRole::Tecnico);

        $this->withToken($token)
            ->deleteJson("/api/work-orders/{$workOrder->id}/files/{$file->id}")
            ->assertStatus(HttpStatus::FORBIDDEN);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function loginAs(UserRole $role): string
    {
        $user = User::factory()->create([
            'role' => $role->value,
            'password' => Hash::make('password123'),
        ]);

        return $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->json('data.access_token');
    }
}
