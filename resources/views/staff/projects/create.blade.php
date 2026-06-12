@extends('layouts.staff')
@section('title', 'New Project')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('staff.projects.store') }}">
        @csrf
        @include('staff.projects._form')
    </form>
</div>
@endsection
