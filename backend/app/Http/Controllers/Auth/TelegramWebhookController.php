<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TelegramLoginToken;
use App\Models\User;
use App\Services\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, Telegram $tg)
    {
        abort_unless(
            hash_equals(
                (string) config('services.telegram.webhook_secret'),
                (string) $request->header('X-Telegram-Bot-Api-Secret-Token')
            ),
            403
        );

        if ($request->has('message')) {
            $this->onMessage($request->input('message'), $tg);
        }

        if ($request->has('callback_query')) {
            $this->onCallback($request->input('callback_query'), $tg);
        }

        return response()->json(['ok' => true]);
    }

    public function onMessage(array $message, Telegram $tg): void
    {

        $chatId = $message['chat']['id'];

        if (isset($message['contact'])) {
            User::where('telegram_id', $chatId)
                ->update(['phone' => $message['contact']['phone_number']]);

            $tg->sendMessage($chatId, '✅ រក្សាទុកលេខទូរស័ព្ទរួចរាល់។', [
                'remove_keyboard' => true,
            ]);
            return;
        }

        $text = $message['text'] ?? '';

        if (! Str::startsWith($text, '/start') || trim(Str::after($text, '/start')) === '') {
            $tg->sendMessage($chatId, 'សូមចុចប៊ូតុង "ចូលគណនី" នៅលើគេហទំព័រ។');
            return;
        }

        $payload = trim(Str::after($text, '/start'));
        $row     = TelegramLoginToken::where('token', $payload)->first();

        if (! $row || ! $row->isUsable()) {
            $tg->sendMessage($chatId, '⚠️ តំណនេះផុតកំណត់។ សូមព្យាយាមម្តងទៀត។');
            return;
        }

        $tg->sendMessage(
            $chatId,
            "អនុញ្ញាត <b>Job Recruitment Platform</b> ចូលគណនី ដើម្បីទទួលបានបទពិសោធន៍ដ៏អស្ចារ្យ!",
            ['inline_keyboard' => [[
                ['text' => 'No',  'callback_data' => "auth:no:{$payload}"],
                ['text' => 'Yes', 'callback_data' => "auth:yes:{$payload}"],
            ]]]
        );
    }

    public function onCallback(array $cb, Telegram $tg): void
    {

        $tg->call('answerCallbackQuery', ['callback_query_id' => $cb['id']]);

        $parts = explode(':', $cb['data'] ?? '');
        if (count($parts) !== 3 || $parts[0] !== 'auth') {
            return;
        }

        [, $answer, $token] = $parts;
        $from   = $cb['from'];
        $chatId = $from['id'];
        $msgId  = $cb['message']['message_id'];

        $row = TelegramLoginToken::where('token', $token)->first();

        if (! $row || ! $row->isUsable()) {
            $tg->call('editMessageText', [
                'chat_id' => $chatId, 'message_id' => $msgId,
                'text' => '⚠️ តំណនេះផុតកំណត់។ សូមព្យាយាមម្តងទៀត។',
            ]);
            return;
        }

        if ($answer === 'no') {
            $row->update(['status' => 'declined']);
            $tg->call('editMessageText', [
                'chat_id' => $chatId, 'message_id' => $msgId,
                'text' => '❌ បានបោះបង់។',
            ]);
            return;
        }

        $existing   = User::where('telegram_id', $from['id'])->first();
        $targetUser = $row->user_id ? User::find($row->user_id) : $existing;

        if ($targetUser) {
            $user = $targetUser;
            $user->update([
                'telegram_id'       => $from['id'],
                'telegram_username' => $from['username'] ?? $user->telegram_username,
            ]);
        } else {
            $name = trim(($from['first_name'] ?? '').' '.($from['last_name'] ?? '')) ?: 'Telegram User';
            $user = User::create([
                'name'              => $name,
                'telegram_id'       => $from['id'],
                'telegram_username' => $from['username'] ?? null,
                'role'              => 'job_seeker',
                'is_active'         => true,
            ]);
        }

        // grab their Telegram profile photo, if they have one
        if (! $user->telegram_photo) {
            if ($url = $tg->fetchAndStoreProfilePhoto($chatId)) {
                $user->update(['telegram_photo' => $url]);
            }
        }

        $row->update([
            'status'      => 'approved',
            'user_id'     => $user->id,
            'telegram_id' => $from['id'],
        ]);

        $tg->call('editMessageText', [
            'chat_id' => $chatId, 'message_id' => $msgId,
            'text' => '✅ ចូលគណនីដោយជោគជ័យ! សូមត្រឡប់ទៅគេហទំព័រវិញ។',
        ]);

        if (! $user->phone) {
            $tg->sendMessage($chatId, 'ចែករំលែកលេខទូរស័ព្ទរបស់អ្នក?', [
                'keyboard' => [[
                    ['text' => '📱 ចែករំលែកលេខទូរស័ព្ទ', 'request_contact' => true],
                ]],
                'resize_keyboard'   => true,
                'one_time_keyboard' => true,
            ]);
        }
    }
}
