<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Models\TenantApplication;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TenantApplicationApprovalMail extends Mailable implements ShouldQueue
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        public TenantApplication $application,
        public Tenant $tenant,
        public User $user,
        public string $temporaryPassword
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Organization Application Approved - Welcome to Sannu-Sannu!',
            to: [$this->application->contact_person_email],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tenant-application-approval',
            with: [
                'application' => $this->application,
                'tenant' => $this->tenant,
                'user' => $this->user,
                'temporaryPassword' => $this->temporaryPassword,
                'loginUrl' => route('login'),
                'tenantUrl' => route('tenant.dashboard', $this->tenant->slug),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
