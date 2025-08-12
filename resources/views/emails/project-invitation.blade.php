<x-mail::message>
# Project Invitation

You have been invited to join the **{{ $invitation->project->name }}** project.

Click the button below to accept the invitation and get started.

<x-mail::button :url="$url">
Accept Invitation
</x-mail::button>

If you did not expect to receive this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
