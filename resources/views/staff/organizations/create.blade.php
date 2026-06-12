@extends('layouts.staff')
@section('title', 'Add Organization')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('staff.organizations.store') }}" class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" name="slug" value="{{ old('slug') }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Domain</label>
            <input type="text" name="domain" value="{{ old('domain') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" placeholder="portal.company.com">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email Domains</label>
            <textarea name="email_domains" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border" placeholder="harriscomputer.com&#10;stchealth.com">{{ old('email_domains') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">One domain per line. Inbound email from any of these routes to this organization.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Address</label>
            <input type="text" name="address" value="{{ old('address') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
        </div>
        <div class="grid grid-cols-4 gap-4">
            <div><label class="block text-sm font-medium text-gray-700">City</label><input type="text" name="city" value="{{ old('city') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
            <div><label class="block text-sm font-medium text-gray-700">State</label><input type="text" name="state" value="{{ old('state') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
            <div><label class="block text-sm font-medium text-gray-700">ZIP</label><input type="text" name="zip" value="{{ old('zip') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
            <div><label class="block text-sm font-medium text-gray-700">Phone</label><input type="text" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('staff.organizations.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Organization</button>
        </div>
    </form>
</div>
@endsection
