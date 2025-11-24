@extends('layouts.app')

@section('title', 'Enter OTP')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-maroon text-white">
                        <h4 class="mb-0">One-Time Password</h4>
                    </div>
                    <div class="card-body">
                        @if(session('status'))
                            <div class="alert alert-info">{{ session('status') }}</div>
                        @endif

                        <p class="mb-3">
                            Enter the 6-digit code sent to your USeP email address.
                        </p>

                        <form method="POST" action="{{ route('otp.verify') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="code" class="form-label">OTP Code</label>
                                <input type="text" id="code" name="code"
                                    class="form-control @error('code') is-invalid @enderror" maxlength="6" required
                                    autofocus>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Verify OTP
                            </button>
                        </form>

                        {{-- later you can add a "Resend code" button if you want --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection