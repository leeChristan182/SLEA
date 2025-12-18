@component('mail::message')
# SLEA Final Review Result

Dear {{ $student->first_name }} {{ $student->last_name }},

Your application for the **Student Leadership Excellence Award (SLEA)** has completed the **final administrative
review**.

**Result:** {{ $decisionLabel }}

@if (!empty($finalReview->remarks))
    **Remarks from the SLEA Committee:**

    > {{ $finalReview->remarks }}
@endif

@if ($finalReview->decision === 'approved')
    You will receive further instructions regarding the awarding process and any additional requirements or schedule of
    recognition.
@else
    We appreciate the time and effort you invested in completing the requirements. Your leadership and service are still
    highly valued by the university community.
@endif

If you believe there is an error in your record, you may coordinate with the SLEA committee or your program coordinator
for clarification.

Thank you for your participation in the SLEA.

Sincerely,
**SLEA Committee**
{{ config('app.name') }}

@endcomponent