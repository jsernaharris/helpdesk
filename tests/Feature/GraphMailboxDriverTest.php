<?php

namespace Tests\Feature;

use App\Models\EmailMailbox;
use App\Models\Organization;
use App\Services\Mail\GraphMailboxDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GraphMailboxDriverTest extends TestCase
{
    use RefreshDatabase;

    private function graphMailbox(): EmailMailbox
    {
        $org = Organization::create(['name' => 'MSP', 'slug' => 'msp', 'is_msp' => true, 'is_active' => true]);

        return EmailMailbox::create([
            'organization_id' => $org->id,
            'name' => 'Shared Support',
            'email_address' => 'support@company.com',
            'driver' => EmailMailbox::DRIVER_GRAPH,
            'graph_tenant_id' => 'tenant-123',
            'graph_client_id' => 'client-123',
            'graph_client_secret' => 'secret-123',
            'graph_user_id' => 'support@company.com',
        ]);
    }

    public function test_fetch_unread_normalizes_messages_and_marks_read(): void
    {
        Http::fake([
            '*oauth2/v2.0/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/messages/*/attachments' => Http::response(['value' => [[
                'name' => 'invoice.pdf',
                'contentType' => 'application/pdf',
                'size' => 5,
                'contentBytes' => base64_encode('hello'),
            ]]]),
            '*mailFolders/inbox/messages?*' => Http::response(['value' => [[
                'id' => 'AAA',
                'subject' => 'Need help',
                'from' => ['emailAddress' => ['address' => 'user@client.com']],
                'body' => ['contentType' => 'html', 'content' => '<p>Hi</p>'],
                'internetMessageId' => '<msg-1@client.com>',
                'hasAttachments' => true,
                'internetMessageHeaders' => [
                    ['name' => 'In-Reply-To', 'value' => '<prev@company.com>'],
                ],
            ]]]),
            '*/messages/*' => Http::response([], 200),
        ]);

        $mailbox = $this->graphMailbox();
        $messages = iterator_to_array(app(GraphMailboxDriver::class)->fetchUnread($mailbox));

        $this->assertCount(1, $messages);
        $email = $messages[0];
        $this->assertSame('user@client.com', $email['from']);
        $this->assertSame('Need help', $email['subject']);
        $this->assertSame('<p>Hi</p>', $email['body']);
        $this->assertSame('<msg-1@client.com>', $email['message_id']);
        $this->assertSame('<prev@company.com>', $email['in_reply_to']);
        $this->assertCount(1, $email['attachments']);
        $this->assertSame('hello', $email['attachments'][0]['content']);

        // Marked read via PATCH after the message was yielded/consumed.
        Http::assertSent(fn ($request) => $request->method() === 'PATCH'
            && str_contains($request->url(), '/messages/AAA')
            && $request['isRead'] === true);
    }

    public function test_send_creates_draft_and_sends(): void
    {
        Http::fake([
            '*oauth2/v2.0/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            '*/messages/*/send' => Http::response([], 202),
            '*/messages' => Http::response(['id' => 'draft-1', 'internetMessageId' => '<out-1@company.com>'], 201),
        ]);

        $mailbox = $this->graphMailbox();
        $id = app(GraphMailboxDriver::class)->send($mailbox, new \App\Services\Mail\OutboundMessage(
            toAddress: 'user@client.com',
            subject: 'Re: [INC-1] Need help',
            htmlBody: '<p>On it</p>',
        ));

        $this->assertSame('<out-1@company.com>', $id);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/messages/draft-1/send'));
    }

    public function test_missing_credentials_throws(): void
    {
        $org = Organization::create(['name' => 'MSP', 'slug' => 'msp', 'is_msp' => true, 'is_active' => true]);
        $mailbox = EmailMailbox::create([
            'organization_id' => $org->id,
            'name' => 'Broken',
            'email_address' => 'support@company.com',
            'driver' => EmailMailbox::DRIVER_GRAPH,
        ]);

        $this->expectException(\RuntimeException::class);
        iterator_to_array(app(GraphMailboxDriver::class)->fetchUnread($mailbox));
    }
}
