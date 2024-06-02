<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Services\ChatGPTService;
use Telegram\Bot\Api;

class BotController extends Controller
{
    protected $chatGPTService;

    public function __construct(ChatGPTService $chatGPTService)
    {
        $this->chatGPTService = $chatGPTService;
    }

    public function start(Request $request)
    {
        $chatId = $request->input('message.chat.id');
        $firstName = $request->input('message.chat.first_name');
        $text = "Halo, $firstName! Ada yang bisa saya bantu?";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }

    public function handle()
    {
        $update = Telegram::getWebhookUpdates();

        // Check if the update contains a message
        if ($update->has('message')) {
            $message = $update->getMessage();

            $chatId = $message->getChat()->getId();
            $firstName = $message->getChat()->getFirstName();
            $text = $message->getText();

            // Check if the message is a command
            if ($text === '/start') {
                $responseText = "Halo, $firstName! Saya adalah AI. Kamu bisa bertanya apa saja. Saya akan berusaha menjawabnya.";
            } else {
                $responseText = $this->chatGPTService->sendMessage($text);
            }

            // Send a response back to the user
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $responseText,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
