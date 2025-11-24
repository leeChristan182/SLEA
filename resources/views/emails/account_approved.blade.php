@component('mail::message')
# SLEA Account Approved

Dear {{ $user->first_name }},

Your **Student Leadership Excellence Awards (SLEA)** account has been **approved**.

You may now log in to the SLEA portal using your USeP email:

**{{ $user->email }}**

@component('mail::button', ['url' => url('/login')])
Go to SLEA Portal
@endcomponent

If you believe this email was sent to you in error, please contact your OSAS office.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent