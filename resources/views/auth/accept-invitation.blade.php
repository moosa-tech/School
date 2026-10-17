<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accept School Invitation - School Management SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-900 rounded-2xl shadow-lg mb-3 text-white">
                <i class="fa-solid fa-envelope-open-text text-2xl text-amber-400"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Welcome to {{ $school->name }}</h1>
            <p class="text-slate-500 text-sm mt-1">Complete your registration to access your school workspace</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/80 p-8 space-y-6">

            <!-- School & Role summary card -->
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-xs space-y-1.5">
                <div class="flex justify-between text-slate-600">
                    <span>School Tenant:</span>
                    <span class="font-bold text-indigo-950">{{ $school->name }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Assigned Role:</span>
                    <span class="font-bold text-indigo-700 capitalize">{{ str_replace('_', ' ', $invitation->role) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Invited Email:</span>
                    <span class="font-medium text-slate-900">{{ $invitation->email }}</span>
                </div>
            </div>

            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl p-3">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('invitations.accept.post', $token) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Your Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                        placeholder="e.g. Principal Jane Doe"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Set Password</label>
                    <input type="password" id="password" name="password" required
                        placeholder="At least 8 characters"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        placeholder="Repeat password"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white transition">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-indigo-900 hover:bg-indigo-800 text-white font-semibold py-2.5 px-4 rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Activate Account & Enter School</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</body>
</html>
