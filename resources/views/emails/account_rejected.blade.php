@component('mail::message')
# SLEA Account Registration - Action Required
<img src="{{ asset('images/osas-logo.png') }}" alt="SLEA Logo"
    style="max-width:150px; margin:20px auto; display:block;">

Dear {{ $user->first_name }},

We regret to inform you that your **Student Leadership Excellence Awards (SLEA)** account registration has been
**rejected**.

@if(!empty($rejectionReason))
    **Reason for Rejection:**
    {{ $rejectionReason }}
@else
    Your registration did not meet the required criteria for approval.
@endif

If you believe this decision was made in error, or if you have questions about your registration, please contact the
OSAS office for assistance.

You may also re-register with corrected information if needed.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent



