<!DOCTYPE html>
<html lang="en" x-data="{ tab: 'html', viewport: 'desktop', metaOpen: false, clearModal: false }">
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
            background: var(--bg); color: var(--text); font-size: 14px; height: 100vh;
        }
        .logo { display: flex; align-items: center; gap: 9px; font-weight: 700; letter-spacing: .2px; font-size: 15px; }
        .logo img { height: 24px; width: auto; display: block; }
        .logo .wordmark { color: #111318; }
        .logo .wordmark span { color: var(--accent); }
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--panel); color: var(--text); border: 1px solid var(--border);
            padding: 7px 13px; border-radius: 8px; cursor: pointer; font-size: 13px; text-decoration: none;
            box-shadow: var(--shadow); transition: border-color .12s, color .12s, background .12s;
        }
        .btn:hover { border-color: var(--accent); color: var(--accent); }
        .btn.danger:hover { border-color: var(--danger); color: var(--danger); }
        .btn.btn-lock { background: var(--danger); border-color: var(--danger); color: #fff; }
        .btn.btn-lock:hover { border-color: var(--danger); color: #fff; filter: brightness(.95); }
        .btn-ico { width: 16px; height: 16px; display: none; }
        main { display: grid; grid-template-columns: 350px 1fr; height: 100vh; min-height: 0; }
        .list { display: flex; flex-direction: column; min-height: 0; border-right: 1px solid var(--border); background: var(--panel); }
        .sidebar-head {
            flex-shrink: 0; display: flex; align-items: center; justify-content: space-between; gap: 10px;
            padding: 12px 14px; border-bottom: 1px solid var(--border);
        }
        .list-toolbar {
            flex-shrink: 0; display: flex; align-items: center; gap: 6px;
            padding: 8px 12px; border-bottom: 1px solid var(--border);
        }
        .list-items { flex: 1; min-height: 0; overflow-y: auto; }
        .search {
            flex: 1; min-width: 0; display: flex; align-items: center; gap: 8px;
            padding: 7px 11px; background: var(--panel); border: 1px solid var(--border); border-radius: 9px;
        }
        .search:focus-within { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(47,111,237,.12); }
        .search svg { width: 15px; height: 15px; color: var(--muted); flex-shrink: 0; }
        .search input { flex: 1; min-width: 0; border: 0; outline: none; background: transparent; font-size: 13px; color: var(--text); }
        .search input::placeholder { color: var(--muted); }
        .icon-btn {
            display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px;
            border: 0; background: transparent; border-radius: 8px; color: var(--muted); cursor: pointer; text-decoration: none;
        }
        .icon-btn:hover { background: var(--panel-2); color: var(--accent); }
        .icon-btn.danger:hover { color: var(--danger); }
        .icon-btn svg { width: 17px; height: 17px; }
        .item {
            display: block; padding: 13px 18px; border-bottom: 1px solid var(--border);
            text-decoration: none; color: var(--text);
        }
        .item:hover { background: var(--panel-2); }
        .item.active { background: var(--accent-soft); box-shadow: inset 3px 0 0 var(--accent); }
        .item .subject { font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .item .bottom { display: flex; align-items: baseline; gap: 8px; margin-top: 3px; }
        .item .to { flex: 1; min-width: 0; color: var(--muted); font-size: 12px;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .item .time { color: var(--muted); font-size: 11px; white-space: nowrap; flex-shrink: 0; }
        .item.unread .subject::before {
            content: ""; display: inline-block; width: 8px; height: 8px; border-radius: 50%;
            background: var(--unread); margin-right: 6px; vertical-align: middle;
        }
        .detail { display: flex; flex-direction: column; min-width: 0; min-height: 0; }
        .meta { padding: 16px 22px; border-bottom: 1px solid var(--border); background: var(--panel); }
        .meta-top { display: flex; align-items: baseline; justify-content: space-between; gap: 14px; }
        .meta h1 { margin: 0 0 10px; font-size: 18px; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .meta-date { color: var(--muted); font-size: 12px; white-space: nowrap; flex-shrink: 0; }
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

        .btn-solid-danger { background: var(--danger); border-color: var(--danger); color: #fff; }
        .btn-solid-danger:hover { border-color: var(--danger); color: #fff; filter: brightness(.95); }
        .modal-overlay {
            position: fixed; inset: 0; z-index: 100; display: flex; align-items: center; justify-content: center;
            padding: 20px; background: rgba(16,24,40,.45); backdrop-filter: blur(2px);
        }
        .modal {
            width: 100%; max-width: 380px; background: var(--panel); border-radius: 14px;
            padding: 24px; box-shadow: 0 20px 50px rgba(16,24,40,.28); text-align: center;
        }
        .modal-icon {
            width: 46px; height: 46px; margin: 0 auto 14px; border-radius: 50%;
            display: grid; place-items: center; background: #fdecec; color: var(--danger);
        }
        .modal-icon svg { width: 22px; height: 22px; }
        .modal h2 { margin: 0 0 6px; font-size: 17px; }
        .modal p { margin: 0 0 20px; color: var(--muted); font-size: 13px; line-height: 1.5; }
        .modal-actions { display: flex; gap: 10px; }
        .modal-actions > .btn { flex: 1; }
        .modal-actions form { flex: 1; }
        .modal-actions .btn { width: 100%; justify-content: center; padding: 9px 14px; }

        /* One pane at a time on phones: the list, or the open message. */
        .mobile-back { display: none; }
        @media (max-width: 820px) {
            main { grid-template-columns: 1fr; }
            main[data-view="message"] .list { display: none; }
            main[data-view="list"] .detail { display: none; }

            .logo { font-size: 14px; }
            .logo img { height: 22px; }
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
    <main data-view="{{ request()->filled('m') ? 'message' : 'list' }}">
        <div class="list">
            <div class="sidebar-head">
                <div class="logo">
                    <img src="{{ route('maillens.logo') }}" alt="MailLens">
                    <span class="wordmark">Mail<span>Lens</span></span>
                </div>
                @if(filled(config('maillens.password')))
                    <form class="inline" method="POST" action="{{ route('maillens.logout') }}">
                        @csrf
                        <button class="btn btn-lock" type="submit" title="Lock">
                            <svg class="btn-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span class="btn-label">Lock</span>
                        </button>
                    </form>
                @endif
            </div>

            <div class="list-toolbar">
                <form class="search" method="GET" action="{{ route('maillens.index') }}">
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search…" autocomplete="off" @if($search !== '') autofocus @endif>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                </form>
                <form class="inline" method="POST" action="{{ route('maillens.read') }}">
                    @csrf
                    <button class="icon-btn" type="submit" title="Mark all as read">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 13V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h9"/><path d="m2 7 10 6 10-6"/><path d="m16 19 2 2 4-4"/></svg>
                    </button>
                </form>
                <a class="icon-btn" href="{{ route('maillens.index', array_filter(['q' => $search])) }}" title="Refresh">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                </a>
                <button class="icon-btn danger" type="button" title="Clear all messages" @click="clearModal = true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6M14 11v6"/></svg>
                </button>
            </div>

            <div class="list-items">
            @forelse($messages as $message)
                <a class="item {{ $selected && $selected->uuid === $message->uuid ? 'active' : '' }} {{ $message->read ? '' : 'unread' }}"
                   href="{{ route('maillens.index', array_filter(['m' => $message->uuid, 'q' => $search])) }}">
                    <div class="subject">{{ $message->subject ?: '(no subject)' }}</div>
                    <div class="bottom">
                        <span class="to">to: {{ $message->recipients ?: '—' }}</span>
                        <span class="time">{{ $message->created_at?->diffForHumans() }}</span>
                    </div>
                </a>
            @empty
                <div class="empty">
                    @if($search !== '')
                        <div>
                            <div class="big">No matches</div>
                            Nothing matches &ldquo;{{ $search }}&rdquo;.
                        </div>
                    @else
                        <div>
                            <div class="big">Inbox is empty</div>
                            Send an email with <code>MAIL_MAILER={{ config('maillens.mailer', 'lens') }}</code><br>and it shows up here.
                        </div>
                    @endif
                </div>
            @endforelse
            </div>
        </div>

        <div class="detail">
            <a class="mobile-back" href="{{ route('maillens.index', array_filter(['q' => $search])) }}">&larr; Inbox</a>
            @if($selected)
                <div class="meta" :class="{ 'meta-open': metaOpen }">
                    <div class="meta-top">
                        <h1>{{ $selected->subject ?: '(no subject)' }}</h1>
                        <span class="meta-date">{{ $selected->created_at?->toDayDateTimeString() }}</span>
                    </div>

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

    <div class="modal-overlay" x-show="clearModal" x-cloak
         @keydown.escape.window="clearModal = false" @click.self="clearModal = false">
        <div class="modal">
            <div class="modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6M14 11v6"/></svg>
            </div>
            <h2>Clear all messages?</h2>
            <p>This permanently deletes every captured email. This can’t be undone.</p>
            <div class="modal-actions">
                <button type="button" class="btn" @click="clearModal = false">Cancel</button>
                <form method="POST" action="{{ route('maillens.clear') }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-solid-danger">Delete all</button>
                </form>
            </div>
        </div>
    </div>

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

        // Search: submit shortly after the user stops typing, and keep the caret
        // at the end of the box after the page reloads with results.
        (function () {
            var input = document.querySelector('.search input[name=q]');
            if (!input) return;
            if (input.value) { var v = input.value; input.value = ''; input.value = v; }
            var timer;
            input.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(function () { input.form.submit(); }, 400);
            });
        })();
    </script>
</body>
</html>
