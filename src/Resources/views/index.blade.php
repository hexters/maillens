<!DOCTYPE html>
<html lang="en" x-data="{ tab: 'html', viewport: 'desktop', metaOpen: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MailLens — Inbox</title>
    <link rel="icon" type="image/png" href="{{ route('maillens.logo') }}">
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        :root {
            --bg: #f4f6fb; --panel: #ffffff; --panel-2: #f7f9fc; --border: #e5e9f0;
            --text: #1f2733; --muted: #7a8699; --accent: #2f6fed; --accent-soft: #eaf1ff;
            --unread: #2f6fed; --danger: #e5484d; --shadow: 0 1px 2px rgba(16,24,40,.06);
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg); color: var(--text); font-size: 14px;
            display: grid; grid-template-rows: auto 1fr; height: 100vh;
        }
        header {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 20px; background: var(--panel); border-bottom: 1px solid var(--border);
        }
        header .logo { display: flex; align-items: center; gap: 9px; font-weight: 700; letter-spacing: .2px; font-size: 15px; }
        header .logo img { height: 26px; width: auto; display: block; }
        header .logo .wordmark { color: #111318; }
        header .logo .wordmark span { color: var(--accent); }
        header .count { color: var(--muted); font-size: 12px; }
        header .spacer { flex: 1; }
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--panel); color: var(--text); border: 1px solid var(--border);
            padding: 7px 13px; border-radius: 8px; cursor: pointer; font-size: 13px; text-decoration: none;
            box-shadow: var(--shadow); transition: border-color .12s, color .12s, background .12s;
        }
        .btn:hover { border-color: var(--accent); color: var(--accent); }
        .btn.danger:hover { border-color: var(--danger); color: var(--danger); }
        .btn-ico { width: 16px; height: 16px; display: none; }
        main { display: grid; grid-template-columns: 350px 1fr; min-height: 0; }
        .list { border-right: 1px solid var(--border); overflow-y: auto; background: var(--panel); }
        .item {
            display: block; padding: 13px 18px; border-bottom: 1px solid var(--border);
            text-decoration: none; color: var(--text); position: relative;
        }
        .item:hover { background: var(--panel-2); }
        .item.active { background: var(--accent-soft); box-shadow: inset 3px 0 0 var(--accent); }
        .item .top { display: flex; justify-content: space-between; gap: 8px; }
        .item .from { font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .item .time { color: var(--muted); font-size: 11px; white-space: nowrap; }
        .item .subject { margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .item .preview { color: var(--muted); font-size: 12px; margin-top: 3px;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .item.unread .from::before {
            content: ""; display: inline-block; width: 8px; height: 8px; border-radius: 50%;
            background: var(--unread); margin-right: 6px; vertical-align: middle;
        }
        .detail { display: flex; flex-direction: column; min-width: 0; min-height: 0; }
        .meta { padding: 16px 22px; border-bottom: 1px solid var(--border); background: var(--panel); }
        .meta h1 { margin: 0 0 10px; font-size: 18px; }
        .meta .row { display: flex; gap: 8px; margin: 3px 0; font-size: 13px; }
        .meta .row .k { color: var(--muted); width: 60px; flex-shrink: 0; }
        .meta .row .v { word-break: break-word; }
        .meta-summary { display: none; }
        .meta-details { display: block; }
        .toolbar {
            display: flex; align-items: center; gap: 10px; padding: 8px 22px 0;
            background: var(--panel); border-bottom: 1px solid var(--border);
        }
        .tabs { display: flex; gap: 4px; }
        .tab { padding: 9px 14px; cursor: pointer; color: var(--muted); border-bottom: 2px solid transparent; font-size: 13px; }
        .tab.active { color: var(--text); border-bottom-color: var(--accent); }
        .tab .badge { background: var(--panel-2); border: 1px solid var(--border); border-radius: 10px; padding: 1px 7px; font-size: 11px; margin-left: 4px; }
        .viewport-switch { display: flex; gap: 2px; background: var(--panel-2); border: 1px solid var(--border); border-radius: 9px; padding: 3px; }
        .viewport-switch button {
            display: flex; align-items: center; gap: 5px; border: 0; background: transparent; cursor: pointer;
            color: var(--muted); padding: 5px 11px; border-radius: 6px; font-size: 12px; font-weight: 500;
        }
        .viewport-switch button.active { background: var(--panel); color: var(--accent); box-shadow: var(--shadow); }
        .viewport-switch svg { width: 15px; height: 15px; }
        .body { flex: 1; min-height: 0; overflow: auto; background: var(--panel-2); }
        .stage { display: flex; justify-content: center; height: 100%; padding: 18px; }
        .frame-wrap {
            background: #fff; border: 1px solid var(--border); border-radius: 10px; overflow: hidden;
            box-shadow: 0 6px 24px rgba(16,24,40,.08); width: 100%; height: 100%;
            transition: max-width .2s ease;
        }
        .frame-wrap.desktop { max-width: 100%; }
        .frame-wrap.tablet  { max-width: 768px; }
        .frame-wrap.mobile  { max-width: 390px; }
        .frame-wrap iframe { width: 100%; height: 100%; border: 0; background: #fff; display: block; }
        pre { margin: 0; padding: 20px 22px; white-space: pre-wrap; word-break: break-word;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12.5px; line-height: 1.6; color: var(--text); }
        .attachments { padding: 16px 22px; display: flex; flex-wrap: wrap; gap: 10px; }
        .attachment {
            display: flex; align-items: center; gap: 8px; background: var(--panel);
            border: 1px solid var(--border); border-radius: 8px; padding: 9px 13px;
            color: var(--text); text-decoration: none; font-size: 13px; box-shadow: var(--shadow);
        }
        .attachment:hover { border-color: var(--accent); }
        .attachment .size { color: var(--muted); font-size: 11px; }
        .empty { display: grid; place-items: center; height: 100%; color: var(--muted); text-align: center; padding: 40px; }
        .empty .big { font-size: 15px; color: var(--text); margin-bottom: 6px; }
        .empty code { background: var(--panel); border: 1px solid var(--border); border-radius: 5px; padding: 2px 6px; }
        form.inline { display: inline; }

        /* One pane at a time on phones: the list, or the open message. */
        .mobile-back { display: none; }
        @media (max-width: 820px) {
            main { grid-template-columns: 1fr; }
            main[data-view="message"] .list { display: none; }
            main[data-view="list"] .detail { display: none; }

            header { gap: 8px; padding: 10px 14px; }
            header .count { display: none; }
            header .logo { font-size: 14px; }
            header .logo img { height: 22px; }
            .btn { padding: 7px 9px; }
            .btn-ico { display: inline-flex; }
            .btn-label { display: none; }

            .list { border-right: 0; }
            .meta { padding: 14px 16px; }
            .meta h1 { font-size: 16px; margin-bottom: 8px; }
            .toolbar { padding: 8px 14px 0; gap: 8px; overflow-x: auto; }
            .stage { padding: 12px; }

            /* Gmail-style collapsible headers: compact sender line + chevron. */
            .meta-summary {
                display: flex; align-items: center; gap: 8px; width: 100%;
                background: transparent; border: 0; padding: 0; cursor: pointer;
                font: inherit; text-align: left;
            }
            .meta-sender {
                flex: 1; min-width: 0; font-size: 13px; color: var(--muted);
                overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
            }
            .meta-chevron { width: 18px; height: 18px; flex-shrink: 0; color: var(--muted); transition: transform .15s; }
            .meta.meta-open .meta-chevron { transform: rotate(180deg); }
            .meta-details { display: none; margin-top: 10px; }
            .meta.meta-open .meta-details { display: block; }

            /* The desktop/tablet/mobile preview switch makes no sense on a phone. */
            .viewport-switch { display: none; }
            .frame-wrap.tablet, .frame-wrap.mobile { max-width: 100%; }

            .mobile-back {
                display: flex; align-items: center; gap: 6px;
                padding: 11px 16px; background: var(--panel);
                border-bottom: 1px solid var(--border);
                color: var(--accent); text-decoration: none; font-weight: 600; font-size: 13px;
            }
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="{{ route('maillens.logo') }}" alt="MailLens">
            <span class="wordmark">Mail<span>Lens</span></span>
        </div>
        <div class="count">{{ $messages->count() }} message{{ $messages->count() === 1 ? '' : 's' }}</div>
        <div class="spacer"></div>
        <a class="btn" href="{{ route('maillens.index') }}" title="Refresh">
            <svg class="btn-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            <span class="btn-label">Refresh</span>
        </a>
        @if($messages->isNotEmpty())
            <form class="inline" method="POST" action="{{ route('maillens.clear') }}"
                  onsubmit="return confirm('Delete all captured mail?')">
                @csrf @method('DELETE')
                <button class="btn danger" type="submit" title="Clear all">
                    <svg class="btn-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6M14 11v6"/></svg>
                    <span class="btn-label">Clear all</span>
                </button>
            </form>
        @endif
    </header>

    <main data-view="{{ request()->filled('m') ? 'message' : 'list' }}">
        <div class="list">
            @forelse($messages as $message)
                <a class="item {{ $selected && $selected->uuid === $message->uuid ? 'active' : '' }} {{ $message->read ? '' : 'unread' }}"
                   href="{{ route('maillens.index', ['m' => $message->uuid]) }}">
                    <div class="top">
                        <span class="from">{{ $message->from_line ?: '(no sender)' }}</span>
                        <span class="time">{{ $message->created_at?->diffForHumans(null, true) }}</span>
                    </div>
                    <div class="subject">{{ $message->subject ?: '(no subject)' }}</div>
                    <div class="preview">{{ \Illuminate\Support\Str::limit($message->preview, 70) }}</div>
                </a>
            @empty
                <div class="empty">
                    <div>
                        <div class="big">Inbox is empty</div>
                        Send an email with <code>MAIL_MAILER={{ config('maillens.mailer', 'fake') }}</code><br>and it shows up here.
                    </div>
                </div>
            @endforelse
        </div>

        <div class="detail">
            <a class="mobile-back" href="{{ route('maillens.index') }}">&larr; Inbox</a>
            @if($selected)
                <div class="meta" :class="{ 'meta-open': metaOpen }">
                    <h1>{{ $selected->subject ?: '(no subject)' }}</h1>

                    {{-- Gmail-style: a compact sender line with a chevron that expands
                         the full headers. On desktop the details are always shown. --}}
                    <button type="button" class="meta-summary" @click="metaOpen = !metaOpen" :aria-expanded="metaOpen">
                        <span class="meta-sender">{{ $selected->from_line ?: '(no sender)' }}</span>
                        <svg class="meta-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                    </button>

                    <div class="meta-details">
                        <div class="row"><span class="k">From</span><span class="v">{{ $selected->from_line ?: '—' }}</span></div>
                        <div class="row"><span class="k">To</span><span class="v">{{ $selected->to_line ?: '—' }}</span></div>
                        @if($selected->cc)
                            <div class="row"><span class="k">Cc</span><span class="v">{{ $selected->formatAddresses($selected->cc) }}</span></div>
                        @endif
                        <div class="row"><span class="k">Date</span><span class="v">{{ $selected->created_at?->toDayDateTimeString() }}</span></div>
                    </div>
                </div>

                <div class="toolbar">
                    <div class="tabs">
                        <div class="tab" :class="{ active: tab === 'html' }" @click="tab = 'html'">HTML</div>
                        <div class="tab" :class="{ active: tab === 'text' }" @click="tab = 'text'">Text</div>
                        <div class="tab" :class="{ active: tab === 'source' }" @click="tab = 'source'">Source</div>
                        @if($selected->attachments && count($selected->attachments))
                            <div class="tab" :class="{ active: tab === 'attachments' }" @click="tab = 'attachments'">
                                Attachments<span class="badge">{{ count($selected->attachments) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="spacer" style="flex:1"></div>

                    <div class="viewport-switch" x-show="tab === 'html'" x-cloak>
                        <button :class="{ active: viewport === 'desktop' }" @click="viewport = 'desktop'" title="Desktop">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                            Desktop
                        </button>
                        <button :class="{ active: viewport === 'tablet' }" @click="viewport = 'tablet'" title="Tablet">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M12 18h.01"/></svg>
                            Tablet
                        </button>
                        <button :class="{ active: viewport === 'mobile' }" @click="viewport = 'mobile'" title="Mobile">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M12 18h.01"/></svg>
                            Mobile
                        </button>
                    </div>

                    <form class="inline" method="POST" action="{{ route('maillens.destroy', $selected) }}" style="padding:4px 0">
                        @csrf @method('DELETE')
                        <button class="btn danger" type="submit">Delete</button>
                    </form>
                </div>

                <div class="body">
                    <div x-show="tab === 'html'" style="height:100%">
                        <div class="stage">
                            <div class="frame-wrap" :class="viewport">
                                {{-- srcdoc renders the pristine captured email; using a src URL would let
                                     the host app's global HTML middleware (e.g. injected widgets) leak in.
                                     <base target="_blank"> makes every link in the email open in a new tab
                                     instead of navigating inside the preview; the sandbox allows that popup. --}}
                                <iframe srcdoc="{{ '<base target=_blank>' . ($selected->html ?: e($selected->text) ?: '(no content)') }}"
                                        sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox"></iframe>
                            </div>
                        </div>
                    </div>
                    <div x-show="tab === 'text'" x-cloak><pre>{{ $selected->text ?: '(no plain-text part)' }}</pre></div>
                    <div x-show="tab === 'source'" x-cloak><pre>{{ $selected->raw }}</pre></div>
                    @if($selected->attachments && count($selected->attachments))
                        <div x-show="tab === 'attachments'" x-cloak class="attachments">
                            @foreach($selected->attachments as $i => $attachment)
                                <a class="attachment" href="{{ route('maillens.attachment', [$selected, $i]) }}">
                                    📎 {{ $attachment['filename'] ?? 'attachment' }}
                                    <span class="size">{{ number_format(($attachment['size'] ?? 0) / 1024, 1) }} KB</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="empty">
                    <div>
                        <div class="big">No message selected</div>
                        Captured mail will appear on the left.
                    </div>
                </div>
            @endif
        </div>
    </main>

    <script>
        // Keep the inbox fresh like Mailtrap: poll while the tab is visible and
        // refresh the moment it regains focus. We only reload when something
        // actually changed (new mail arrived or mail was removed), so reading
        // a message is never interrupted needlessly. The current ?m= stays in
        // the URL, so your open message stays open across the refresh.
        (function () {
            var url = {{ \Illuminate\Support\Js::from(route('maillens.poll')) }};
            var current = {{ \Illuminate\Support\Js::from($messages->count() . ':' . ($messages->first()?->uuid ?? '')) }};

            function check() {
                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                        if (!data) return;
                        var signature = data.count + ':' + (data.latest || '');
                        if (signature !== current) window.location.reload();
                    })
                    .catch(function () {});
            }

            setInterval(function () { if (!document.hidden) check(); }, 5000);
            document.addEventListener('visibilitychange', function () { if (!document.hidden) check(); });
            window.addEventListener('focus', check);
        })();
    </script>
</body>
</html>
