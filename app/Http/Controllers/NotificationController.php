<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class NotificationController extends Controller
{
    public static function sendMessage($chatId, $message, $showButton = true)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');

        $apiUrl = "https://api.telegram.org/bot$botToken/sendMessage";

        $keyboard = [
            [
                [
                    'text' => 'Open App',
                    'web_app' => [
                        'url' => env('TELEGRAM_MINI_APP_URL')
                    ]
                ]
            ]
        ];

        $replyMarkup = [
            'inline_keyboard' => $keyboard,
        ];

        if($showButton){
            $encodedMarkup = json_encode($replyMarkup);
        } else {
            $encodedMarkup = null;
        }

        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'reply_markup' => $encodedMarkup,
            'parse_mode' => 'HTML',
        ];

        $response = Http::post($apiUrl, $data);
    }
}