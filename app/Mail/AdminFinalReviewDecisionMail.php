<?php

namespace App\Mail;

use App\Models\User;
use App\Models\FinalReview;
use App\Models\AssessorFinalReview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminFinalReviewDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $student;
    public FinalReview $finalReview;
    public ?AssessorFinalReview $assessorFinalReview;
    public string $decisionLabel;

    public function __construct(User $student, FinalReview $finalReview, ?AssessorFinalReview $assessorFinalReview = null)
    {
        $this->student            = $student;
        $this->finalReview        = $finalReview;
        $this->assessorFinalReview = $assessorFinalReview;

        // Human-friendly label for the email body
        $this->decisionLabel = match ($finalReview->decision) {
            'approved'       => 'QUALIFIED for the Student Leadership Excellence Award (SLEA)',
            'not_qualified'  => 'NOT QUALIFIED for the Student Leadership Excellence Award (SLEA)',
            default          => strtoupper((string) $finalReview->decision),
        };
    }

    public function build()
    {
        $subject = $this->finalReview->decision === 'approved'
            ? 'SLEA Final Review Result: You are QUALIFIED'
            : 'SLEA Final Review Result';

        return $this->subject($subject)
            ->markdown('emails.admin_final_review_decision');
    }
}
