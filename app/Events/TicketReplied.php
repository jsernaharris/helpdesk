<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\TicketThread;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketReplied
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public TicketThread $thread,
    ) {}
}
