<?php

use function Pest\Laravel\get;

it('system admin can access the tenant applications list page', function () {
    $admin = \App\Models\User::factory()->systemAdmin()->create();
    $this->actingAs($admin);
    $response = get('/admin/tenant-applications');
    $response->assertOk();
    $response->assertInertia(fn ($page) =>
        $page->component('admin/tenant-applications/index')
            ->has('applications')
            ->has('filters')
    );
});
