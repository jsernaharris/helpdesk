@extends('layouts.portal')
@section('title', $ticket->ticket_number)

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-semibold text-gray-900">{{ $ticket->subject }}</h2>
            <div class="flex items-center gap-2">
                @include('components.priority-badge', ['priority' => $ticket->priority])
                @include('components.status-badge', ['status' => $ticket->status])
            </div>
        </div>
        <div class="text-sm text-gray-500 flex items-center gap-4">
            <span>{{ $ticket->ticket_number }}</span>
            <span>{{ ucfirst(str_replace('_',' ',$ticket->type)) }}</span>
            <span>Created {{ $ticket->created_at->format('M d, Y H:i') }}</span>
            @if($ticket->assignedTo)
            <span>Assigned to {{ $ticket->assignedTo->name }}</span>
            @endif
        </div>

        @if($ticket->isOpen())
        <form method="POST" action="{{ route('portal.tickets.close', $ticket) }}" class="mt-4">
            @csrf @method('PATCH')
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 underline">Close this ticket</button>
        </form>
        @endif
    </div>

    <!-- Conversation -->
    <div class="space-y-4 mb-6">
        @foreach($ticket->threads as $thread)
        <div class="bg-white shadow rounded-lg p-5 @if($thread->user && $thread->user->isMspStaff()) border-l-4 border-indigo-400 @endif">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-900">
                    {{ $thread->author_name }}
                    @if($thread->user && $thread->user->isMspStaff())
                    <span class="text-xs text-indigo-600">(Support)</span>
                    @endif
                </span>
                <span class="text-xs text-gray-500">{{ $thread->created_at->format('M d, Y H:i') }}</span>
            </div>
            <div class="prose prose-sm max-w-none text-gray-700">{!! nl2br(e($thread->body)) !!}</div>
            @if($thread->attachments->count())
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($thread->attachments as $attachment)
                <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs text-gray-700">{{ $attachment->file_name }}</span>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Reply Form -->
    @if($ticket->isOpen())
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Add a Reply</h3>
        <form method="POST" action="{{ route('portal.tickets.reply', $ticket) }}" enctype="multipart/form-data">
            @csrf
            <textarea name="body" rows="4" required placeholder="Type your message..." class="block w-full rounded-md border-gray-300 text-sm px-3 py-2 border mb-3"></textarea>
            <div class="flex justify-between items-center">
                <input type="file" name="attachments[]" multiple class="text-sm text-gray-500">
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Send Reply</button>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
