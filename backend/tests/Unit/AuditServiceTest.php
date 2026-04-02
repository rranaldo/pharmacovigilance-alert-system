<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AuditService::class);
    }

    public function test_creates_log_with_all_parameters(): void
    {
        $user = User::create([
            'username' => 'admin',
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $log = $this->service->log(
            $user,
            'alert.sent',
            'App\\Models\\Alert',
            42,
            ['lot_number' => '951357'],
            '192.168.1.1',
        );

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'alert.sent',
            'entity_type' => 'App\\Models\\Alert',
            'entity_id' => 42,
            'ip_address' => '192.168.1.1',
        ]);

        $this->assertEquals(['lot_number' => '951357'], $log->details);
    }

    public function test_creates_log_with_null_user(): void
    {
        $log = $this->service->log(null, 'system.cleanup');

        $this->assertNull($log->user_id);
        $this->assertEquals('system.cleanup', $log->action);
    }

    public function test_ip_address_parameter_overrides_request_ip(): void
    {
        $log = $this->service->log(
            null,
            'test.action',
            ipAddress: '10.0.0.1',
        );

        $this->assertEquals('10.0.0.1', $log->ip_address);
    }

    public function test_falls_back_to_request_ip_when_no_ip_provided(): void
    {
        $log = $this->service->log(null, 'test.action');

        $this->assertNotNull($log->ip_address);
    }
}
