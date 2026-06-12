@extends('layouts.staff')
@section('title', 'Edit ' . $project->project_number)

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('staff.projects.update', $project) }}">
        @csrf @method('PUT')
        @include('staff.projects._form')
    </form>
</div>
@endsection
