{{-- resources/views/emails/otp.blade.php --}}
<p>Dear {{ $user->first_name ?? 'Student' }},</p>

<p>Your one-time password (OTP) for {{ $purpose }} is:</p>

<h2 style="letter-spacing:4px;font-family:monospace;">{{ $code }}</h2>

<p>This code will expire in {{ config('auth.otp.lifetime_minutes', 10) }} minutes.</p>

<p>If you did not request this, you can safely ignore this email.</p>