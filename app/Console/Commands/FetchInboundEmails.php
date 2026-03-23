<?php

namespace App\Console\Commands;

use App\Jobs\ProcessInboundEmail;
use App\Models\EmailMailbox;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;

class FetchInboundEmails extends Command
{
    protected $signature = 'helpdesk:fetch-emails';
    protected $description = 'Fetch and process inbound emails from configured mailboxes';

    public function handle(): int
    {
        $mailboxes = EmailMailbox::where('is_active', true)->get();

        if ($mailboxes->isEmpty()) {
            $this->info('No active mailboxes configured.');
            return self::SUCCESS;
        }

        foreach ($mailboxes as $mailbox) {
            $this->processMailbox($mailbox);
        }

        return self::SUCCESS;
    }

    private function processMailbox(EmailMailbox $mailbox): void
    {
        try {
            $client = Client::make([
                'host' => $mailbox->imap_host,
                'port' => $mailbox->imap_port,
                'encryption' => $mailbox->imap_encryption,
                'validate_cert' => true,
                'username' => $mailbox->imap_username,
                'password' => $mailbox->imap_password,
                'protocol' => 'imap',
            ]);

            $client->connect();
            $folder = $client->getFolder('INBOX');
            $messages = $folder->query()->unseen()->get();

            $count = 0;
            foreach ($messages as $message) {
                $emailData = [
                    'from' => $message->getFrom()[0]->full ?? '',
                    'subject' => $message->getSubject() ?? 'No Subject',
                    'body' => $message->hasHTMLBody() ? $message->getHTMLBody() : $message->getTextBody(),
                    'message_id' => $message->getMessageId()?->toString(),
                    'in_reply_to' => $message->getInReplyTo()?->toString(),
                    'references' => [],
                    'attachments' => [],
                ];

                // Collect attachment info
                foreach ($message->getAttachments() as $attachment) {
                    $emailData['attachments'][] = [
                        'name' => $attachment->getName(),
                        'content' => $attachment->getContent(),
                        'mime_type' => $attachment->getMimeType(),
                        'size' => $attachment->getSize(),
                    ];
                }

                ProcessInboundEmail::dispatch($emailData, $mailbox);
                $message->setFlag('Seen');
                $count++;
            }

            $mailbox->update(['last_fetched_at' => now()]);
            $this->info("Mailbox '{$mailbox->name}': processed {$count} emails.");

            $client->disconnect();
        } catch (\Exception $e) {
            $this->error("Mailbox '{$mailbox->name}' error: {$e->getMessage()}");
        }
    }
}
