<?php

namespace App\Services\Mail;

use App\Models\EmailMailbox;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;
use Webklex\IMAP\Facades\Client;

/**
 * Reads inbound mail over IMAP and sends outbound mail over SMTP using the
 * per-mailbox credentials. This is the original transport, preserved for
 * mailboxes that still use Basic Auth.
 */
class ImapMailboxDriver implements MailboxDriver
{
    public function fetchUnread(EmailMailbox $mailbox): iterable
    {
        $client = $this->connect($mailbox);

        try {
            $messages = $client->getFolder('INBOX')->query()->unseen()->get();

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

                foreach ($message->getAttachments() as $attachment) {
                    $emailData['attachments'][] = [
                        'name' => $attachment->getName(),
                        'content' => $attachment->getContent(),
                        'mime_type' => $attachment->getMimeType(),
                        'size' => $attachment->getSize(),
                    ];
                }

                yield $emailData;

                $message->setFlag('Seen');
            }
        } finally {
            $client->disconnect();
        }
    }

    public function send(EmailMailbox $mailbox, OutboundMessage $message): ?string
    {
        // 'ssl' => implicit TLS (SMTPS); 'tls'/null => opportunistic STARTTLS; 'none' => plaintext.
        $tls = match ($mailbox->smtp_encryption) {
            'ssl' => true,
            'none' => false,
            default => null,
        };

        $transport = new EsmtpTransport($mailbox->smtp_host, (int) $mailbox->smtp_port, $tls);
        if (filled($mailbox->smtp_username)) {
            $transport->setUsername($mailbox->smtp_username);
            $transport->setPassword((string) $mailbox->smtp_password);
        }

        $email = (new Email())
            ->from($mailbox->email_address)
            ->to($message->toAddress)
            ->subject($message->subject)
            ->html($message->htmlBody);

        if (filled($message->inReplyTo)) {
            $email->getHeaders()->addTextHeader('In-Reply-To', $message->inReplyTo);
        }

        (new Mailer($transport))->send($email);

        // Symfony generates the Message-ID at send time; thread matching falls back
        // to the subject line, so returning null here is acceptable.
        return null;
    }

    public function testConnection(EmailMailbox $mailbox): void
    {
        $client = $this->connect($mailbox);
        $client->getFolder('INBOX');
        $client->disconnect();
    }

    private function connect(EmailMailbox $mailbox)
    {
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

        return $client;
    }
}
