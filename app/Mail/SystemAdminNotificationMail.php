<?php

namespace App\Mail;

use App\Enums\Role;
use App\Models\TenantApplication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SystemAdminNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public TenantApplication $application
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Get all system admin emails
        $adminEmails = User::where('role', Role::SYSTEM_ADMIN)
            ->pluck('email')
            ->toArray();

        return new Envelope(
            subject: 'New Organization Application - '.$this->application->organization_name,
            to: $adminEmails,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.system-admin-notification',
            with: [
                'application' => $this->application,
                'reviewUrl' => route('admin.tenant-applications.show', $this->application),
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
