<?php

namespace Hexters\MailLens\Models;

use Illuminate\Database\Eloquent\Model;

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
