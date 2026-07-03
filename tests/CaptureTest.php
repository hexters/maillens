<?php

namespace Hexters\MailLens\Tests;

use Hexters\MailLens\Models\MailLensMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class CaptureTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_lens_mailer_is_injected(): void
    {
        $this->assertSame('maillens', config('mail.mailers.lens.transport'));
    }

    public function test_outgoing_mail_is_captured_instead_of_sent(): void
    {
        $this->assertSame(0, MailLensMessage::count());

        Mail::html('<h1>Hello</h1><p>body</p>', function ($message) {
            $message->to('to@example.com', 'Rcpt')
                ->cc('cc@example.com')
                ->subject('Captured subject')
                ->from('from@example.com', 'Sender');
        });

        $this->assertSame(1, MailLensMessage::count());

        $mail = MailLensMessage::first();
        $this->assertSame('Captured subject', $mail->subject);
        $this->assertSame('to@example.com', $mail->to[0]['address']);
        $this->assertSame('cc@example.com', $mail->cc[0]['address']);
        $this->assertStringContainsString('Hello', $mail->html);
    }

    public function test_attachments_are_stored(): void
    {
        Mail::html('<p>with file</p>', function ($message) {
            $message->to('to@example.com')
                ->subject('With attachment')
                ->attachData('file contents', 'note.txt', ['mime' => 'text/plain']);
        });

        $attachments = MailLensMessage::first()->attachments;

        $this->assertCount(1, $attachments);
        $this->assertSame('note.txt', $attachments[0]['filename']);
        $this->assertSame('file contents', base64_decode($attachments[0]['content']));
    }

    public function test_messages_get_a_uuid_used_as_the_route_key(): void
    {
        Mail::html('<p>hi</p>', function ($message) {
            $message->to('to@example.com')->subject('Has uuid');
        });

        $mail = MailLensMessage::first();

        $this->assertNotNull($mail->uuid);
        $this->assertSame($mail->uuid, $mail->getRouteKey());
        $this->assertStringContainsString($mail->uuid, route('maillens.html', $mail));
    }

    public function test_inbox_is_reachable(): void
    {
        $this->get('/mail')->assertOk();
    }

    public function test_search_filters_by_subject_and_recipient(): void
    {
        Mail::html('<p>a</p>', function ($message) {
            $message->to('alice@example.com')->subject('Invoice paid');
        });
        Mail::html('<p>b</p>', function ($message) {
            $message->to('bob@example.com')->subject('Password reset');
        });

        $this->get('/mail?q=Invoice')
            ->assertOk()
            ->assertSee('Invoice paid')
            ->assertDontSee('Password reset');

        $this->get('/mail?q=bob@example.com')
            ->assertOk()
            ->assertSee('Password reset')
            ->assertDontSee('Invoice paid');

        $this->get('/mail?q=nothingmatchesthis')
            ->assertOk()
            ->assertSee('No matches');
    }

    public function test_selecting_a_message_by_uuid(): void
    {
        Mail::html('<p>pick me</p>', function ($message) {
            $message->to('to@example.com')->subject('Selected by uuid');
        });

        $mail = MailLensMessage::first();

        $this->get('/mail?m=' . $mail->uuid)
            ->assertOk()
            ->assertSee('Selected by uuid');
    }

    public function test_poll_reports_count_and_latest_uuid(): void
    {
        Mail::html('<p>hi</p>', function ($message) {
            $message->to('to@example.com')->subject('Poll me');
        });

        $latest = MailLensMessage::query()->orderByDesc('id')->first();

        $this->get('/mail/poll')
            ->assertOk()
            ->assertJson(['count' => 1, 'latest' => $latest->uuid]);
    }

    public function test_logo_is_served(): void
    {
        $this->get('/mail/logo.png')
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_old_messages_are_pruned_to_the_limit(): void
    {
        config(['maillens.limit' => 3]);

        foreach (range(1, 5) as $i) {
            Mail::html("<p>mail {$i}</p>", function ($message) use ($i) {
                $message->to('to@example.com')->subject("Mail {$i}");
            });
        }

        $this->assertSame(3, MailLensMessage::count());
        $this->assertSame('Mail 5', MailLensMessage::orderByDesc('id')->first()->subject);
    }
}
