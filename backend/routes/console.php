<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\Telegram;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('telegram:set-webhook {--remove}', function (Telegram $telegram) {
    if ($this->option('remove')) {
        $result = $telegram->call('deleteWebhook');
    } else {
        $result = $telegram->call('setWebhook', array_filter([
            'url'             => rtrim((string) config('app.url'), '/').'/api/telegram/webhook',
            'secret_token'    => config('services.telegram.webhook_secret'),
            'allowed_updates' => json_encode(['message', 'callback_query']),
        ]));
    }

    if (! ($result['ok'] ?? false)) {
        $this->error($result['description'] ?? 'Telegram webhook request failed.');

        return 1;
    }

    $this->info($this->option('remove')
        ? 'Telegram webhook removed.'
        : 'Telegram webhook configured: '.rtrim((string) config('app.url'), '/').'/api/telegram/webhook');

    return 0;
})->purpose('Configure or remove the Telegram bot webhook');
