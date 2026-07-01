<?php

namespace Hexters\MailLens\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MailLensMessage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'from' => 'array',
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'reply_to' => 'array',
        'attachments' => 'array',
        'read' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Give every message a UUID. The numeric id stays as the primary key,
        // but the UUID is what public URLs (?m=..) and route binding use, so we
        // never leak the row id or how many messages exist.
        static::creating(function (self $message) {
            $message->uuid ??= (string) Str::uuid();
        });
    }

    /**
     * Bind {message} route params and resolve links by UUID, not id.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getTable(): string
    {
        return config('maillens.table', 'maillens_messages');
    }

    /**
     * Turn a list of ['name' => .., 'address' => ..] into "Name <addr>, ..".
     */
    public function formatAddresses(?array $addresses): string
    {
        return collect($addresses ?? [])
            ->map(fn ($a) => filled($a['name'] ?? null)
                ? sprintf('%s <%s>', $a['name'], $a['address'] ?? '')
                : ($a['address'] ?? ''))
            ->filter()
            ->implode(', ');
    }

    public function getFromLineAttribute(): string
    {
        return $this->formatAddresses($this->from);
    }

    public function getToLineAttribute(): string
    {
        return $this->formatAddresses($this->to);
    }

    public function getPreviewAttribute(): string
    {
        $body = $this->text ?: strip_tags((string) $this->html);

        return trim(preg_replace('/\s+/', ' ', $body));
    }
}
