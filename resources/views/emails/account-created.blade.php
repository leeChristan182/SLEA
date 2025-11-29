<p>Dear {{ $user->first_name }},</p>

<p>An account has been created for you in the SLEA system.</p>

<p>
    <strong>Login email:</strong> {{ $user->email }}<br>
    <strong>Temporary password:</strong> {{ $plainPassword }}
</p>

<p>
    For security, please log in as soon as possible and change your password
    on your profile page.
</p>