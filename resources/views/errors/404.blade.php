{{-- resources/views/errors/404.blade.php --}}
@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
    <div class="min-h-[60vh] flex flex-col items-center justify-center text-center px-4">
        <h1 class="text-5xl font-bold text-red-600 mb-2">404</h1>
        <h2 class="text-xl font-semibold mb-4">Oops… That page doesn’t exist.</h2>
        <p class="text-gray-600 mb-6">
            The route you are trying to access is not available or may have been moved.
        </p>

        <a href="{{ auth()->check() ? url('/') : route('login') }}" class="btn btn-primary">
            ⬅ Back
        </a>

    </div>
@endsection