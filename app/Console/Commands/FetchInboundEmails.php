<?php

namespace App\Console\Commands;

use App\Jobs\ProcessInboundEmail;
use App\Models\EmailMailbox;
use App\Services\Mail\MailboxDriverManager;
use Illuminate\Console\Command;

class FetchInboundEmails extends Command
{
    protected $signature = 'helpdesk:fetch-emails';
    protected $description = 'Fetch and process inbound emails from configured mailboxes';

    public function handle(MailboxDriverManager $drivers): int
    {
        $mailboxes = EmailMailbox::where('is_active', true)->get();

        if ($mailboxes->isEmpty()) {
            $this->info('No active mailboxes configured.');
            return self::SUCCESS;
        }

        foreach ($mailboxes as $mailbox) {
            $this->processMailbox($mailbox, $drivers);
        }

        return self::SUCCESS;
    }

    private function processMailbox(EmailMailbox $mailbox, MailboxDriverManager $drivers): void
    {
        try {
            $count = 0;
            foreach ($drivers->for($mailbox)->fetchUnread($mailbox) as $emailData) {
                ProcessInboundEmail::dispatch($emailData, $mailbox);
                $count++;
            }

            $mailbox->update(['last_fetched_at' => now()]);
            $this->info("Mailbox '{$mailbox->name}': processed {$count} emails.");
        } catch (\Exception $e) {
            $this->error("Mailbox '{$mailbox->name}' error: {$e->getMessage()}");
        }
    }
}
