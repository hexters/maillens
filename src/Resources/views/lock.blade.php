<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MailLens — Locked</title>
    <link rel="icon" type="image/png" href="{{ route('maillens.logo') }}">
    <style>
        :root { --bg: #f4f6fb; --panel: #fff; --border: #e5e9f0; --text: #1f2733; --muted: #7a8699; --accent: #2f6fed; --danger: #e5484d; }
        * { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg); color: var(--text); display: grid; place-items: center; padding: 20px;
        }
        .card {
            width: 100%; max-width: 360px; background: var(--panel); border: 1px solid var(--border);
            border-radius: 14px; padding: 28px 24px; box-shadow: 0 8px 30px rgba(16,24,40,.06); text-align: center;
        }
        .card img { height: 44px; width: auto; }
        .card h1 { font-size: 17px; margin: 14px 0 4px; }
        .card h1 span { color: var(--accent); }
        .card p { color: var(--muted); font-size: 13px; margin: 0 0 20px; }
        form { display: flex; flex-direction: column; gap: 10px; }
        input {
            width: 100%; padding: 11px 13px; border: 1px solid var(--border); border-radius: 9px;
            font-size: 14px; outline: none; transition: border-color .12s, box-shadow .12s;
        }
        input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(47,111,237,.15); }
        button {
            padding: 11px 13px; border: 0; border-radius: 9px; background: var(--accent); color: #fff;
            font-size: 14px; font-weight: 600; cursor: pointer;
        }
        button:hover { filter: brightness(.96); }
        .error { color: var(--danger); font-size: 13px; margin: 0 0 12px; }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ route('maillens.logo') }}" alt="MailLens">
        <h1>Mail<span>Lens</span></h1>
        <p>This inbox is password protected.</p>

        @if($error)
            <p class="error">Wrong password, try again.</p>
        @endif

        <form method="POST" action="{{ route('maillens.unlock') }}">
            @csrf
            <input type="password" name="password" placeholder="Password" autofocus autocomplete="current-password">
            <button type="submit">Unlock</button>
        </form>
    </div>
</body>
</html>
