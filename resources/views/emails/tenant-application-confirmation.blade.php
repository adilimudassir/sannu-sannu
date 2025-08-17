@component('mail::message')
# Application Received

Dear {{ $application->contact_person_name }},

Thank you for submitting your organization application to join the Sannu-Sannu platform. We have received your application and it is now under review.

## Application Details
- **Reference Number:** {{ $application->reference_number }}
- **Organization:** {{ $application->organization_name }}
- **Industry:** {{ ucfirst(str_replace('_', ' ', $application->industry_type)) }}
- **Submitted:** {{ $application->submitted_at->format('F j, Y \a\t g:i A') }}

## What Happens Next?

1. **Review Process:** Our team will review your application within 2-3 business days
2. **Verification:** We may contact you if additional information is needed
3. **Decision:** You'll receive an email notification with our decision
4. **Onboarding:** If approved, you'll receive login credentials and onboarding instructions

@component('mail::button', ['url' => $statusUrl])
Check Application Status
@endcomponent

You can check your application status at any time using your reference number: **{{ $application->reference_number }}**

If you have any questions, please don't hesitate to contact our support team.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent