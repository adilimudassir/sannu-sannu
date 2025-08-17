<?php

use App\Models\User;
use function Pest\Laravel\patch;
use App\Models\TenantApplication;
use function Pest\Laravel\actingAs;
use Illuminate\Support\Facades\Mail;
use App\Enums\TenantApplicationStatus;
use App\Mail\TenantApplicationApprovalMail;
use App\Mail\TenantApplicationRejectionMail;

it('sends approval email with correct details', function () {
    Mail::fake();
    $admin = User::factory()->systemAdmin()->create();
    $application = TenantApplication::factory()->create(['status' => 'pending']);
    actingAs($admin);
    patch("/admin/tenant-applications/{$application->id}/approve", [
        'notes' => 'Welcome!'
    ]);
    Mail::assertQueued(TenantApplicationApprovalMail::class, function ($mail) use ($application) {
        return $mail->application->id === $application->id
            && $mail->application->notes === 'Welcome!';
    });
});

it('sends rejection email with reason and notes', function () {
    Mail::fake();
    $admin = User::factory()->systemAdmin()->create();
    $application = TenantApplication::factory()->create(['status' => 'pending']);
    actingAs($admin);
    patch("/admin/tenant-applications/{$application->id}/reject", [
        'rejection_reason' => 'Incomplete documents',
        'notes' => 'Missing registration certificate.'
    ]);
    Mail::assertQueued(TenantApplicationRejectionMail::class, function ($mail) use ($application) {
        return $mail->application->id === $application->id
            && $mail->application->rejection_reason === 'Incomplete documents'
            && $mail->application->notes === 'Missing registration certificate.';
    });
});
