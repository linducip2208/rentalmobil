<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SystemSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_are_typed_cached_invalidated_and_audited(): void
    {
        $this->actingAs(User::findOrFail(1));
        $service = app(SystemSettingService::class);
        $values = $service->values();
        $values['company_name'] = 'PT Rental Enterprise Indonesia';
        $values['allow_negative_stock'] = true;
        $values['security_max_login_attempts'] = 7;

        $service->save($values);

        $this->assertSame('PT Rental Enterprise Indonesia', SystemSetting::get('company_name'));
        $this->assertTrue(SystemSetting::get('allow_negative_stock'));
        $this->assertSame(7, SystemSetting::get('security_max_login_attempts'));
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'setting.updated',
            'auditable_type' => SystemSetting::class,
        ]);
        $this->assertGreaterThanOrEqual(3, AuditLog::where('action', 'setting.updated')->count());
    }
}
