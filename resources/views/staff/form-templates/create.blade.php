@extends('layouts.staff')
@section('title', 'Create Form Template')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('staff.form-templates.store') }}">
        @csrf
        @include('staff.form-templates._builder', ['formTemplate' => new \App\Models\FormTemplate])
    </form>
</div>
@endsection
