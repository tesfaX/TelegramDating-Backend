<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class TelegramBotController extends Controller
{
    public function handleWebhook(Request $request) {
        $tgData = $request->all();
        $jsonTgData = json_encode($tgData);

        if (isset($tgData['message']['text']) && $tgData['message']['text'] == '/start') {
            $chatId = $tgData['message']['chat']['id'];
            $message = "<b>Welcome to the Telegram Dating bot</b> \n\n Open the app and connect with other people.";
            NotificationController::sendMessage($chatId, $message);
        } else {
            if($tgData['pre_checkout_query']){
                $precheckId = $tgData['pre_checkout_query']['id'];
                $chatId = $tgData['pre_checkout_query']['from']['id'];
                $user = User::where('tg_id', $chatId)->first();
                if($user){
                    if($user->is_pro_user){
                        $this->answerPreCheckoutQuery($precheckId, false, "User already have pro account subscription");
                    } else {
                        $paymentAmount = env('PRO_ACCOUNT_PAYMENT_AMOUNT')*100;
                        $amountSent = $tgData['pre_checkout_query']['total_amount'];
                        if($paymentAmount == $amountSent){
                            $this->answerPreCheckoutQuery($precheckId, true, null, $user);
                        } else {
                            $this->answerPreCheckoutQuery($precheckId, false, "Payment doesn't match the amount");
                        }
                    }

                } else {
                    $this->answerPreCheckoutQuery($precheckId, false, "User doesn't exist");
                }

            }
        }

        return response()->json([
            'status' => 'ok',
        ], 200);
    }

    public function generateInvoice(Request $request) {
        $chatId = auth()->user()->tg_id;
        $providerToken = env('PAYMENT_PROVIDER_TOKEN');
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $apiUrl = "https://api.telegram.org/bot{$botToken}/createInvoiceLink";
        $paymentAmount = env('PRO_ACCOUNT_PAYMENT_AMOUNT')*100;
        $prices = [[
            'label' => 'One-Time Payment for Pro Account',
            'amount' => $paymentAmount,
        ]];
        $data = [
            'chat_id' => $chatId,
            'provider_token' => $providerToken,
            'title' => 'Payment for Premium Telegram Dating',
            'description' => 'Payment for bot',
            'payload' => $chatId,
            'currency' => 'USD',
            'prices' => $prices,
        ];

        $response = Http::post($apiUrl, $data);

        $data = json_decode($response->body(), true);

        if (isset($data['ok']) && $data['ok'] === true) {
            return response()->json([
                'success' => true,
                'payment_url' => $data['result'],
                'message' => 'Payment invoice generated'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'response' => $response->body()
            ], 200);
        }

    }

    public function answerPreCheckoutQuery($precheckQueryId, $status, $message = null, $user = null) {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $apiUrl = "https://api.telegram.org/bot{$botToken}/answerPreCheckoutQuery";

        $data = [
            'pre_checkout_query_id' => $precheckQueryId,
            'ok' => $status,
            'error_message' => $message
        ];

        $response = Http::post($apiUrl, $data);
        if($status){
            $data = json_decode($response->body(), true);
            if($data['ok']){
                $user->is_pro_user = true;
                $user->save();
            }
        }
    }


}
