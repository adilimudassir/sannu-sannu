<?php

namespace Tests\Unit;

use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use PHPUnit\Framework\TestCase;

class ProjectEnumsTest extends TestCase
{
    public function test_project_status_enum_values()
    {
        $this->assertEquals('draft', ProjectStatus::DRAFT->value);
        $this->assertEquals('active', ProjectStatus::ACTIVE->value);
        $this->assertEquals('paused', ProjectStatus::PAUSED->value);
        $this->assertEquals('completed', ProjectStatus::COMPLETED->value);
        $this->assertEquals('cancelled', ProjectStatus::CANCELLED->value);
    }

    public function test_project_status_labels()
    {
        $this->assertEquals('Draft', ProjectStatus::DRAFT->label());
        $this->assertEquals('Active', ProjectStatus::ACTIVE->label());
        $this->assertEquals('Paused', ProjectStatus::PAUSED->label());
        $this->assertEquals('Completed', ProjectStatus::COMPLETED->label());
        $this->assertEquals('Cancelled', ProjectStatus::CANCELLED->label());
    }

    public function test_project_status_accepts_contributions()
    {
        $this->assertTrue(ProjectStatus::ACTIVE->acceptsContributions());
        $this->assertFalse(ProjectStatus::DRAFT->acceptsContributions());
        $this->assertFalse(ProjectStatus::PAUSED->acceptsContributions());
        $this->assertFalse(ProjectStatus::COMPLETED->acceptsContributions());
        $this->assertFalse(ProjectStatus::CANCELLED->acceptsContributions());
    }

    public function test_project_status_is_active()
    {
        $this->assertTrue(ProjectStatus::DRAFT->isActive());
        $this->assertTrue(ProjectStatus::ACTIVE->isActive());
        $this->assertTrue(ProjectStatus::PAUSED->isActive());
        $this->assertFalse(ProjectStatus::COMPLETED->isActive());
        $this->assertFalse(ProjectStatus::CANCELLED->isActive());
    }

    public function test_project_status_is_final()
    {
        $this->assertFalse(ProjectStatus::DRAFT->isFinal());
        $this->assertFalse(ProjectStatus::ACTIVE->isFinal());
        $this->assertFalse(ProjectStatus::PAUSED->isFinal());
        $this->assertTrue(ProjectStatus::COMPLETED->isFinal());
        $this->assertTrue(ProjectStatus::CANCELLED->isFinal());
    }

    public function test_project_visibility_enum_values()
    {
        $this->assertEquals('public', ProjectVisibility::PUBLIC->value);
        $this->assertEquals('private', ProjectVisibility::PRIVATE->value);
        $this->assertEquals('invite_only', ProjectVisibility::INVITE_ONLY->value);
    }

    public function test_project_visibility_labels()
    {
        $this->assertEquals('Public', ProjectVisibility::PUBLIC->label());
        $this->assertEquals('Private', ProjectVisibility::PRIVATE->label());
        $this->assertEquals('Invite Only', ProjectVisibility::INVITE_ONLY->label());
    }

    public function test_project_visibility_is_publicly_discoverable()
    {
        $this->assertTrue(ProjectVisibility::PUBLIC->isPubliclyDiscoverable());
        $this->assertFalse(ProjectVisibility::PRIVATE->isPubliclyDiscoverable());
        $this->assertFalse(ProjectVisibility::INVITE_ONLY->isPubliclyDiscoverable());
    }

    public function test_project_visibility_has_restricted_access()
    {
        $this->assertFalse(ProjectVisibility::PUBLIC->hasRestrictedAccess());
        $this->assertTrue(ProjectVisibility::PRIVATE->hasRestrictedAccess());
        $this->assertTrue(ProjectVisibility::INVITE_ONLY->hasRestrictedAccess());
    }

    public function test_project_status_static_methods()
    {
        $this->assertCount(5, ProjectStatus::all());
        $this->assertCount(1, ProjectStatus::acceptingContributions());
        $this->assertCount(3, ProjectStatus::activeStatuses());
        $this->assertCount(2, ProjectStatus::finalStatuses());
    }

    public function test_project_visibility_static_methods()
    {
        $this->assertCount(3, ProjectVisibility::all());
        $this->assertCount(1, ProjectVisibility::publiclyDiscoverable());
        $this->assertCount(2, ProjectVisibility::restrictedAccess());
    }
}