@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-maroon text-white">
                        <h4 class="mb-0">Forgot Password</h4>
                    </div>
                    <div class="card-body">
                        @if(session('status'))
                            <div class="alert alert-info">{{ session('status') }}</div>
                        @endif

                        @if(!session()->has('password_reset_user_id'))
                            {{-- Step 1: ask for email --}}
                            <p class="mb-3">
                                Enter your registered USeP email. We’ll send you an OTP to confirm your identity.
                            </p>
                            <form method="POST" action="{{ route('password.email') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">USeP Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    Send OTP
                                </button>
                            </form>
                        @else
                            {{-- Step 3: set new password after OTP is verified --}}
                            <p class="mb-3">
                                OTP verified. Set your new password below.
                            </p>
                            <form method="POST" action="{{ route('password.update') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-success w-100">
                                    Update Password
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection