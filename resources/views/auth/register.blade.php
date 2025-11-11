<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create your account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">
    <main class="max-w-md mx-auto p-6">
        <h1 class="text-2xl font-semibold mb-4">Student Registration</h1>

        @if (session('status'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
            {{ session('status') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm mb-1">First name</label>
                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required
                        class="w-full border rounded px-3 py-2" autocomplete="given-name">
                </div>

                <div>
                    <label for="last_name" class="block text-sm mb-1">Last name</label>
                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required
                        class="w-full border rounded px-3 py-2" autocomplete="family-name">
                </div>
            </div>

            <div>
                <label for="middle_name" class="block text-sm mb-1">Middle name (optional)</label>
                <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name') }}"
                    class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label for="email" class="block text-sm mb-1">UP/School Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                    class="w-full border rounded px-3 py-2" autocomplete="email">
            </div>

            <div>
                <label for="password" class="block text-sm mb-1">Password</label>
                <input id="password" name="password" type="password" required
                    class="w-full border rounded px-3 py-2" autocomplete="new-password">
                <p class="text-xs text-gray-600 mt-1">Minimum 8 characters.</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm mb-1">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    class="w-full border rounded px-3 py-2" autocomplete="new-password">
            </div>

            {{-- Optional: phone at signup (you can remove if not needed) --}}
            {{-- <div>
        <label for="contact" class="block text-sm mb-1">Contact number (optional)</label>
        <input id="contact" name="contact" type="text" value="{{ old('contact') }}"
            class="w-full border rounded px-3 py-2">
            </div> --}}

            <button type="submit" class="w-full rounded bg-indigo-600 text-white px-4 py-2">
                Create account
            </button>
        </form>

        <p class="mt-4 text-sm">
            Already have an account?
            <a href="{{ route('login') }}" class="text-indigo-700 underline">Sign in</a>
        </p>
    </main>
</body>

</html>