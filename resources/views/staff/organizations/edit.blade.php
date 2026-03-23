@extends('layouts.staff')
@section('title', 'Edit ' . $organization->name)

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('staff.organizations.update', $organization) }}" class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700">Name</label><input type="text" name="name" value="{{ old('name', $organization->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
            <div><label class="block text-sm font-medium text-gray-700">Slug</label><input type="text" name="slug" value="{{ old('slug', $organization->slug) }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700">Domain</label><input type="text" name="domain" value="{{ old('domain', $organization->domain) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
            <div><label class="block text-sm font-medium text-gray-700">Email Domain</label><input type="text" name="email_domain" value="{{ old('email_domain', $organization->email_domain) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        </div>
        <div><label class="block text-sm font-medium text-gray-700">Address</label><input type="text" name="address" value="{{ old('address', $organization->address) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        <div class="grid grid-cols-4 gap-4">
            <div><label class="block text-sm font-medium text-gray-700">City</label><input type="text" name="city" value="{{ old('city', $organization->city) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
            <div><label class="block text-sm font-medium text-gray-700">State</label><input type="text" name="state" value="{{ old('state', $organization->state) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
            <div><label class="block text-sm font-medium text-gray-700">ZIP</label><input type="text" name="zip" value="{{ old('zip', $organization->zip) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
            <div><label class="block text-sm font-medium text-gray-700">Phone</label><input type="text" name="phone" value="{{ old('phone', $organization->phone) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></div>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $organization->is_active)) class="rounded border-gray-300 text-indigo-600">
            <label class="text-sm text-gray-700">Active</label>
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('staff.organizations.show', $organization) }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
        </div>
    </form>
</div>
@endsection
