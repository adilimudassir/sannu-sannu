<?php

use function Pest\Laravel\get;

it('system admin can view a tenant application details page', function () {
    $admin = \App\Models\User::factory()->systemAdmin()->create();
    $application = \App\Models\TenantApplication::factory()->create();
    $this->actingAs($admin);
    $response = get("/admin/tenant-applications/{$application->id}");
    $response->assertOk();
    $response->assertInertia(fn ($page) =>
        $page->component('admin/tenant-applications/show')
            ->where('application.id', $application->id)
            ->where('application.organization_name', $application->organization_name)
    );
});
