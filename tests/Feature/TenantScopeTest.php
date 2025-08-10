<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_scopes_queries_to_the_current_tenant()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);

        $this->actingAs($user1);

        app()->instance('tenant', $tenant1);

        $this->assertCount(1, User::all());
        $this->assertEquals($user1->id, User::first()->id);

        app()->instance('tenant', $tenant2);

        $this->assertCount(1, User::all());
        $this->assertEquals($user2->id, User::first()->id);
    }
}
