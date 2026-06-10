<?php

namespace App\Services\Mail;

use App\Models\EmailMailbox;
use InvalidArgumentException;

/**
 * Resolves the {@see MailboxDriver} for a given mailbox based on its `driver`.
 */
class MailboxDriverManager
{
    public function for(EmailMailbox $mailbox): MailboxDriver
    {
        return match ($mailbox->driver) {
            EmailMailbox::DRIVER_GRAPH => app(GraphMailboxDriver::class),
            EmailMailbox::DRIVER_IMAP => app(ImapMailboxDriver::class),
            default => throw new InvalidArgumentException("Unknown mailbox driver [{$mailbox->driver}]."),
        };
    }
}
