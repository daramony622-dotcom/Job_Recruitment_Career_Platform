<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Continue in Telegram</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f6f8; display: grid; place-items: center; min-height: 100vh; margin: 0; }
        .card { background: #fff; padding: 32px; border-radius: 12px; max-width: 380px; text-align: center; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .btn { display: inline-block; margin: 16px 0; padding: 12px 22px; background: #229ED9; color: #fff; border-radius: 8px; text-decoration: none; font-size: 16px; }
        .muted { color: #666; font-size: 14px; line-height: 1.5; }
        #msg { margin-top: 12px; font-size: 14px; }
    </style>
</head>
<body>
<div class="card">
    <h2>Continue in Telegram</h2>
    <a class="btn" href="{{ $link }}" target="_blank" rel="noopener">Open Telegram</a>
    <p class="muted">Press <b>Start</b> in the bot, then tap <b>Yes, log me in</b>.<br>
        This page will sign you in automatically.</p>
    <div id="msg" class="muted">Waiting for Telegram…</div>
    <p><a href="{{ route('login') }}" class="muted">Cancel</a></p>
</div>

<script>
    const statusUrl = @json(route('telegram.status', $token));
    const loginUrl  = @json(route('login'));
    const msg = document.getElementById('msg');

    const timer = setInterval(async () => {
        try {
            const res = await fetch(statusUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!res.ok) return;
            const data = await res.json();

            if (data.status === 'confirming') msg.textContent = 'Waiting for your confirmation in Telegram…';
            if (data.status === 'ok')         { clearInterval(timer); msg.textContent = 'Logged in! Redirecting…'; location.href = data.redirect; }
            if (data.status === 'expired')    { clearInterval(timer); msg.textContent = 'Expired. Redirecting…'; setTimeout(() => location.href = loginUrl, 1200); }
        } catch (e) { /* network hiccup, try again next tick */ }
    }, 2000);
</script>
</body>
</html>