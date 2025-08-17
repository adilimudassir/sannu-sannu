@component('mail::message')
# Welcome to Sannu-Sannu! 🎉

Dear {{ $application->contact_person_name }},

Congratulations! Your organization application has been **approved** and {{ $application->organization_name }} is now part of the Sannu-Sannu platform.

## Application Details
- **Reference Number:** {{ $application->reference_number }}
- **Organization:** {{ $application->organization_name }}
- **Tenant Slug:** {{ $tenant->slug }}
- **Approved:** {{ $application->reviewed_at->format('F j, Y \a\t g:i A') }}

## Your Login Credentials
- **Email:** {{ $user->email }}
- **Temporary Password:** `{{ $temporaryPassword }}`

⚠️ **Important:** Please change your password immediately after your first login for security purposes.

## Getting Started

@component('mail::button', ['url' => $loginUrl])
Login to Your Account
@endcomponent

Once logged in, you'll be guided through our onboarding process which includes:

1. **Profile Setup** - Complete your organization profile
2. **Team Invitation** - Invite team members to join your organization
3. **First Project** - Create your first contribution-based project
4. **Platform Tour** - Learn about all available features

## Your Organization Dashboard

After login, you can access your organization's dedicated space at:
{{ $tenantUrl }}

## Need Help?

Our support team is here to help you get started:
- 📧 Email: support@sannu-sannu.com
- 📚 Documentation: [Getting Started Guide]({{ config('app.url') }}/docs)
- 💬 Live Chat: Available in your dashboard

We're excited to have {{ $application->organization_name }} as part of our community!

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent