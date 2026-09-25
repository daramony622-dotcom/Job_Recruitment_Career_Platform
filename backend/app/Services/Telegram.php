<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Telegram
{
    private string $base;
    private string $fileBase;

    public function __construct()
    {
        $token = (string) config('services.telegram.token');

        $this->base     = "https://api.telegram.org/bot{$token}/";
        $this->fileBase = "https://api.telegram.org/file/bot{$token}/";
    }

    public function call(string $method, array $payload = []): ?array
    {
        try {
            $client = Http::asJson()->timeout(10);

            $caBundle = config('services.telegram.ca_bundle')
                ?: ini_get('curl.cainfo')
                ?: ini_get('openssl.cafile');

            if ($caBundle && is_file((string) $caBundle)) {
                $client = $client->withOptions(['verify' => (string) $caBundle]);
            }

            $response = $client->post($this->base.$method, $payload);
            $body = $response->json();

            if (! $response->successful()) {
                Log::error("Telegram {$method} failed", [
                    'status'      => $response->status(),
                    'description' => $body['description'] ?? $response->reason(),
                ]);
            }

            return is_array($body) ? $body : null;
        } catch (\Throwable $e) {
            Log::error("Telegram {$method} failed: ".$e->getMessage());

            return [
                'ok'          => false,
                'description' => 'Transport error: '.$e->getMessage(),
            ];
        }
    }

    public function sendMessage(int|string $chatId, string $text, ?array $keyboard = null): ?array
    {
        return $this->call('sendMessage', array_filter([
            'chat_id'      => $chatId,
            'text'         => $text,
            'parse_mode'   => 'HTML',
            'reply_markup' => $keyboard ? json_encode($keyboard) : null,
        ]));
    }

    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null): ?array
    {
        return $this->call('answerCallbackQuery', array_filter([
            'callback_query_id' => $callbackQueryId,
            'text'              => $text,
        ]));
    }

    public function deepLink(string $token): ?string
    {
        $username = (string) config('services.telegram.username');

        if ($username === '') {
            return null;
        }

        $username = preg_replace('#^(https?://)?(t\.me|telegram\.me)/#i', '', $username);
        $username = ltrim($username, '@');

        if ($username === '') {
            return null;
        }

        return 'https://t.me/'.$username.'?start='.$token;
    }

    public function fetchAndStoreProfilePhoto(int|string $chatId): ?string
    {
        $photos = $this->call('getUserProfilePhotos', ['user_id' => $chatId, 'limit' => 1]);
        $fileId = $photos['result']['photos'][0][0]['file_id'] ?? null;

        if (! $fileId) {
            return null;
        }

        $file = $this->call('getFile', ['file_id' => $fileId]);
        $path = $file['result']['file_path'] ?? null;

        if (! $path) {
            return null;
        }

        try {
            $bytes = Http::timeout(10)->get($this->fileBase.$path)->throw()->body();
        } catch (\Throwable $e) {
            Log::error('Telegram photo download failed: '.$e->getMessage());
            return null;
        }

        $filename = 'telegram-avatars/'.$chatId.'-'.Str::random(8).'.jpg';
        Storage::disk('public')->put($filename, $bytes);

        return Storage::url($filename);
    }
}