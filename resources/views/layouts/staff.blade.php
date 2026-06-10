<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff Dashboard') - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="h-full">
    <div class="min-h-full">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 overflow-y-auto">
            <div class="flex h-16 items-center px-6">
                <span class="text-xl font-bold text-white">{{ config('app.name') }}</span>
            </div>
            <nav class="px-3 mt-2">
                <div class="space-y-1">
                    <a href="{{ route('staff.dashboard') }}" class="@if(request()->routeIs('staff.dashboard')) bg-gray-800 text-white @else text-gray-300 hover:bg-gray-700 hover:text-white @endif group flex items-center rounded-md px-3 py-2 text-sm font-medium">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                        Dashboard
                    </a>

                    <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">Service Desk</p>

                    <a href="{{ route('staff.tickets.index') }}" class="@if(request()->routeIs('staff.tickets.*')) bg-gray-800 text-white @else text-gray-300 hover:bg-gray-700 hover:text-white @endif group flex items-center rounded-md px-3 py-2 text-sm font-medium">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" /></svg>
                        Tickets
                    </a>
                    <a href="{{ route('staff.problems.index') }}" class="@if(request()->routeIs('staff.problems.*')) bg-gray-800 text-white @else text-gray-300 hover:bg-gray-700 hover:text-white @endif group flex items-center rounded-md px-3 py-2 text-sm font-medium">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" /></svg>
                        Problems
                    </a>
                    <a href="{{ route('staff.changes.index') }}" class="@if(request()->routeIs('staff.changes.*')) bg-gray-800 text-white @else text-gray-300 hover:bg-gray-700 hover:text-white @endif group flex items-center rounded-md px-3 py-2 text-sm font-medium">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M2.985 19.644l3.183-3.183" /></svg>
                        Changes
                    </a>
                    <a href="{{ route('staff.changes.calendar') }}" class="@if(request()->routeIs('staff.changes.calendar')) bg-gray-800 text-white @else text-gray-300 hover:bg-gray-700 hover:text-white @endif group flex items-center rounded-md px-3 py-2 text-sm font-medium">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                        Change Calendar
                    </a>
                    <a href="{{ route('staff.form-templates.index') }}" class="@if(request()->routeIs('staff.form-templates.*')) bg-gray-800 text-white @else text-gray-300 hover:bg-gray-700 hover:text-white @endif group flex items-center rounded-md px-3 py-2 text-sm font-medium">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>
                        Form Templates
                    </a>

                    <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">Knowledge</p>

                    <a href="{{ route('staff.kb.index') }}" class="@if(request()->routeIs('staff.kb.*')) bg-gray-800 text-white @else text-gray-300 hover:bg-gray-700 hover:text-white @endif group flex items-center rounded-md px-3 py-2 text-sm font-medium">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                        Knowledge Base
                    </a>

                    <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">Admin</p>

                    <a href="{{ route('staff.organizations.index') }}" class="@if(request()->routeIs('staff.organizations.*')) bg-gray-800 text-white @else text-gray-300 hover:bg-gray-700 hover:text-white @endif group flex items-center rounded-md px-3 py-2 text-sm font-medium">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                        Organizations
                    </a>
                    <a href="{{ route('staff.users.index') }}" class="@if(request()->routeIs('staff.users.*')) bg-gray-800 text-white @else text-gray-300 hover:bg-gray-700 hover:text-white @endif group flex items-center rounded-md px-3 py-2 text-sm font-medium">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                        Users
                    </a>
                    @can('settings.manage')
                    <a href="{{ route('staff.roles.index') }}" class="@if(request()->routeIs('staff.roles.*')) bg-gray-800 text-white @else text-gray-300 hover:bg-gray-700 hover:text-white @endif group flex items-center rounded-md px-3 py-2 text-sm font-medium">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                        Roles
                    </a>
                    <a href="{{ route('staff.mailboxes.index') }}" class="@if(request()->routeIs('staff.mailboxes.*')) bg-gray-800 text-white @else text-gray-300 hover:bg-gray-700 hover:text-white @endif group flex items-center rounded-md px-3 py-2 text-sm font-medium">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                        Mailboxes
                    </a>
                    @endcan
                    <a href="{{ route('staff.reports.index') }}" class="@if(request()->routeIs('staff.reports.*')) bg-gray-800 text-white @else text-gray-300 hover:bg-gray-700 hover:text-white @endif group flex items-center rounded-md px-3 py-2 text-sm font-medium">
                        <svg class="mr-3 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                        Reports
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main content -->
        <div class="pl-64">
            <!-- Top bar -->
            <div class="sticky top-0 z-40 flex h-16 items-center gap-x-4 border-b border-gray-200 bg-white px-6 shadow-sm">
                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1 items-center">
                        <h1 class="text-lg font-semibold text-gray-900">@yield('title', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center gap-x-4">
                        <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Logout</button>
                        </form>
                    </div>
                </div>
            </div>

            <main class="py-6 px-6">
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
    </div>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js" defer></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
