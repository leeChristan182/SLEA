<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Toggle OTP via .env (AUTH_OTP=true/false)
    protected function otpEnabled(): bool
    {
        return filter_var(config('app.auth_otp', env('AUTH_OTP', false)), FILTER_VALIDATE_BOOL);
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['nullable', 'boolean'],
        ]);

        // Simple throttle: 5 tries / 60s per email+IP
        $key = 'login:' . sha1($request->ip() . '|' . $request->input('email'));
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many attempts. Try again in {$seconds}s.",
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.',
            ]);
        }

        RateLimiter::clear($key);

        $user = Auth::user();

        if ($this->otpEnabled()) {
            // Don’t fully sign in until OTP passes: log out, stash user id in session
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $request->session()->put('otp_user_id', $user->id);
            $code = random_int(100000, 999999);
            Cache::put("otp:{$user->id}", $code, now()->addMinutes(10));

            // TODO: send via mail/SMS/notify; for local dev you can log it:
            // \Log::info("OTP for {$user->email}: {$code}");

            return redirect()->route('otp.show')->with('status', 'We sent a 6-digit code.');
        }

        // Normal login path
        $request->session()->regenerate();
        return $this->redirectByRole($user);
    }

    public function showOtp(Request $request)
    {
        abort_unless($request->session()->has('otp_user_id'), 403);
        return view('auth.otp'); // simple form with one input named "code"
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $userId = $request->session()->pull('otp_user_id');
        abort_unless($userId, 403);

        $expected = Cache::get("otp:{$userId}");
        if (!$expected || $expected !== (int)$request->code) {
            // put it back if wrong so user can retry
            $request->session()->put('otp_user_id', $userId);
            return back()->withErrors(['code' => 'Incorrect or expired code.'])->withInput();
        }

        Cache::forget("otp:{$userId}");
        Auth::loginUsingId($userId);
        $request->session()->regenerate();

        return $this->redirectByRole(Auth::user());
    }

    public function resendOtp(Request $request)
    {
        $userId = $request->session()->get('otp_user_id');
        abort_unless($userId, 403);

        $code = random_int(100000, 999999);
        Cache::put("otp:{$userId}", $code, now()->addMinutes(10));
        // send notify/email here; for dev:
        // \Log::info("Resent OTP for user {$userId}: {$code}");

        return back()->with('status', 'New code sent.');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'student', // self-registers are students
            'status'   => 'active',
        ]);

        if ($this->otpEnabled()) {
            $request->session()->put('otp_user_id', $user->id);
            $code = random_int(100000, 999999);
            Cache::put("otp:{$user->id}", $code, now()->addMinutes(10));
            return redirect()->route('otp.show')->with('status', 'We sent a 6-digit code.');
        }

        Auth::login($user);
        $request->session()->regenerate();
        return $this->redirectByRole($user);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    protected function redirectByRole(User $user)
    {
        return match ($user->role) {
            'admin'    => redirect()->route('admin.profile'),
            'assessor' => redirect()->route('assessor.profile'),
            default    => redirect()->route('student.profile'),
        };
    }
}
