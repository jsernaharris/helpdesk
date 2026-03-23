<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Support Portal') - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="h-full">
    <div class="min-h-full">
        <nav class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex shrink-0 items-center">
                            <span class="text-xl font-bold text-indigo-600">{{ config('app.name') }}</span>
                        </div>
                        <div class="ml-10 flex items-center space-x-4">
                            <a href="{{ route('portal.dashboard') }}" class="@if(request()->routeIs('portal.dashboard')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium">Dashboard</a>
                            <a href="{{ route('portal.tickets.index') }}" class="@if(request()->routeIs('portal.tickets.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium">My Tickets</a>
                            <a href="{{ route('portal.tickets.create') }}" class="@if(request()->routeIs('portal.tickets.create')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium">Submit Ticket</a>
                            <a href="{{ route('portal.changes.index') }}" class="@if(request()->routeIs('portal.changes.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium">Change Requests</a>
                            <a href="{{ route('portal.kb.index') }}" class="@if(request()->routeIs('portal.kb.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium">Knowledge Base</a>
                        </div>
                    </div>
                    <div class="flex items-center gap-x-4">
                        <a href="{{ route('portal.profile') }}" class="text-sm text-gray-500 hover:text-gray-700">{{ auth()->user()->name }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl py-6 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4">
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-4">
                    <ul class="text-sm text-red-800">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
    @livewireScripts
    @stack('scripts')
</body>
</html>
