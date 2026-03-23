<?php

namespace App\Jobs;

use App\Models\EmailMailbox;
use App\Services\EmailProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessInboundEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public array $emailData,
        public EmailMailbox $mailbox,
    ) {}

    public function handle(EmailProcessingService $service): void
    {
        $ticket = $service->process($this->emailData, $this->mailbox);

        if ($ticket && !empty($this->emailData['attachments'])) {
            foreach ($this->emailData['attachments'] as $attachment) {
                $path = 'attachments/email/' . $ticket->id . '/' . $attachment['name'];
                \Storage::disk('local')->put($path, $attachment['content']);

                $ticket->attachments()->create([
                    'file_name' => $attachment['name'],
                    'file_path' => $path,
                    'file_size' => $attachment['size'] ?? strlen($attachment['content']),
                    'mime_type' => $attachment['mime_type'] ?? 'application/octet-stream',
                ]);
            }
        }
    }
}
