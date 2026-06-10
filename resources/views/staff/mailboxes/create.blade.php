@extends('layouts.staff')
@section('title', 'Add Mailbox')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('staff.mailboxes.store') }}">
        @csrf
        @include('staff.mailboxes._form', ['mailbox' => null])
    </form>
</div>
@endsection
