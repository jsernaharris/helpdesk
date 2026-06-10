<?php

namespace App\Services\Mail;

/**
 * A normalized outbound email, independent of the transport used to send it.
 */
class OutboundMessage
{
    /**
     * @param  array<int, string>  $references  Message-IDs of prior messages in the thread.
     */
    public function __construct(
        public string $toAddress,
        public string $subject,
        public string $htmlBody,
        public ?string $toName = null,
        public ?string $inReplyTo = null,
        public array $references = [],
    ) {}
}
