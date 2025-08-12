<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\Role;
use App\Models\User;
use App\Models\Tenant;
use App\Policies\SystemAdminPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SystemAdminPolicyTest extends TestCase
{
    use RefreshDatabase;

    private SystemAdminPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new SystemAdminPolicy();
    }

    public function test_system_admin_can_access_admin_panel()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertTrue($this->policy->accessAdminPanel($systemAdmin));
    }

    public function test_non_system_admin_cannot_access_admin_panel()
    {
        $tenant = Tenant::factory()->create();
        $tenantAdmin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN
        ]);

        $this->assertFalse($this->policy->accessAdminPanel($tenantAdmin));
    }

    public function test_system_admin_can_manage_tenants()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertTrue($this->policy->manageTenants($systemAdmin));
    }

    public function test_system_admin_can_view_platform_analytics()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertTrue($this->policy->viewPlatformAnalytics($systemAdmin));
    }

    public function test_system_admin_can_manage_platform_fees()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertTrue($this->policy->managePlatformFees($systemAdmin));
    }

    public function test_system_admin_can_manage_system_settings()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertTrue($this->policy->manageSystemSettings($systemAdmin));
    }

    public function test_system_admin_can_view_all_users()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertTrue($this->policy->viewAllUsers($systemAdmin));
    }

    public function test_system_admin_can_manage_user_roles()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertTrue($this->policy->manageUserRoles($systemAdmin));
    }

    public function test_system_admin_can_view_system_logs()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertTrue($this->policy->viewSystemLogs($systemAdmin));
    }

    public function test_system_admin_can_manage_integrations()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertTrue($this->policy->manageIntegrations($systemAdmin));
    }

    public function test_system_admin_can_perform_maintenance()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertTrue($this->policy->performMaintenance($systemAdmin));
    }

    public function test_system_admin_can_export_platform_data()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertTrue($this->policy->exportPlatformData($systemAdmin));
    }

    public function test_system_admin_can_manage_payment_providers()
    {
        $systemAdmin = User::factory()->create([
            'role' => Role::SYSTEM_ADMIN
        ]);

        $this->assertTrue($this->policy->managePaymentProviders($systemAdmin));
    }

    public function test_tenant_admin_cannot_perform_system_admin_actions()
    {
        $tenant = Tenant::factory()->create();
        $tenantAdmin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::TENANT_ADMIN
        ]);

        $this->assertFalse($this->policy->manageTenants($tenantAdmin));
        $this->assertFalse($this->policy->viewPlatformAnalytics($tenantAdmin));
        $this->assertFalse($this->policy->managePlatformFees($tenantAdmin));
        $this->assertFalse($this->policy->manageSystemSettings($tenantAdmin));
        $this->assertFalse($this->policy->viewSystemLogs($tenantAdmin));
        $this->assertFalse($this->policy->performMaintenance($tenantAdmin));
    }

    public function test_contributor_cannot_perform_system_admin_actions()
    {
        $tenant = Tenant::factory()->create();
        $contributor = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => Role::CONTRIBUTOR
        ]);

        $this->assertFalse($this->policy->accessAdminPanel($contributor));
        $this->assertFalse($this->policy->manageTenants($contributor));
        $this->assertFalse($this->policy->viewPlatformAnalytics($contributor));
        $this->assertFalse($this->policy->managePlatformFees($contributor));
        $this->assertFalse($this->policy->manageSystemSettings($contributor));
        $this->assertFalse($this->policy->viewAllUsers($contributor));
        $this->assertFalse($this->policy->manageUserRoles($contributor));
        $this->assertFalse($this->policy->viewSystemLogs($contributor));
        $this->assertFalse($this->policy->manageIntegrations($contributor));
        $this->assertFalse($this->policy->performMaintenance($contributor));
        $this->assertFalse($this->policy->exportPlatformData($contributor));
        $this->assertFalse($this->policy->managePaymentProviders($contributor));
    }
}