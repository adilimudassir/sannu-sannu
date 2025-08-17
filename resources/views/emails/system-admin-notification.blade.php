@component('mail::message')
# New Organization Application

A new organization has applied to join the Sannu-Sannu platform and requires review.

## Application Details
- **Reference Number:** {{ $application->reference_number }}
- **Organization:** {{ $application->organization_name }}
- **Industry:** {{ ucfirst(str_replace('_', ' ', $application->industry_type)) }}
- **Contact Person:** {{ $application->contact_person_name }}
- **Email:** {{ $application->contact_person_email }}
- **Phone:** {{ $application->contact_person_phone ?? 'Not provided' }}
- **Website:** {{ $application->website_url ?? 'Not provided' }}
- **Registration Number:** {{ $application->business_registration_number ?? 'Not provided' }}
- **Submitted:** {{ $application->submitted_at->format('F j, Y \a\t g:i A') }}

## Business Description
{{ $application->business_description }}

@component('mail::button', ['url' => $reviewUrl])
Review Application
@endcomponent

Please review this application promptly to maintain our service level commitments.

Thanks,<br>
{{ config('app.name') }} System
@endcomponent