@extends('layouts.staff')
@section('title', 'Edit Mailbox')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('staff.mailboxes.update', $mailbox) }}">
        @csrf
        @method('PUT')
        @include('staff.mailboxes._form', ['mailbox' => $mailbox])
    </form>
</div>
@endsection
