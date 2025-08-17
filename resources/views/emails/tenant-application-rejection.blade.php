@component('mail::message')
# Application Update

Dear {{ $application->contact_person_name }},

Thank you for your interest in joining the Sannu-Sannu platform. After careful review, we are unable to approve your organization application at this time.

## Application Details
- **Reference Number:** {{ $application->reference_number }}
- **Organization:** {{ $application->organization_name }}
- **Reviewed:** {{ $application->reviewed_at->format('F j, Y \a\t g:i A') }}

## Reason for Decision
{{ $application->rejection_reason }}

## Next Steps

We encourage you to address the concerns mentioned above and reapply. Here's how you can improve your application:

1. **Review Requirements** - Ensure your application meets all platform requirements
2. **Provide Additional Information** - Include more detailed business information
3. **Update Documentation** - Ensure all business documents are current and complete
4. **Contact Support** - Reach out if you need clarification on requirements

@component('mail::button', ['url' => $reapplyUrl])
Submit New Application
@endcomponent

## Need Assistance?

If you have questions about this decision or need guidance on reapplying:

- 📧 Email: {{ $supportEmail }}
- 📞 Phone: Available during business hours
- 💬 Live Chat: Available on our website

We appreciate your interest in Sannu-Sannu and hope to welcome you to our platform in the future.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent