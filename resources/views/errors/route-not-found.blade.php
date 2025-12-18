{{-- resources/views/errors/route-not-found.blade.php --}}
@extends('layouts.app')

@section('title', 'Route Not Found')

@section('content')
    <div class="min-h-[60vh] flex flex-col items-center justify-center text-center px-4">
        <h1 class="text-4xl font-bold mb-3">Out of Bounds</h1>
        <p class="text-gray-600 mb-4">
            The route you tried to access doesn’t exist in the system.
        </p>
        <p class="text-gray-500 mb-6">
            Please check the link or go back to your Profile.
        </p>

        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                    Go to Admin Profile
                </a>
            @elseif(auth()->user()->isAssessor())
                <a href="{{ route('assessor.dashboard') }}" class="btn btn-primary">
                    Go to Assessor Profile
                </a>
            @else
                <a href="{{ route('student.profile') }}" class="btn btn-primary">
                    Go to Student Profile
                </a>
            @endif
        @else
            <a href="{{ route('login.show') }}" class="btn btn-primary">
                Go to Login
            </a>
        @endauth
    </div>
@endsection