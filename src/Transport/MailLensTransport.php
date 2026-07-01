<?php

namespace Hexters\MailLens\Transport;

use Hexters\MailLens\Models\MailLensMessage;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

/**
 * A Symfony mailer transport that never delivers anything. Every message is
 * parsed and stored so it can be read later in the browser at /mail.
 */
class MailLensTransport extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $raw = $message->toString();

        MailLensMessage::create([
            'message_id' => $this->messageId($email),
            'subject' => $email->getSubject(),
            'from' => $this->addresses($email->getFrom()),
            'to' => $this->addresses($email->getTo()),
            'cc' => $this->addresses($email->getCc()),
            'bcc' => $this->addresses($email->getBcc()),
            'reply_to' => $this->addresses($email->getReplyTo()),
            'html' => $this->body($email->getHtmlBody()),
            'text' => $this->body($email->getTextBody()),
            'attachments' => $this->attachments($email),
            'raw' => $raw,
            'size' => strlen($raw),
        ]);

        $this->prune();
    }

    /**
     * @param  array<int, Address>  $addresses
     * @return array<int, array{name: string, address: string}>
     */
    protected function addresses(array $addresses): array
    {
        return array_map(fn (Address $a) => [
            'name' => $a->getName(),
            'address' => $a->getAddress(),
        ], $addresses);
    }

    protected function body($body): ?string
    {
        if ($body === null) {
            return null;
        }

        return is_string($body) ? $body : (string) $body;
    }

    /**
     * @return array<int, array{filename: string, content_type: string, size: int, content: string}>
     */
    protected function attachments(Email $email): array
    {
        $out = [];

        foreach ($email->getAttachments() as $attachment) {
            $body = $attachment->getBody();

            $out[] = [
                'filename' => $attachment->getFilename() ?? 'attachment',
                'content_type' => $attachment->getMediaType() . '/' . $attachment->getMediaSubtype(),
                'size' => strlen($body),
                'content' => base64_encode($body),
            ];
        }

        return $out;
    }

    protected function messageId(Email $email): ?string
    {
        $headers = $email->getHeaders();

        return $headers->has('Message-ID')
            ? trim($headers->get('Message-ID')->getBodyAsString(), '<>')
            : null;
    }

    /**
     * Keep the inbox from growing without bound.
     */
    protected function prune(): void
    {
        $limit = config('maillens.limit');

        if (! $limit) {
            return;
        }

        $keepIds = MailLensMessage::query()
            ->orderByDesc('id')
            ->limit($limit)
            ->pluck('id');

        MailLensMessage::query()
            ->whereNotIn('id', $keepIds)
            ->delete();
    }

    public function __toString(): string
    {
        return 'maillens';
    }
}
