@extends('layouts.staff')
@section('title', 'Edit Form Template')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('staff.form-templates.update', $formTemplate) }}">
        @csrf @method('PUT')
        @include('staff.form-templates._builder')
    </form>
</div>
@endsection
