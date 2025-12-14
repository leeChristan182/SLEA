@extends('layouts.app')

@section('title', 'Complete Assessor Requirements')

@section('content')
    <div class="max-w-3xl mx-auto py-10 px-4">

        {{-- Header --}}
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-gray-800">
                Complete Assessor Requirements
            </h1>
            <p class="text-gray-600 mt-2">
                Please provide your assessor information to proceed.
                Your account will be reviewed by the administrator after submission.
            </p>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('profile.complete.assessor.store') }}"
            class="bg-white shadow rounded-lg p-6 space-y-6">
            @csrf

            {{-- Office / Unit --}}
            <div>
                <label for="office_unit" class="block text-sm font-medium text-gray-700">
                    Office / Unit <span class="text-red-500">*</span>
                </label>
                <input type="text" id="office_unit" name="office_unit" value="{{ old('office_unit') }}" required
                    maxlength="150" placeholder="e.g. Office of Student Affairs"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            {{-- Position --}}
            <div>
                <label for="position" class="block text-sm font-medium text-gray-700">
                    Position / Designation <span class="text-red-500">*</span>
                </label>
                <input type="text" id="position" name="position" value="{{ old('position') }}" required maxlength="100"
                    placeholder="e.g. Faculty Adviser, Program Coordinator"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            {{-- Assessor Code (Optional) --}}
            <div>
                <label for="assessor_code" class="block text-sm font-medium text-gray-700">
                    Assessor Code <span class="text-gray-400">(optional)</span>
                </label>
                <input type="text" id="assessor_code" name="assessor_code" value="{{ old('assessor_code') }}" maxlength="50"
                    placeholder="If provided by the institution"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            {{-- Info Notice --}}
            <div class="rounded-lg bg-blue-50 border border-blue-200 p-4 text-sm text-blue-700">
                <p>
                    After submission, your account will be placed under administrative review.
                    You will be notified once your assessor access is fully approved.
                </p>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Submit for Review
                </button>
            </div>
        </form>
    </div>
@endsection